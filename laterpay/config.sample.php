<?php

// Name of group of registered users in your WordPress installation
// that gets unrestricted access to paid posts;
// Can be used to give donators, students, subscribers etc. free full access
define('LATERPAY_ACCESS_ALL_ARTICLES_GROUP', '');

// File types protected against direct download from paid posts without purchasing
define('LATERPAY_PROTECTED_FILE_TYPES',    'docx|doc|gif|jpeg|jpg|pdf|png|pptx|ppt|rar|rtf|tiff|tif|txt|xlsx|xls|zip');

// Settings for generating excerpts of paid content shown to visitors
define('LATERPAY_AUTO_GENERATED_TEASER_CONTENT_WORD_COUNT', 120);
define('LATERPAY_PAID_CONTENT_PREVIEW_WORD_COUNT',          400);

// Access logging for generating sales statistics within the plugin;
// Sets a cookie and logs all requests from visitors to your blog, if enabled
define('LATERPAY_ACCESS_LOGGING_ENABLED',   true);

// Debugging
define('LATERPAY_LOGGER_ENABLED',           false);
define('LATERPAY_LOGGER_FILE',              '/var/log/laterpay_api.log');


// #############################################################################
// DO NOT CHANGE THE FOLLOWING PARAMETERS UNLESS YOU REALLY REALLY KNOW
// WHAT YOU ARE DOING AND HAVE A VERY GOOD REASON FOR DOING SO
// #############################################################################

// Encryption parameters
define('LATERPAY_SALT',                     '{LATERPAY_SALT}');
define('LATERPAY_COOKIE_TOKEN_NAME',        'token');
define('LATERPAY_RESOURCE_ENCRYPTION_KEY',  '{LATERPAY_RESOURCE_ENCRYPTION_KEY}');

// Path to images, CSS, JS, and font files
define('LATERPAY_ASSET_PATH',               WP_PLUGIN_URL . '/' . LATERPAY_BASE_NAME);

// Parameters for plugin auto-update functionality
define('LATERPAY_GITHUB_PROJECT_NAME',      'laterpay-wordpress-plugin');
define('LATERPAY_GITHUB_USER_NAME',         'laterpay');
define('LATERPAY_GITHUB_TOKEN',             '');

// LaterPay URLs
define('LATERPAY_SANDBOX_API_URL',          'https://api.sandbox.laterpaytest.net');
define('LATERPAY_SANDBOX_WEB_URL',          'https://web.sandbox.laterpaytest.net');
define('LATERPAY_LIVE_API_URL',             'https://api.laterpay.net');
define('LATERPAY_LIVE_WEB_URL',             'https://web.laterpay.net');
define('LATERPAY_MERCHANTBACKEND_URL',      'https://merchant.laterpay.net/');

// Initial values for currency, price, and tax
define('LATERPAY_CURRENCY_DEFAULT',         'EUR');
define('LATERPAY_GLOBAL_PRICE_DEFAULT',     0.29);
define('LATERPAY_VAT',                      'DE19');
