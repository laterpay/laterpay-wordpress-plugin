<?php

return array (
    // Name of group of registered users in your WordPress installation
    // that gets unrestricted access to paid posts;
    // Can be used to give donators, students, subscribers etc. free full access
    'LATERPAY_ACCESS_ALL_ARTICLES_GROUP' => '',

    // File types protected against direct download from paid posts without purchasing
    'LATERPAY_PROTECTED_FILE_TYPES' =>    'docx|doc|gif|jpeg|jpg|pdf|png|pptx|ppt|rar|rtf|tiff|tif|txt|xlsx|xls|zip',

    // Settings for generating excerpts of paid content shown to visitors
    'LATERPAY_AUTO_GENERATED_TEASER_CONTENT_WORD_COUNT' => 120,
    'LATERPAY_PAID_CONTENT_PREVIEW_WORD_COUNT' =>          400,

    // Access logging for generating sales statistics within the plugin;
    // Sets a cookie and logs all requests from visitors to your blog, if enabled
    'LATERPAY_ACCESS_LOGGING_ENABLED' =>   true,

    // Debugging
    'LATERPAY_LOGGER_ENABLED' =>           false,
    'LATERPAY_LOGGER_FILE' =>              '/var/log/laterpay_api.log',


    // #############################################################################
    // DO NOT CHANGE THE FOLLOWING PARAMETERS UNLESS YOU REALLY REALLY KNOW
    // WHAT YOU ARE DOING AND HAVE A VERY GOOD REASON FOR DOING SO
    // #############################################################################

    // Encryption parameters
    'LATERPAY_SALT' =>                     '{LATERPAY_SALT}',
    'LATERPAY_COOKIE_TOKEN_NAME' =>        'token',
    'LATERPAY_RESOURCE_ENCRYPTION_KEY' =>  '{LATERPAY_RESOURCE_ENCRYPTION_KEY}',

    // Path to images, CSS, JS, and font files
    'LATERPAY_ASSET_PATH' =>               WP_PLUGIN_URL . '/' . LATERPAY_BASE_NAME . '/static',

    // Parameters for plugin auto-update functionality
    'LATERPAY_GITHUB_PROJECT_NAME' =>      'laterpay-wordpress-plugin',
    'LATERPAY_GITHUB_USER_NAME' =>         'laterpay',
    'LATERPAY_GITHUB_TOKEN' =>             '',

    // LaterPay URLs
    'LATERPAY_SANDBOX_API_URL' =>          'https://api.sandbox.laterpaytest.net',
    'LATERPAY_SANDBOX_WEB_URL' =>          'https://web.sandbox.laterpaytest.net',
    'LATERPAY_LIVE_API_URL' =>             'https://api.laterpay.net',
    'LATERPAY_LIVE_WEB_URL' =>             'https://web.laterpay.net',
    'LATERPAY_MERCHANTBACKEND_URL' =>      'https://merchant.laterpay.net/',

    // Initial values for currency, price, and tax
    'LATERPAY_CURRENCY_DEFAULT' =>         'EUR',
    'LATERPAY_GLOBAL_PRICE_DEFAULT' =>     0.29,
    'LATERPAY_VAT' =>                      'DE19'
);
