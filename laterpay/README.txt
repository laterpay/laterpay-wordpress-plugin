=== LaterPay ===

Contributors: laterpay, dominik-rodler, mihail-turalenka
Tags: laterpay, accept micropayments, accept payments, access control, billing, buy now pay later, content monetization, creditcard, debitcard, free to read, laterpay for wordpress, laterpay payment, laterpay plugin, micropayments, monetize, paid content, pay button, pay per use, payments, paywall, PPU, sell digital content, sell digital goods, single sale, wordpress laterpay
Requires at least: 3.5.2
Tested up to: 4.0
Stable tag: trunk
Author URI: https://laterpay.net
Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
License: MIT
License URI: http://opensource.org/licenses/MIT

Sell digital content with LaterPay. It allows super easy and fast payments from as little as 5 cent up to 149.99 Euro at a 15% fee and no fixed costs.


== Description ==

The LaterPay WordPress plugin offers the following features:

= Pricing =
The plugin allows you to set different price types for your blog posts:

* Global default price: This price is by default applied to all new and existing posts of the blog.
* Category default price: This price is applied to all new and existing posts in a given category.
     If a category default price is set, it overwrites the global default price.
     E.g. setting a category default price of 0.00 Euro, while having set a global default price of 0.49 Euro,
     makes all posts in that category free.
* Individual price: This price can be set for each post.
     It overwrites both the category default price and the global default price for the respective article.
     E.g. setting an individual price of 0.19 Euro with a category default price of 0.10 Euro and a global
     default price of 0.00 Euro results in a price for that post of 0.19 Euro.

You may also change the plugin's currency and apply a dynamic pricing scheme to posts:

* Default Currency: The plugin allows you to set the default currency for your blog.
  Changing the default currency will not change the prices you have set, but only the currency code
  that is displayed next to the price.
* Dynamic Pricing: For every single post, you can set a price curve that changes the price of a blog post
  over time, starting from the publication date.
  E.g. you can offer a breaking news post for 0.49 Euro for the first two days and then automatically reduce the price
  to 0.05 Euro until the fith day to increase your sales.

= Presentation =
* LaterPay button: Each post with a price > 0.00 Euro automatically contains a LaterPay button at the beginning of the
  post content. You can choose to not show this button and instead render it from within your theme by calling
  <?php do_action( 'laterpay_purchase_button' ); ?> within your theme.
* Teaser content: Every post you sell with LaterPay has to contain a teaser.
  The teaser is shown to the user before he has purchased a post.
  The plugin automatically generates teaser content by taking the first 120 words of every existing post.
  You can refine the teaser content on the ‘Add / Edit Post’ page.

You have the choice between two presentation modes for your teaser content:

* Teaser only: This mode shows only the teaser with an unobtrusive purchase link below.
* Teaser + overlay: This mode shows the teaser, an excerpt of the full content under a semi-transparent overlay
     that briefly explains LaterPay's benefits. The plugin never loads the full content before a user has bought it.

Furthermore, the plugin provides:

* LaterPay invoice indicator: The plugin provides a code snippet you can insert into your theme that displays
  the user's current LaterPay invoice total and provides a direct link to his LaterPay user backend.
  You don't have to integrate this snippet, but we recommend it for transparency reasons.
* Support for all standard post types and custom post types.
* Full localization: The plugin is fully localized for English and German.

= Security =
File protection: The plugin secures files in paid posts against downloading them via a shared direct link.
So even if a user purchases a post and shares the direct link to a contained file, other users won't be able
to access that file, unless they've already bought the post.

= Crawler Friendliness =
* Social media: The plugin supports Facebook, Twitter, and Google+ crawlers, so it won't hurt your social media reach.
* Google and Google News: The plugin also supports Google and Google News crawlers.
  They will never have access to the full content but only to your teaser content.
  So depending on the presentation mode you've chosen, Google will access only the teaser content or
  the teaser content plus an excerpt of the full content.

= Caching Compatibility =
The plugin automatically detects if one of the available WordPress caching plugins (WP Super Cache, W3 Total Cache,
Quick Cache, WP Fastest Cache, Cachify, WP-Cache.com) are active and sets the config-key `caching.compatible_mode`
accordingly. If the site is in page caching compatibly mode, the post page is rendered without the actual post content,
which the plugin then requests using Ajax. If the user has not purchased the post already, only the teaser content and
the purchase button are displayed.

