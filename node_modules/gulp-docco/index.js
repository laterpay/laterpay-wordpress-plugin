(function(){
  'use strict';

  var through2 = require('through2'),
      path = require('path'),
      gutil = require('gulp-util');

  var PluginError = gutil.PluginError;

  var helpers = require('./lib/helpers.js');
  var Backend = require('./lib/backend.js');

  var _ = require('underscore');
  var vfs = require('vinyl-fs');

  /**
   * That's the plugin parser
   */
  var streamParser = function (config) {
    config = config || {};
    config = helpers.configure(config);

    var wp = new Backend(config);

    var templateReady;

    var parse = function(file, enc, next){
      if (file.isNull()) return; // ignore
      if (file.isStream()) return this.emit('error', new PluginError('gulp-docco',  'Streaming not supported'));

      // Ignore unsupported files altogether
      var lang = helpers.isSupported(file.path, config);
      if (!lang) {
        next();
        return;
      }

      // Got template? process file and go on
      if(templateReady){
        wp.process(file);
        this.push(file);
        next();
        return;
      }

      // Otherwise, grab the template and go with it
      templateReady = [config.template];
      if(config.css)
        templateReady.push(config.css);
      if(config['public'])
        templateReady.push(config['public'] + '/**/*');

      // Hold while we receive the template files
      vfs.src(templateReady)
        .pipe(through2.obj(function(f, e, n){
          // Template hile itself? Process it
          if(typeof config.template == 'string' && f.path == path.resolve(config.template)){
            config.template = _.template(f.contents.toString('utf8'));
            n();
            return;
          }
          if(/\/public\//.test(f.path))
            f.base = path.dirname(f.base);
          // Insert into the stream if not template
          this.push(f);
          n();
        }.bind(this), function(end){
          end();
          // So, we have the template complete - do the file itself now
          wp.process(file);
          this.push(file);
          next();
        }.bind(this)));

    };

    return through2.obj(parse);
  };

  // // Wrap reporter helper
  // var wrapReporter = function(reporter){
  //   return function(options){
  //     return through2.obj(function(file, enc, next){
  //       var warnings = JSON.parse(file.contents.toString('utf8')).warnings;
  //       if(warnings && warnings.length){
  //         // Don't trust the (yahoo) reporter too much
  //         try{
  //           reporter(warnings, options);
  //         }catch(e){
  //           return this.emit('error', new PluginError('gulp-docco', 'Reporter crashed!' + e));
  //         }
  //       }
  //       this.push(file);
  //       next();
  //     });
  //   };
  // };

  // var docco = function(destination, template, infos, buildOptions){
  //   return gutil.combine(
  //     docco.parser(infos),
  //     // docco.reporter(),
  //     // docco.generator(destination, template, buildOptions)
  //   )();
  // };

  // // Yui default, provided reporter
  // docco.yuiReporter = wrapReporter(require('./lib/uglyreporter'));

  // // Our own reporter
  // docco.chalkReporter = wrapReporter(require('./lib/chalkreporter'));

  // // Default to chalk, nicier :)
  // docco.reporter = docco.chalkReporter;

  // docco.generator = streamGenerator;

  // docco.parser = streamParser;

  module.exports = streamParser;

}());

