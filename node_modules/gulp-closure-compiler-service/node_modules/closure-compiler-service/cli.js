#!/usr/bin/env node

(function() {
  'use strict';

  var ccs = require('closure-compiler-service');
  var fs  = require('fs');
  var pkg = require('./package.json');

  var exit = function(msg) {
    console.log(msg);
    process.exit(0);
  }

  var die = function(msg) {
    console.error(msg);
    process.exit(1);
  };

  // write output to console if called from command line
  if (process.argv.length === 3) {
    var arg = process.argv[2];

    if (arg === '-v' || arg === '--version') {
      exit(pkg.name + ' ' + pkg.version);
    }

    if (arg === '-h' || arg === '--help') {
      exit('See ' + pkg.homepage);
    }

    var js_code = fs.readFile(arg, function(err, buf) {
      if (err) { die(err); }

      ccs.compile(buf, function(errs, code) {
        if (errs) { die(errs); }
        console.log(code);
      });
    });
  }
})();