= Test and Live Mode =
* Test mode: The test mode lets you test your plugin configuration. While providing the full plugin functionality,
  no real transactions are processed. We highly recommend to configure and test the integration of the LaterPay
  WordPress plugin into your site on a test system, not on your production system.
* Live mode: After integrating and testing the plugin, you might want to start selling content and process real
  transactions. Mail us the signed merchant contract and the necessary identification documents and we will send you
  LaterPay API credentials for switching your plugin to live mode.

= Statistics =
If you open a post as a logged-in admin (or user with adequate rights), you will see a statistics tab with the
following data about the respective post:

* Total sales: The total number of sales of this particular post
* Total revenue: The total revenue of this particular post
* Today's revenue
* Today's visitors
* Today's conversion rate: The share of visitors that actually purchased
* History charts for sales, revenue, and conversion rate of the last 30 days

Please note that the provided statistics are only indicators and not binding in any way.

= Roles and Capabilities =
Some plugin features may not be available for certain user roles, based on the WordPress model of roles and capabilities:

* Subscribers (and regular, non-registered visitors): **cannot change any** plugin settings
* Contributors: can edit the teaser content and see the statistics of their **own** posts
* Authors: can **additionally** edit the individual prices of their **own** posts
* Editors: can edit the teaser content and individual prices of **all** posts and can see the statistics of **all** posts
* Super Admins and Admins: Can **additionally** access the plugin backend, edit the plugin settings and (un-)install and (de-)activate the LaterPay WordPress plugin.


== Installation ==

# Upload the LaterPay WordPress plugin on the ‘Install Plugins’ page of your WordPress installation
  (/wp-admin/plugin-install.php?tab=upload) and activate it on the ‘Plugins’ page (/wp-admin/plugins.php).
  The WordPress plugin will show up in the admin sidebar with a callout pointing at it.
# Click on the LaterPay entry in the admin sidebar. You will be taken to the ‘Get Started’ page.
# Choose a global default price on the ‘Get Started’ page. This price will be set for all your blog posts.
  If you choose 0.00 Euro, all posts remain free. You can later adjust your prices in detail.
  After clicking the ‘Activate LaterPay in Test Mode’ button, LaterPay is active on your blog in Test mode.
  In Test mode, the plugin is not visible to visitors, but only to admins.
  You can test and configure everything to your liking.
  If you want to start earning money, you have to first register a LaterPay merchant account and request your
  Live API credentials.

The plugin will notify you about available updates that you can install with a single click.


== Advanced Configuration ==

The plugin has a number of advanced configuration options that are not available from the graphical user interface, but
instead must be set using filters.

# Edit API Settings

## Filter

'laterpay_get_api_settings'

## Example

``php
add_filter( 'laterpay_get_api_settings', 'demo_filter_laterpay_get_api_settings' );
/**
 * Demo callback to change the settings
 *
 * @param array $settings array(
 *    'api.sandbox_url'           => String,
 *    'api.sandbox_web_url'       => String
 *    'api.live_url'              => String
 *    'api.live_web_url'          => String
 *  )
 *
 * @return array $settings
function demo_filter_laterpay_get_api_settings( array $settings ) {
  /* do something with settings */

  return $settings;
}
``

# Activate / Deactivate Caching-compatible Mode

## Filter

'laterpay_get_caching_compatible_mode'

## Example

``php
add_filter( 'laterpay_get_caching_compatible_mode', 'demo_filter_laterpay_get_caching_compatible_mode' );
/**
 * Demo callback to manually set the caching mode
 *
 * @param bool $mode  - true = activated | false = deactivated
 *
 * @return bool $mode
*/
function demo_filter_laterpay_get_caching_compatible_mode( $mode ) {
  if ( /* something */ ) {
    $mode = true;
  } else {
    $mode = false;
  }

  return $mode;
}
``

# Edit Content Settings

## Filter

'laterpay_get_content_settings'

## Example

