laterpay-wordpress-plugin
=========================

This is the official Laterpay plugin for selling digital content with WordPress.

Feel free to fork the plugin and adapt it to your needs.

Please get involved in this project and contribute back changes other users would also benefit from.


## Installation

The plugin is available on http://wordpress.org/plugins/laterpay


## Contributing

1. Fork it ( https://github.com/laterpay/laterpay-wordpress-plugin/fork )
2. Run `composer install` to install all the dependencies.
3. Create your feature branch (`git checkout -b feature/my_new_feature develop`)
4. Add your changes, verify coding standards and language compatibility ( [Check #development-notes](#development-notes) )
5. Run `gulp build` for `js` and / or `css` changes. Please check [gulpfile](gulpfile.js) for more tasks.
6. Commit your changes (`git commit -am 'Added some feature'`)
7. Push to the branch (`git push origin feature/my_new_feature`)
8. Create a new Pull Request to develop.

Note: Source code of the plugin resides in [laterpay](./laterpay) directory, you should copy / rsync files from this directory to your test site, in order to see the changes done.

⚠️ Warning: Code from this repo is only to be used for development purposes. Always use the latest version available at http://wordpress.org/plugins/laterpay for production sites.

## Development Notes

##### Please run following commands from the root directory.

1. Please verify your code is in compliance to the Coding Standards used in this Project.
2. Run `composer phpcs filename` or `composer phpcs laterpay` to check for PHPCS errors/warnings.
3. Run `composer phpcompat` to check if the code is compatible for PHP 5.6 and above

This project uses Gulp to build its assets.
Gulp is a node.js module. If you have node.js running, you can install gulp with ```sudo npm install -g gulp```.
Then go to the repository root folder and install the required gulp plugins with ```npm install```.
Now you can run any of the tasks defined in the gulpfile from the repository root folder.
During development you can either watch the repo for changes and automatically recompile the modified assets using ```gulp```.
For exporting the assets for a release, you can also run ```gulp build```.

An [EditorConfig](http://editorconfig.org) file is supplied to make it easier to adjust your IDE to the project standards in applying whitespace.

Contributed PHP code must comply with the WordPress coding standards.
We recommend testing it with PHP_CodeSniffer + [standard 'WordPress'](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards).

All pull requests are automatically linted with JSHint and the [.jshintrc](https://github.com/laterpay/laterpay-wordpress-plugin/blob/master/.jshintrc) included in this repository.


## Versioning

The Laterpay WordPress plugin uses [Semantic Versioning 2.0.0](http://semver.org)


## Copyright

Copyright 2020 Laterpay GmbH – Released under MIT License
