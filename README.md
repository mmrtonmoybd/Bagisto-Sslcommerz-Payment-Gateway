# Bagisto Sslcommerz Payment Gateway
Sslcommerz is a popular payment gateway in Bangladesh. This package provides a additional strong help for the user to use the sslcommerz payment gateway in their Bagisto laravel ecommerce application.

## Automatic Installation
1. Use command prompt to run this package `composer require mmrtonmoybd/sslcommerz`
5. Now go to your bagisto admin section `admin/configuration/sales/paymentmethods` you will see the new payment gateway Sslcommerz. 
3. Now run `php artisan config:cache`

## Manual Installation
1. Download the zip folder from the github repository.
2. Unzip the folder and go to your bagisto application path `package` and create a folder name `Mmrtonmoybd\Sslcommerz` upload `src` folder inside this path.
3. Now open `config/app.php` and register sslcommerz provider.
```sh
'providers' => [
        // Sslcommerz provider
         Mmrtonmoybd\Sslcommerz\Providers\SslcommerzServiceProvider::class,
]
```
4. Now open composer.json and go to `autoload psr-4`.
```sh
"autoload": {
        "psr-4": {
        "Mmrtonmoybd\\Sslcommerz\\": "packages/Mmrtonmoybd/Sslcommerz/src"
        }
    }
```
5. Now open the command prompt and run `composer dump-autoload`.
6. Now run `php artisan config:cache`
7. Now go to your bagisto admin section `admin/configuration/sales/paymentmethods` you will see the new payment gateway sslcommerz. 

## Troubleshooting

1. if anybody facing after placing a order you are not redirecting to payment gateway and getting a route error then simply go to `bootstrap/cache` and delete all the cache files.

For any help or customisation  <https://www.facebook.com/mmrtonmoy> or email us <rmedha037@gmail.com>