``php
add_filter( 'laterpay_get_content_settings', 'demo_filter_laterpay_get_content_settings' );
/**
 * Demo callback to change the settings
 *
 * @param Array $settings array(
 *    'content.auto_generated_teaser_content_word_count'  => Integer - Number of words used for automatically extracting teaser content for paid posts,
 *    'content.preview_percentage_of_content'              => Integer - percentage of content to be extracted (values: 1-100); 20 means "extract 20% of the total number of words of the post",
 *    'content.preview_word_count_min'                     => Integer - MINimum number of words; applied if number of words as percentage of the total number of words is less than this value,
 *    'content.preview_word_count_max'                     => Integer - MAXimum number of words; applied if number of words as percentage of the total number of words exceeds this value,
 *    'content.enabled_post_types'                         => Array - allowed post_types that support LaterPay purchases
 *    'content.show_purchase_button'                       => Boolean - show / hide the purchase button before the teaser content
 *  )
 * @return Array $settings
 */
function demo_filter_laterpay_get_content_settings( array $settings ) {
  /* do something with settings */

  return $settings;
}
``

# activate / Deactivate Access Logging

## Filter
'later_pay_access_logging_enabled'

## Example

``php
add_filter( 'later_pay_access_logging_enabled', 'demo_filter_later_pay_access_logging_enabled' );
/**
 * Demo callback to manually set the logging mode
 *
 * @param bool $logging - true = activated | false = deactivated
 *
 * @return bool $logging
*/
function demo_filter_laterpay_later_pay_access_logging_enabled( $logging ) {
  if ( /* something */ ) {
    $logging = true;
  } else {
    $logging = false;
  }

  return $logging;
}
``

# Edit Browscap Settings

## Filter
'laterpay_get_browscap_settings'

## Example

``php
add_filter( 'laterpay_get_browscap_settings', 'demo_filter_laterpay_get_browscap_settings' );
/**
 * Demo callback to change the settings
 *
 * @param Array $settings array(
 *    'browscap.autoupdate'             => Boolean - When set to true, the plugin will automatically fetch updates of this library from browscap.org
 *    'browscap.manually_updated_copy'  => String - If you can't or don't want to enable automatic updates, you can provide the full path to a browscap.ini file on your server
 *  )
 *
 * @return Array $settings
 */
function demo_filter_laterpay_get_browscap_settings( array $settings ) {
  /* do something with settings */

  return $settings;
}
``

== Modification, Bug Reports, and Feature Requests ==

The LaterPay WordPress plugin is one possible implementation of the LaterPay API that is targeted at the typical
needs of bloggers and small to medium-sized online magazines.
You can — and are highly welcome — to modify the LaterPay plugin to fit your requirements.

If you are an expert WordPress user who is comfortable with web technologies and want to explore every last possibility
of the LaterPay API, you may be better off by modifying the plugin or writing your own integration from scratch.
As a rule of thumb, if you employ a whole team of developers, it is very likely that you may want to make a few
modifications to the LaterPay WordPress plugin.

If you have made a modification that would benefit other users of the LaterPay WordPress plugin, we will happily have a
look at your work and integrate it into the official plugin.
If you want to suggest a feature or report a bug, we are also looking forward to your message to wordpress@laterpay.net


== Frequently Asked Questions ==

= Contextual Help =
The LaterPay WordPress Plugin supports contextual help, so you will have all the information at hand right where and
when you need it. Contextual help for the current page is available via the ‘Help’ tab on the top of the respective page.

= Knowledge Base =
You can find further information about LaterPay and the LaterPay WordPress plugin in the LaterPay Knowledge Base on
support.laterpay.net

= How do I get my LaterPay Live API credentials? =
To get your LaterPay Live API credentials, please send us the signed merchant contract and all necessary identification
documents that are listed in the merchant contract. After we've checked your documents, we will send you an e-mail with
your LaterPay Live API credentials.

= My theme looks broken after activating the LaterPay plugin =
The LaterPay WordPress plugin has been tested to work fine with most WordPress standard themes.
Given the sheer mass of available WordPress themes, it is still likely that you will have to make some adjustments to
your theme after installing the LaterPay WordPress plugin.


== Screenshots ==

1. Get started with three simple steps.
2. LaterPay lets you easily enter teaser content and set an individual or...
3. Dynamic price for your blogposts.
4. In the Pricing tab, you can set default prices for the entire blog or specific categories.
5. In the Appearance tab, you can choose between two preview modes for your content.
6. Option 1 shows only a post's teaser content and a LaterPay purchase link.
7. Option 2 additionally shows an excerpt of the full content under an overlay explaining LaterPay.
8. The Account tab lets you enter, update, or delete your API credentials and switch between test and live mode.
9. The statistics pane provides sales statistics for each post.


