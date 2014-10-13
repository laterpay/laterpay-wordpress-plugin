#!/usr/bin/env node

/**
 * closure-compiler-service - compile scripts with the Closure compiler service
 * https://github.com/gavinhungry/closure-compiler-service
 */

(function() {
  'use strict';

  var ccs = {};

  var request = require('request');
  var querystring = require('querystring');

  // https://developers.google.com/closure/compiler/docs/api-ref
  var API = 'https://closure-compiler.appspot.com/compile';

  /**
   * Get API URI from options
   *
   * @param {Object} [opts] - API options
   * @return {String} full API URI, with API options as URL-encoded strings
   */
  ccs.uri = function(opts) {
    opts = opts || {};

    opts.output_info = opts.output_info || ['compiled_code', 'errors'];
    opts.compilation_level = opts.compilation_level || 'SIMPLE_OPTIMIZATIONS';
    opts.output_format = opts.output_format || 'json';

    var encoded = querystring.encode(opts);

    return encoded ? (API + '?' + encoded) : API;
  };

  /**
   * Compile a string of JavaScript with the Closure compiler service
   *
   * Pass null as first argument if specifying code_url or js_code API options
   *
   * @param {String|Buffer} js_code - JavaScript code to compile
   * @param {Object} [options] - API options
   * @param {Function} [callback](errs, code) - defaults to console output
   */
  ccs.compile = function(js_code, options, callback) {
    js_code = js_code || '';
    var opts = options || {};
    if (typeof options === 'function') {
      opts = {};
      callback = options;
    }

    if (typeof callback !== 'function') {
      callback = function(errs, code) {
        if (errs) { return console.error(errs); }
        console.log(code);
      }
    }

    // allow buffers to be passed directly
    if (typeof js_code.toString === 'function') {
      js_code = js_code.toString();
    }

    var r = request.post({ uri: ccs.uri(opts) }, function(err, res, body) {
      var result, ex = null;

      try {
        result = JSON.parse(body);
      } catch(ex) {
        ex = ex;
      }

      var errs = (result && result.errors) ? result.errors : ex;
      var code = (result && result.compiledCode) ? result.compiledCode : '';
      callback(errs, code);
    });

    r.form({ js_code: js_code });
  };

  module.exports = ccs;
})();
