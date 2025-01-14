# Bagisto Sslcommerz Payment Gateway
Sslcommerz is a popular payment gateway in Bangladesh. This package provides a additional strong help for the user to use the sslcommerz payment gateway in their Bagisto laravel ecommerce application.

## Composer Installation
1. Use command prompt to run this package `composer require mmrtonmoybd/sslcommerz`
5. Now go to your bagisto admin section `admin/configuration/sales/payment_methods` you will see the new payment gateway Sslcommerz. 
3. Now run `php artisan config:cache`

## Troubleshooting

1. if anybody facing after placing a order you are not redirecting to payment gateway and getting a route error then simply go to `bootstrap/cache` and delete all the cache files.

For any help or customisation  <https://www.facebook.com/mmrtonmoy> or email us <rmedha037@gmail.com>