== Changelog ==

= 0.9.8 (September 30, 2014): Single Sales Release =
* Added option to sell content as single sale (SIS), allowing prices up to 149.99 Euro
* Added configuration option for enabled post types in appearance tab
* Added the action 'laterpay_invoice_indicator' to render the invoice indicator from within a theme
* Huge improvements on RAM consumption and CPU usage
* Ensured compatibility with WordPress 4.0
* Added plugin icon for WordPress 4.0 plugins page
* Rewrote all CSS using Stylus CSS preprocessor
* Rewrote all Javascript to encapsulate all variables and functions
* Added hint text for premium posts to feeds
* Fixed bug caused by checking for edit_plugins capability, which might be disabled
* Restricted querying for categories to taxonomy 'category'
* Improved uninstall action
* Extracted LaterPay PHP client into separate repository and included it as vendor library
* Fixed paths to LaterPay libraries depending on plugin mode
* Extensive refactoring plus various smaller bugfixes and improvements

= 0.9.7.2: Migration to wordpress.org =

= 0.9.7.1 (August 13, 2014): Bugfix Release =
* Removed GitHub plugin updater to switch plugin over to wordpress.org plugin release channel
* Fixed bugs in multi-layer pricing logic (global default, category default, individual price)
* Fixed minor bug on post add / edit page that would trigger a Javascript confirm message when saving
* Revised user interface to work on tablet resolutions
* Fixed preview mode for paid posts
* Disabled autoupdates for Browscap and removed requirements check for writable cache folder
* Disabled rendering of post statistics, if a page includes multiple single post pages
* LaterPay CSS and JS assets are now only loaded, if a post can be purchased
* Various smaller bugfixes and improvements

= 0.9.7 (August 8, 2014): Production-readiness release IV =
* Added support for all standard as well as custom post types
* Instead of modifying the_title, we now prepend a purchase button to the post content to prevent various compatibility issues
* Added the action 'laterpay_purchase_button' to render the purchase button from within a theme
* Added shortcode to align premium content shortcodes
* Changed advanced settings mechanism from file-based to WordPress filters
* Increased robustness of installation and activation procedure
* Replaced custom code with native WordPress functions wherever possible
* Improved performance / reduced memory footprint of plugin
* Improved security of plugin (validation, sanitizing, access to files)
* Prefixed all class names, variables etc. to avoid collisions with other plugins
* Changed internal coding style to adhere to WordPress standards
* Lots of smaller bugfixes and improvements

= 0.9.6 (July 21, 2014): Production-readiness release III =
* Included public Sandbox API credentials supplied by default
* Fully implemented planned roles and capabilities model
* Revised pricing form in add / edit post page
* Removed superfluous handle from dynamic pricing widget
* Added shortcode to render nicely styled links to premium content related to a post
* Added contextual help to all backend pages
* Fixed problem where re-activating the plugin forwarded to the getStarted tab
* Added submenu links to the admin menu
* Added two columns to posts table that indicate price and price type of each post
* Tested and established compatibility with PHP 5.2.4
* Revised README to comply with WordPress standards
* Added option to switch off auto-updating of browscap
* Secured plugin folders against external access
* Extended list of protected filetypes by popular audio, video, and ebook filetypes
* Prefixed all classes and functions with 'LaterPay'
* Improved requirements check during installation
* Several smaller bugfixes

= 0.9.5.1 (July 10, 2014): Bugfix release =
* Fixed purchase button
* Fixed rendering of paid posts overlay on smartphones
* Added option to choose between automatic and manual updating of browser detection library browscap
* Secured plugin folders against external access by adding an empty index.php file to each folder
* Added versioning to LaterPay icon font to ensure cache invalidation on updates

= 0.9.5 (July 9, 2014): Production-readiness release II =
* Made plugin compatible with page caching solutions like WP Super Cache
* Redesigned overlay for previewing paid content
* Added more fine grained over amount of text previewed behind overlay
* Bugfix for auto-updating of browser detection library
* Improved internal use of standard WP APIs (transport, wp_localize_script)
* Added price of posts to posts table in admin backend
* Added more flash messages for system feedback
* Ensured the Buyers bar chart properly scales to 1 (100%)
* Added possibility to hide / show the statistics pane on the view post page
* Switched to loading minified version of YUI
* Renamed views and several variables to be more self-explanatory
* Added an already cached copy of browscap library to the plugin
* Added uninstall.php file that takes care of wiping the database from all data added by the plugin,
  when the plugin gets deactivated and then uninstalled
