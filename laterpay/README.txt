=== LaterPay ===

Contributors: laterpay, dominik-rodler, mihail-turalenka
Tags: laterpay, accept micropayments, accept payments, access control, billing, buy now pay later, content monetization, creditcard, debitcard, free to read, laterpay for wordpress, laterpay payment, laterpay plugin, micropayments, monetize, paid content, pay button, pay per use, payments, paywall, PPU, sell digital content, sell digital goods, single sale, wordpress laterpay
Requires at least: 3.5.2
Tested up to: 4.2.2
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

With time passes, you can sell time-limited access to all the LaterPay content
* on your entire website
* in a specific category
* on your entire website except for a specific category.
The user will have access to all the covered content during the validity period and afterwards, this access will expire automatically.
Time passes are displayed within a dedicated sidebar widget that automatically sorts available time passes by relevance.
Be careful when deleting a time pass: Users, who have bought the respective time pass, will lose the access to the covered content. Deleted time passes cannot be restored.

For each time pass, you can create any number of voucher codes that enable your users to purchase a time pass for a reduced price.
A user can enter a voucher code in the time pass sidebar widget by clicking "I have a voucher". The price for the respective time pass will then be updated.
Voucher codes are not user specific and can be used for any number of times until you delete them. Deleting a voucher code will not affect the access to time passes which have already been purchased with this code.

The plugin also comes with a mighty bulk price editor: It allows you to change many prices at once by
* making all posts free
* setting one specific price for all posts
* increasing or decreasing the prices of all posts by a specific percentage or absolute amount
* resetting all posts to the global default price
Whenever possible, the plugin will maintain the current pricing structure: So if possible, after a bulk action
* posts with individual price will still use the individual price
* posts with category default price will still use the category default price and
* posts with global default price will still use the global default price.

If you change all prices by a certain percentage, the lower limit is always 0.05 EUR - so rounding will never accidentally make posts free.


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

The plugin provides two shortcodes that will allow you to sell additional content directly from within another post:

* [laterpay_premium_download] renders a 300px x 300px box containing information about a linked premium content and a LaterPay purchase button. A user can purchase the linked content directly via this shortcode. You can sell attachments from your WordPress media library or other posts via a shortcode. If a user purchases an attachment via a shortcode, it will be downloaded after the purchase. If he purchases another post, he will be redirected to that post after the purchase.
* [laterpay_box_wrapper] aligns multiple [laterpay_premim_download] boxes.
Please note: Shortcodes and the respective parameters are extensively documented in the "appearance" tab.

Furthermore, the plugin provides:

* Content Rating: If you enable content rating, users who have purchased a post, will be able to rate it on a five star scale. Users, who haven't bought a post yet will see a summary of all prior ratings below the LaterPay purchase button.
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
* Super Admins and Admins: Can **additionally** access the plugin backend, edit the plugin settings and (un-)install
and (de-)activate the LaterPay WordPress plugin.


== Installation ==

# Upload the LaterPay WordPress plugin on the ‘Install Plugins’ page of your WordPress installation
  (/wp-admin/plugin-install.php?tab=upload) and activate it on the ‘Plugins’ page (/wp-admin/plugins.php).
  The WordPress plugin will show up in the admin sidebar with a callout pointing at it.
# The plugin is now in Test mode, i.e. the plugin is not visible to visitors, but only to admins.
  You can test and configure everything to your liking.
  If you want to start earning money, you have to first register a LaterPay merchant account and request your
  Live API credentials.
# Click on the LaterPay entry in the admin sidebar to adjust the plugin preferences and prices.

The plugin will notify you about available updates that you can install with a single click.


== Advanced Configuration ==

The plugin's settings page (Settings > LaterPay) allows you to adjust some parameters, which are too advanced to list
them in the regular LaterPay plugin backend:

= Caching Compatibility Mode =
The plugin detects on activation or update, if you are using one of the common caching plugins and automatically
switches to caching compatibility mode.
You can manually turn this mode on (or off), if you have installed a caching plugin after installing the LaterPay
plugin or if you are using a caching plugin that is not detected.

= LaterPay-enabled Post Types =
You can enable or disable LaterPay for any standard or custom post type. By default, LaterPay is enabled for all
post types.

= Automatically Generated Teaser Content =
The plugin will automatically generate teaser content, if you leave the teaser empty.
This functionality was introduced to handle the case that the LaterPay plugin is installed to monetize a large number
of existing posts and it would be too much effort to create individual teaser content or that work simply has not yet
been done. With the setting in this section, you can control, how many words of the full content the plugin should use
as teaser content. (E.g. 500 will use the first 500 words of the full content as teaser content,
if there is no teaser content.)

= Excerpt under Teaser Overlay =
If you choose the preview mode "Teaser + excerpt of the full text under overlay" in the appearance tab,
you can define the length of the excerpt under the overlay with the three settings in this section.

