laterpay-wordpress-plugin
=========================

This is the official LaterPay plugin for selling digital content with WordPress.

Feel free to fork the plugin and adapt it to your needs.

Please get involved in this project and contribute back changes other users would also benefit from.


## Installation

Grab the latest version from https://github.com/laterpay/laterpay-wordpress-plugin/releases/latest and upload it
to the plugins folder of your WordPress installation.
The plugin will soon also be available from http://wordpress.org/plugins/


## Contributing

1. Fork it ( https://github.com/laterpay/laterpay-wordpress-plugin/fork )
2. Create your feature branch (`git checkout -b feature/my_new_feature`)
3. Commit your changes (`git commit -am 'Added some feature'`)
4. Push to the branch (`git push origin feature/my_new_feature`)
5. Create a new Pull Request

Contributed PHP code must comply with the WordPress coding standards. We recommend testing it with PHP_CodeSniffer + [standard 'WordPress'](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards). 

Contributed JS code must be linted with JSHint.


## Updating Translations

* Go to a server the plugin is installed on
* Make sure SVN is available (`apt-get install subversion`)
* Make sure the WordPress translation tools are available (`svn checkout http://i18n.svn.wordpress.org/tools/trunk/`)
* Extract POT file with translations (php makepot.php wp-plugin /path/to/my-plugin): e.g. `php makepot.php wp-plugin /var/www/wp-content/plugins/laterpay`
* Download laterpay.pot to the languages folder of your local copy of the plugin
* Open outdated PO file with Poedit (http://poedit.net)
* Choose Catalog > Update from POT file… and select the new POT file
* Update translations
* Save to generate the new PO/MO files


## Versioning

The LaterPay WordPress plugin uses [Semantic Versioning 2.0.0](http://semver.org)


## Copyright

Copyright 2014 LaterPay GmbH – Released under MIT License