* Fixed notices that broke the activation process in debug mode
* Fixed bug in getStarted tab that showed an error message that Merchant ID or API Key is not valid, if it was not entered yet

= 0.9.4.2 (June 29, 2014): Bugfix release =
* Removed superfluous function argument for saving the teaser content that caused a warning

= 0.9.4.1 (June 28, 2014): Bugfix release =
* Fixed visibility of plugin to visitors in test mode

= 0.9.4 (June 27, 2014): Production-readiness release =
* Modified behaviour of plugin to be not visible to visitors in test mode
* Added switch to post page, to allow admin users to preview their settings like a visitor
* Added mechanism to ensure that configurations are properly migrated on plugin updates
* Updated price validation to comply with the LaterPay terms and conditions for Pay-per-Use (0.05 - 5.00 Euro)
* Removed questions callout from account tab
* Applied a few visual fixes

= 0.9.3.3 (June 26, 2014): Post-migration release =
* Updated LATERPAY_ASSETS_PATH constant to include '/static'

= 0.9.3.2 (June 26, 2014): Pre-migration release =
* Updated configuration for auto-update functionality to allow migration to new public repo

= 0.9.3.1 (June 25, 2014): Bugfix release =
* Fixed loading of YUI library
* Several smaller visual fixes

= 0.9.3 (June 25, 2014): Code quality release =
* Dramatically reduced memory consumption of browser detection and added auto-updating for browser detection library
* Fixed bug that caused free images to be encrypted
* Fixed bug related to loading API key
* Restricted API calls and other plugin activity to paid posts
* Improved documentation
* Added LaterPay contracts for requesting LaterPay Live API credentials to Account tab
* Made logging function compatible with IPv6
* Refactored plugin to properly register and enqueue Javascript and CSS files
* Added handling for invalid prices
* Added option to define file types protected against direct download in config.php
* Refactored laterpay.php and several controllers
* Removed Javascript and CSS files that are not used anymore

= 0.9.2 (June 13, 2014): Bugfix release =
* Fixed visual glitches of switch

= 0.9.1 (June 13, 2014): Code quality release =
* Removed vendor libraries for HTTP requests and switched to using native WP functionality

= 0.9 (June 11, 2014): Improved maintenance release =
* Added mechanism for automatic plugin updates from official LaterPay repository on github
* Added mechanism for migrating the database on plugin updates
* Added mechanism for clearing application caches on plugin updates
* Added mechanism to prevent config.php from being deleted on plugin updates
* Added requirements check on plugin installation
* Improved layout of account tab in plugin backend
* Improved German translations

= 0.8.2 (June 5, 2014): Bugfix release =
* Extended truncate function to remove HTML comments when auto-generating teaser content
* Made sure flash message warning about missing teaser content is visible
* Removed useless wrapper div#post-wrapper in singlePost
* Added functionality to generate config.php with unique salt and resource encryption key from config.sample.php on setup
* Fixed database error in statistics logging that occurs if one user visits a post multiple times on the same day

= 0.8.1 (June 4, 2014): Bugfix release =
* Made plugin backwards compatible with PHP >= 5.2
* Added rendering of invoice indicator HTML snippet to appearance tab
* Changed auto-generation of teaser content from batch creation on initialization of plugin to on-demand creation on first view or edit of post
* Added pointers to hint at key functions
* Fixed bug related to printing
* Exchanged full version of browscap.ini by its much smaller standard version

= 0.8 (May 27, 2014): First release for beta customers =
* Updated LaterPay PHP client to API v2
* Added separate inputs for Sandbox Merchant ID and Live Merchant ID to Account tab
* Changed Merchant ID input in Get Started tab to Sandbox Merchant ID input
* Added a simple passthrough script that checks authorization for file downloads
* Added a constant to config.php that lets you define a user role that has unrestricted access to all paid content
* Added script that doesn't load jQuery if it's already present
* Changed treatment of search engine bots to avoid cloaking penalties; removed toggle for search engine visibility from appearance tab
