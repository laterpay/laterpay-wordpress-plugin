closure-compiler-service
========================
Compile JavaScript with the Google
[Closure compiler service](https://developers.google.com/closure/compiler/docs/api-ref).
No Java dependency.


Installation
------------

    # npm -g install closure-compiler-service


Usage
-----

### Command Line

Output is written to stdout, and can thus be redirected to a file:

    $ closure-service input.js > input.min.js


### Module

    var ccs = require('closure-compiler-service');
    var input = fs.readFileSync('input.js');

    /**
     * Compile a string of JavaScript with the Closure compiler service
     *
     * @param {String|Buffer} js_code - JavaScript code to compile
     * @param {Object} [options] - API options
     * @param {Function} [callback](errs, code) - defaults to console output
     */
    ccs.compile(input, function(errs, code) {
      // `code` is the compiled result
    });


`ccs.compile` optionally takes an options object as the second argument (before
the callback function), which are passed to the API:

    ccs.compile(js_code, {
      compilation_level: 'WHITESPACE_ONLY'
    }, function(errs, code) {
      // ...
    });

Pass an array to stipulate multiple values for the same option:

    ccs.compile(js_code, {
      code_url: [url1, url2 /*, ... */]
    }, function(errs, code) {
      // ...
    });

`js_code` may be `null` if using `code_url` to provide code.


### Default Options

    output_format     = 'json' // you won't want to change this
    output_info       = ['compiled_code', 'errors']
    compilation_level = 'SIMPLE_OPTIMIZATIONS'


License
-------
Released under the terms of the
[MIT license](http://tldrlegal.com/license/mit-license). See **LICENSE**.
