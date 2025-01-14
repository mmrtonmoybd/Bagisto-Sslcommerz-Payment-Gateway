<?php

namespace Mmrtonmoybd\Sslcommerz\Http\Controllers;

use Illuminate\Http\Request;
use Mmrtonmoybd\Sslcommerz\Library\SslCommerz\SslCommerzNotification;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Contracts\Order;
use Webkul\Sales\Models\OrderPayment;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;
use Webkul\Sales\Transformers\OrderResource;

class SslCommerzPaymentController extends Controller
{
    protected $iorder;

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

    protected $OrderTransactionRepository;

    /**
     * Create a new controller instance.
     *
     *
     * @return void
     */
    public function __construct(OrderRepository $orderRepository, InvoiceRepository $invoiceRepository, OrderTransactionRepository $OrderTransactionRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->OrderTransactionRepository = $OrderTransactionRepository;
    }

    public function index()
    {
        // Here you have to receive all the order data to initate the payment.
        // Let's say, your oder transaction informations are saving in a table called "orders"
        // In "orders" table, order unique identity is "transaction_id". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.
        $cart = Cart::getCart();
        $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        $discount_amount = $cart->discount_amount; // discount amount
        $total_amount = $cart->grand_total; // total amount
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
        $post_data['ship_add1'] = $information->address;
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

    public function success(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $cart = Cart::getCart();

        $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        $discount_amount = $cart->discount_amount; // discount amount
        $total_amount = $cart->grand_total; // total amount
        $information = $cart->billing_address;
        $cart_currency = $cart->cart_currency_code;
        $sslc = new SslCommerzNotification();
        $validation = $sslc->orderValidate($request->all(), $cart->id, $total_amount, $cart_currency);

        if ($validation == true) {
            $data = (new OrderResource($cart))->jsonSerialize();

            $order = $this->orderRepository->create($data);

            $this->savePaymentTransactionId($order['id'], $tran_id);

            if ($order->canInvoice()) {
                $invoice = $this->invoiceRepository->create($this->prepareInvoiceData($order));

                $this->OrderTransactionRepository->updateOrCreate([
                    'transaction_id' => $request->input('bank_tran_id'),
                    'status' => 'paid',
                    'type' => $request->input('card_type'),
                    'payment_method' => $invoice->order->payment->method,
                    'amount' => $total_amount,
                    'order_id' => $invoice->order->id,
                    'invoice_id' => $invoice->id,
                    'data' => json_encode($request->all()),
                ]);
            }

            Cart::deActivateCart();

            session()->flash('order_id', $order->id);

            return redirect()->route('shop.checkout.onepage.success');
        }
    }

    protected function prepareInvoiceData($order): array
    {
        $invoiceData = [
            'order_id' => $order->id,
            'invoice' => ['items' => []],
        ];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    public function fail(Request $request)
    {
        session()->flash('error', 'SslCommerz payment either cancelled or transaction failure.');

        return redirect()->route('shop.checkout.cart.index');
    }

    protected function ipnprepareInvoiceData(): array
    {
        $invoiceData = [
            'order_id' => $this->iorder->id,
        ];

        foreach ($this->iorder->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    /*
    Feature Request
      */
    public function ipn(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $this->iorder = $this->orderRepository->findOneByField(['cart_id' => $tran_id]);
        //$cart = Cart::getCart();

       // $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        //$discount_amount = $cart->discount_amount; // discount amount
        $total_amount = $this->iorder->grand_total; // total amount
        //$information = $cart->billing_address;
        $cart_currency = $this->iorder->cart_currency_code;
        $sslc = new SslCommerzNotification();
        $validation = $sslc->orderValidate($request->all(), $tran_id, $total_amount, $cart_currency);

        if ($validation == true) {
            $this->orderRepository->update(['status' => 'processing'], $this->iorder->id);
            $this->savePaymentTransactionId($this->iorder->id, $tran_id);

            if ($this->iorder->canInvoice()) {
                $invoice = $this->invoiceRepository->create($this->ipnprepareInvoiceData());

                $this->OrderTransactionRepository->updateOrCreate([
                    'transaction_id' => $request->input('bank_tran_id'),
                    'status' => 'paid',
                    'type' => $request->input('card_type'),
                    'payment_method' => $invoice->iorder->payment->method,
                    'amount' => $total_amount,
                    'order_id' => $invoice->iorder->id,
                    'invoice_id' => $invoice->id,
                    'data' => json_encode($request->all()),
                ]);
            }

            //Cart::deActivateCart();
        }
    }

    protected function savePaymentTransactionId(int $orderId, string $tran): void
    {
        OrderPayment::where('order_id', $orderId)->update(['additional' => $tran]);
    }
}
