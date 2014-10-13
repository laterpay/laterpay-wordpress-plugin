(function() {
  'use strict';

  var PLUGIN_NAME = 'gulp-closure-compiler-service';

  var gutil   = require('gulp-util');
  var through = require('through2');
  var closure = require('closure-compiler-service');

  module.exports = function(options) {
    'use strict';

    var closure_service = function(file, enc, callback) {
      var that = this;

      // NOP
      if (file.isNull()) {
        that.push(file);
        return callback();
      }

      if (file.isStream()) {
        that.emit('error',
          new gutil.PluginError(PLUGIN_NAME, 'Streaming not supported'));

        return callback();
      }

      if (file.isBuffer()) {
        closure.compile(file.contents, options, function(errs, code) {
          if (errs && errs.length) {
            that.emit('error',
              new gutil.PluginError(PLUGIN_NAME, errs[0].error));

            return callback();
          }

          file.contents = new Buffer(code);
          that.push(file);
          return callback();
        });
      }

      else { return callback(); }
    }

    return through.obj(closure_service);
  };

})();
