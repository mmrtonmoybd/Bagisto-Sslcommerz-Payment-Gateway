<?php

return [
    [
        'key' => 'sales.payment_methods.sslcommerz',
        'name' => 'sslcommerz::app.sslcommerz.name',
        'info' => 'sslcommerz::app.sslcommerz.info',
        'sort' => 0,
        'fields' => [
            [
                'name' => 'title',
                'title' => 'sslcommerz::app.sslcommerz.system.title',
                'type' => 'text',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true,
            ], [
                'name' => 'description',
                'title' => 'sslcommerz::app.sslcommerz.system.description',
                'type' => 'textarea',
                'channel_based' => false,
                'locale_based' => true,
            ],
            [
                'name' => 'sslcommerz_store_id',
                'title' => 'sslcommerz::app.sslcommerz.system.sslcommerz-store-id',
                'type' => 'text',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true,
            ],
            [
                'name' => 'sslcommerz_store_password',
                'title' => 'sslcommerz::app.sslcommerz.system.sslcommerz-store-password',
                'type' => 'text',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true,
            ],
            [
                'name' => 'sandbox',
                'title' => 'sslcommerz::app.sslcommerz.system.sslcommerz-websitestatus',
                'type' => 'boolean',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true,
            ],
            [
                'name' => 'sslcommerz_connect_from_localhost',
                'title' => 'sslcommerz::app.sslcommerz.system.sslcommerz-connect-from-localhost',
                'type' => 'boolean',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true,
            ],
            [
                'name'          => 'image',
                'title'         => 'sslcommerz::app.sslcommerz.system.image',
                'info'          => 'admin::app.configuration.index.sales.payment-methods.logo-information',
                'type'          => 'file',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name' => 'active',
                'title' => 'sslcommerz::app.sslcommerz.system.status',
                'type' => 'boolean',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true,
            ],
            [
                'name' => 'sort',
                'title' => 'sslcommerz::app.sslcommerz.system.sort_order',
                'type' => 'select',
                'options' => [
                    [
                        'title' => '1',
                        'value' => 1,
                    ], [
                        'title' => '2',
                        'value' => 2,
                    ], [
                        'title' => '3',
                        'value' => 3,
                    ], [
                        'title' => '4',
                        'value' => 4,
                    ],
                ],
            ],
        ],
    ],
];
