gulp-docco (a.k.a "Gulco citron")
=============

[![NPM version][npm-image]][npm-url] [![Build Status][travis-image]][travis-url]  [![Coverage Status][coveralls-image]][coveralls-url] [![Dependency Status][depstat-image]][depstat-url] [![Code Climate][codeclimate-image]][codeclimate-url]

> docco plugin for [gulp](https://github.com/wearefractal/gulp)

WARNING
-------------

This is an early release - if you find bugs, please say so.

TL;DR
-------------

Install `gulp-docco` as a development dependency:

```shell
npm install --save-dev gulp-docco
```

Then, add it to your `gulpfile.js`:

```javascript
var docco = require("gulp-docco");

gulp.src("./src/*.js")
  .pipe(docco())
  .pipe(gulp.dest('./documentation-output'))
```

That's it.

## API

### options

Additionally, we support passing an options object following the [docco syntax](http://jashkenas.github.io/docco/):

```javascript
var docco = require("gulp-docco");

gulp.src("./src/*.js")
  .pipe(docco(options))
  .pipe(gulp.dest('./documentation-output'))
```

Mainly of interest are the various embedded layouts (parallel, linear, classic), and custom template support.

## Caveats?

We bypass some of docco internals in order to prevent it from manipulating files on its own - if something is broken, say so!

## License

[MIT License](http://en.wikipedia.org/wiki/MIT_License)

[npm-url]: https://npmjs.org/package/gulp-docco
[npm-image]: https://badge.fury.io/js/gulp-docco.png

[travis-url]: http://travis-ci.org/jsBoot/gulp-docco
[travis-image]: https://secure.travis-ci.org/jsBoot/gulp-docco.png?branch=master

[coveralls-url]: https://coveralls.io/r/jsBoot/gulp-docco
[coveralls-image]: https://coveralls.io/repos/jsBoot/gulp-docco/badge.png?branch=master

[depstat-url]: https://david-dm.org/jsBoot/gulp-docco
[depstat-image]: https://david-dm.org/jsBoot/gulp-docco.png

[codeclimate-url]: https://codeclimate.com/github/jsBoot/gulp-docco.js
[codeclimate-image]: https://codeclimate.com/github/jsBoot/gulp-docco.png