= Unlimited Access to Paid Content =
This setting gives all logged in users with a specific role full access to all paid content on your website.
To use this feature, you have to create at least one custom user role (e.g. with the free plugin 'User Role Editor')
and add the respective users to this group.

= Access Logging for Generating Sales Statistics =
By default, the plugin will store anonymous usage and sales data on your server to provide sales statistics.
This data will not be sent to LaterPay and will be automatically deleted after three months.
If you don't need any sales statistics, you can disable the access logging in this section.

= LaterPay API URLs =
Attention: This is an option primarily used for LaterPay's demo and test purposes:
Changing the API endpoints (more precisely: using the sandbox endpoints as live endpoints) makes the plugin
(e.g. on laterpaydemo.com) behave like in live mode while still talking to the sandbox environment.
We highly discourage changing the default setting.


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
documents that are listed in the merchant contract. You can find the merchant contract on the ’Account’ tab of the
plugin backend. After we've checked your documents, we will send you an e-mail with your LaterPay Live API credentials.


== Screenshots ==

1. LaterPay lets you easily enter teaser content and set an individual price for a post starting at 0.05 EUR ...
2. ... up to 149.99 EUR. Or you may set a dynamic price curve, use a category default price, or the global default price.
3. The statistics dashboard gives you detailed insights about your sales performance.
4. In the Pricing tab, you can set the default prices for the entire plugin or specific categories. And you can create time passes, which enable you to sell time-limited access to all the content on your website or in a specific category.
5. The appearance tab allows you to adjust the position of the purchase button and time passes. Furthermore, you can choose between two preview modes for your paid content.
6. Option 1 shows only a post's teaser content and a LaterPay purchase link.
7. Option 2 additionally shows an excerpt of the full content under an overlay and a short explanation of LaterPay.
8. The Account tab lets you enter, update, or delete your API credentials and switch between test and live mode. In test mode, you can choose, if LaterPay should be visible for regular visitors or not.
9. The plugin comes with its own debugger.

== Changelog ==

= 0.9.12 (July 8, 2015): Bugfix Release (v1.0 RC5) =
* Added feature to allow setting prices in time pass only mode
* Added advanced setting to not contact LaterPay on the homepage
* Added avanced setting to disable check_token on homepage
* Disabled sales statistics
* Fixed fatal error after plugin activation
* Fixed issue with special characters in time pass URLs
* Fixed time Passes being displayed for users, but not in the pricing tab
* Fixed bug that prevented to create voucher code while creating time pass
* Fixed warning: "Cannot modify header information - headers already sent"
* Limited validity of time passes to 1 year
* Fixed images not being displayed in print preview / not printed in Internet Explorer
* Fixed state of "Time Passes Only"-toggle not saving
* Fixed duplicate entries in database
* Adjusted calculation of New Customers metric

= 0.9.11.4 (May 8, 2015): Bugfix Release (v1.0 RC4) =
* Completely revised plugin backend user interface with clearer layout and smoother user interaction
* Added functionality to automatically remove logged page view data after three months
* Added advanced option to manually update the Browscap database from the advanced settings page
* Added advanced option to define the plugin behavior in case the LaterPay API is not responding
* Improved behavior of deleting time passes (only mark as deleted instead of actually removing from database)
* Changed mechanism for including vendor libraries from git submodules to Composer
* Fixed several internals regarding the calculation of sales statistics
* Adjusted copy in teaser content overlay for Time Passes and Single Sale purchases
* Fixed various visual bugs
* Lots of internal structural improvements

= 0.9.11.3 (April 7, 2015): Bugfix Release (v1.0 RC3) =
* Added parameter 'id' to the shortcode [laterpay_time_passes] to display only one specific time pass
* Fixed display of voucher code statistics in pricing tab
* Visual fixes for LaterPay purchase button
* Fixed attachment download via the shortcode [laterpay_premium_download] in caching mode
* Fixed redeeming voucher codes via the shortcode [laterpay_redeem_voucher]
* Fixed undefined index in time_pass partial
* Fixed a few visual bugs in post price form
* More ongoing refactoring of markup and SCSS files

= 0.9.11.2 (March 5, 2015): Bugfix Release (v1.0 RC2) =
* Fixed undefined variable on dashboard
* Removed sourcemaps from production assets

= 0.9.11.1 (March 5, 2015): Bugfix Release (v1.0 RC1) =
* Added capability to also allow users with role 'editor' to see the dashboards in the plugin backend
* Fixed bug that caused link checker plugins to report broken links
* Fixed bug that prevented time passes widget to render, if a specific time pass id was not provided
* Visual fixes for redeem voucher code form in some themes
* Fixed bug that caused custom columns in posts page to not be rendered
* Improved dashboard behavior: running Ajax requests are aborted now, when changing the dashboard configuration
* Improved performance: do not check LaterPay token on free posts
* Removed default values for VAT, which were made obsolete by VATMOSS
* Removed filters from plugin config, because of recent introduction of advanced settings page
* Removed commented out function to switch the default currency
* Lots of internal refactoring and clean-up

