<?php

if ( file_exists(LATERPAY_GLOBAL_PATH . 'settings.php') ) {
    $user_settings = include(LATERPAY_GLOBAL_PATH . 'settings.php');
} else {
    $user_settings = array();
}

return array_merge(array(
    // #############################################################################
    // THESE PARAMETERS WILL BE OVERWRITTEN AFTER PLUGIN UPDATE, USE "settings.php" FILE INSTEAD
    // #############################################################################

    // Path to images, CSS, JS, and font files
    'LATERPAY_ASSETS_PATH' =>               WP_PLUGIN_URL . '/' . LATERPAY_BASE_NAME . '/assets',

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
    'LATERPAY_VAT' =>                      'DE19',

    // Default Sandbox API credentials for easy tryouts
    'LATERPAY_DEFAULT_SANDBOX_MERCHANT_ID' => 'LaterPay-WordPressDemo',
    'LATERPAY_DEFAULT_SANDBOX_API_KEY' =>  'decafbaddecafbaddecafbaddecafbad',
), $user_settings);
