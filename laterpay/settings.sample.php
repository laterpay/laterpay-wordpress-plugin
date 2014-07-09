<?php

return array (
    // Name of group of registered users in your WordPress installation
    // that gets unrestricted access to paid posts;
    // Can be used to give donators, students, subscribers etc. free full access
    'LATERPAY_ACCESS_ALL_ARTICLES_GROUP'                    => '',

    // File types protected against direct download from paid posts without purchasing
    'LATERPAY_PROTECTED_FILE_TYPES'                         => 'docx|doc|gif|jpeg|jpg|pdf|png|pptx|ppt|rar|rtf|tiff|tif|txt|xlsx|xls|zip',

    // Number of words used for automatically extracting teaser content for paid posts
    'LATERPAY_AUTO_GENERATED_TEASER_CONTENT_WORD_COUNT'     => 60,
    // Number of words of actual paid content displayed under semitransparent overlay in preview mode "teaser + overlay"
    // Three parameters can be defined:
    // - percentage of content to be extracted (values: 1-100); 20 means "extract 20% of the total number of words of the post"
    // - MINimum number of words; applied if number of words as percentage of the total number of words is less than this value
    // - MAXimum number of words; applied if number of words as percentage of the total number of words exceeds this value
    'LATERPAY_PAID_CONTENT_PREVIEW_PERCENTAGE_OF_CONTENT'   => 25,
    'LATERPAY_PAID_CONTENT_PREVIEW_WORD_COUNT_MIN'          => 26,
    'LATERPAY_PAID_CONTENT_PREVIEW_WORD_COUNT_MAX'          => 200,

    // Use page caching compatible mode
    // Set this to true, if you are using a caching solution like WP Super Cache that caches entire HTML pages;
    // In compatibility mode the plugin renders paid posts without the actual content so they can be cached as static
    // files and then uses an Ajax request to load either the preview content or the full content,
    // depending on the current visitor
    'LATERPAY_PAGE_CACHING_COMPATIBLE_MODE'                 => '{SITE_USES_PAGE_CACHING}',

    // Access logging for generating sales statistics within the plugin;
    // Sets a cookie and logs all requests from visitors to your blog, if enabled
    'LATERPAY_ACCESS_LOGGING_ENABLED'                       => true,

    // Auto-update browscap library
    // The plugin requires browscap to ensure search engine bots, social media sites, etc. don't crash when visiting a paid post
    // When set to true, the plugin will automatically fetch updates of this library from browscap.org
    'LATERPAY_BROWSCAP_AUTOUPDATING'                        => true,
    // If you can't or don't want to enable automatic updates, you can provide the full path to a browscap.ini file
    // on your server that you update manually from http://browscap.org/stream?q=PHP_BrowsCapINI
    'LATERPAY_BROWSCAP_MANUALLY_UPDATED_COPY'               => '',

    // Debugging
    'LATERPAY_LOGGER_ENABLED'                               => false,
    'LATERPAY_LOGGER_FILE'                                  => '/var/log/laterpay_api.log',

    // Encryption parameters
    'LATERPAY_SALT'                                         => '{salt}',
    'LATERPAY_COOKIE_TOKEN_NAME'                            => 'token',
    'LATERPAY_RESOURCE_ENCRYPTION_KEY'                      => '{resource_encryption_key}',
);