= 0.9.11 (February 25, 2015): Time Pass Additions Release =
* Added option to allow only time pass purchases or time pass and individual post purchases
* Added dashboard page for time pass customer lifecycle that shows how many time passes are sold and active, and when
  the currently active time passes will expire
* Added shortcode for rendering time passes
* Added option to have the plugin visible or invisible for visitors in test mode
* Added advanced setting for defining unrestricted access for a user role on a per category basis
* Added proper handling of subcategories for time pass access checks
* Added proper handling of subcategories for category prices
* Added separation of analytics data between data collected in test mode and in live mode
* Fixed bug where category-specific time pass would give access to entire site
* Fixed bug where number of page views was not rendered correctly in post statistics pane
* Fixed a lot of usability and rendering bugs of the dynamic pricing widget
* Fixed bug where custom position of purchase was not respected in admin preview
* Fixed bug where custom position of time passes was not respected in admin preview
* Fixed bug with day names in dashboard
* Added missing documentation and fixed inconsistencies in coding style
* The post statistics pane is now rendered again in debug mode after WordPress update 4.1.1 was released

= 0.9.10 (January 21, 2015): Gift Cards Release =
* Added gift cards for time passes to allow giving away time passes as a present
* Added two shortcodes: [laterpay_gift_card] to render gift cards and [laterpay_redeem_voucher] to render a form for
  redeeming gift card codes.
* Changed time pass behavior to render below the content by default
* Added shortcode [laterpay_time_passes] as alternative for the action 'laterpay_time_passes'.
* Added shortcode [laterpay_account_links] and action 'laterpay_account_links' to render stylable links to log in to /
  out of LaterPay
* Implemented filters for dashboard
* Fixed various bugs related to the dashboard
* Changed config mechanism to use a WordPress settings page for advanced settings
* Added support for caching plugin WP Rocket
* Restored option to give unlimited access to a specific user group
* Fixed bug that shortcode [laterpay_premium_download] always uses global default price
* Fixed bug where teaser would not save with price type "global default" and "category default"
* Fixed bug where its price could not be updated after a post was published
* Fixed bug where post statistics pane was not visible
* Fixed bug where Youtube videos in paid content are not loaded
* Fixed bug where '?' was appended to the URL
* Fixed bug where the category default price was not automatically applied, if the category affiliation of a post changed
* Various bug fixes on dynamic pricing widget
* Various smaller bug fixes
KNOWN BUGS:
* The post statistics pane is not rendered in debug mode because of a WordPress bug that will be resolved with WP 4.1.1

= 0.9.9 (December 2, 2014): Time Passes Release =
* Added time passes and vouchers for selling access to the entire site or parts of it for a limited amount of time
* Added sales dashboard (pre-release) for monitoring sales performance
* Added quality rating functionality to let users who bought an article rate it on a five-star scale
* Purchases from shortcode now directly trigger a download, if it is an attachment
* Improved functionality of dynamic pricing widget (added option to enter exact price values, added option to restart
  dynamic pricing, automatically adjust scaling of y-axis, depending on revenue model, etc.)
* Fixed bug that broke the installation ("Unrecognized Address in line 78")
* Fixed loading of youtube videos in paid content
* Around 8784126852 other small bugfixes and improvements
KNOWN BUGS:
* Shortcode always uses global default price https://github.com/laterpay/laterpay-wordpress-plugin/issues/503

= 0.9.8.3 (October 28, 2014): Bugfix Release =
* Added bulk price editor to make editing large numbers of posts easier
* Fixed saving of global default and category default prices with German number format
* Fixed bug where user was not immediately forwarded to purchases content but had to click purchase button a second time
* Fixed IPv6 bug in logger / debugger functionality
* Fixed plugin mode toggle
* Fixed loading of youtube videos in paid posts
* Fixed displaying of custom teaser images in laterpay_premium_download shortcode
* Ensured shortcode plain text is hidden to visitors in test mode
* Improved server-side validation of forms

= 0.9.8.2 (October 9, 2014): Integration Support Release =
* Added debugger pane to help with integration of plugin (pane is displayed in debug mode: define('WP_DEBUG', true);)
* Documented UI options and shortcode usage in appearance tab
* Made post statistics logging compatible with page caching
* Ensured that LaterPay can be enabled on attachment pages
* Extended file protection in paid posts to all files on current host
* Disabled option to select currency as currently only Euro is supported

= 0.9.8.1 (September 30, 2014): Bugfix Release =
* Made sure the LaterPay client is included in the release

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
