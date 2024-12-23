<?php

namespace Mmrtonmoybd\Sslcommerz\Http\Controllers;

use Illuminate\Http\Request;
use Mmrtonmoybd\Sslcommerz\Library\SslCommerz\SslCommerzNotification;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;

class SslCommerzPaymentController extends Controller
{
    /**
     * OrderRepository $orderRepository.
     *
     * @var \Webkul\Sales\Repositories\OrderRepository
     */
    protected $orderRepository;
    /**
     * InvoiceRepository $invoiceRepository.
     *
     * @var \Webkul\Sales\Repositories\InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * Create a new controller instance.
     *
     *
     * @return void
     */
    public function __construct(OrderRepository $orderRepository, InvoiceRepository $invoiceRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function index()
    {
        // Here you have to receive all the order data to initate the payment.
        // Let's say, your oder transaction informations are saving in a table called "orders"
        // In "orders" table, order unique identity is "transaction_id". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.
        $cart = Cart::getCart();
        $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        $discount_amount = $cart->discount_amount; // discount amount
        $total_amount = ($cart->sub_total + $cart->tax_total + $shipping_rate) - $discount_amount; // total amount
        $information = $cart->billing_address;

        $post_data = [];
        $post_data['total_amount'] = $total_amount; // You cant not pay less than 10
        $post_data['currency'] = $cart->cart_currency_code;
        $post_data['tran_id'] = $cart->id; // tran_id must be unique

        // CUSTOMER INFORMATION
        $post_data['cus_name'] = $information->first_name.' '.$information->last_name;
        $post_data['cus_email'] = $information->email;
        $post_data['cus_add1'] = $information->address1;
        $post_data['cus_add2'] = $information->address2;
        $post_data['cus_city'] = $information->city;
        $post_data['cus_state'] = $information->state;
        $post_data['cus_postcode'] = $information->postcode;
        $post_data['cus_country'] = $information->country;
        $post_data['cus_phone'] = $information->phone;

        // SHIPMENT INFORMATION
        $post_data['ship_name'] = $cart->shipping_method;
        $post_data['ship_add1'] = $information->address1;
        $post_data['ship_add2'] = $information->address2;
        $post_data['ship_city'] = $information->city;
        $post_data['ship_state'] = $information->state;
        $post_data['ship_postcode'] = $information->postcode;
        $post_data['ship_phone'] = $information->phone;
        $post_data['ship_country'] = $information->phone;

        $post_data['shipping_method'] = $cart->shipping_method;
        $pname = [];
        $ptype = [];
        $pid = [];
        foreach ($cart->items as $key => $value) {
            array_push($pname, $value->name);
            array_push($ptype, $value->type);
            array_push($pid, $value->product_id);
        }
        $post_data['product_name'] = implode(', ', $pname);
        $post_data['product_category'] = implode(', ', $ptype);
        $post_data['product_profile'] = implode(', ', $pid);

        $post_data['cart'] = $cart->items->toJson();
        $post_data['discount_amount'] = $discount_amount;
        $post_data['convenience_fee'] = $shipping_rate;
        $post_data['vat'] = $cart->tax_total;

        $post_data['emi_option'] = 0;

        $sslc = new SslCommerzNotification();
        // initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (! is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = [];
        }
    }

    /**
     * Prepares order's invoice data for creation.
     *
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    public function success(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $cart = Cart::getCart();
        $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        $discount_amount = $cart->discount_amount; // discount amount
        $total_amount = ($cart->sub_total + $cart->tax_total + $shipping_rate) - $discount_amount; // total amount
        $information = $cart->billing_address;
        $cart_currency = $cart->cart_currency_code;
        $sslc = new SslCommerzNotification();
        $validation = $sslc->orderValidate($request->all(), $cart->id, $total_amount, $cart_currency);

        if ($validation == true) {
            $order = $this->orderRepository->create(Cart::prepareDataForOrder());
            $this->orderRepository->update(['status' => 'processing'], $order->id);
            if ($order->canInvoice()) {
                $this->invoiceRepository->create($this->prepareInvoiceData($order));
            }
            Cart::deActivateCart();
            session()->flash('order', $order);

            // Order and prepare invoice
            return redirect()->route('shop.checkout.success');
        }
    }

    public function fail(Request $request)
    {
        session()->flash('error', 'SslCommerz payment either cancelled or transaction failure.');

        return redirect()->route('shop.checkout.cart.index');
    }

    /*
    Feature Request
      */
    public function ipn(Request $request)
    {
    }
}
