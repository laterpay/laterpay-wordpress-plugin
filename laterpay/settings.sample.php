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

    // Encryption parameters
    'LATERPAY_SALT' =>                     '{LATERPAY_SALT}',
    'LATERPAY_COOKIE_TOKEN_NAME' =>        'token',
    'LATERPAY_RESOURCE_ENCRYPTION_KEY' =>  '{LATERPAY_RESOURCE_ENCRYPTION_KEY}',
);
