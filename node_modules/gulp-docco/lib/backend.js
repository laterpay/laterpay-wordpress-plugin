/**
 * Docco backend
 * =======
 *
 * This is a wrapper around docco parsing functions, that uses only non-fs safe methods, and does
 * template process manually so to avoid docco writing.
 */

(function(){
  'use strict';

  // Dependencies
  var path = require('path');
  var docco = require('docco');
  var parse = docco.parse;
  var format = docco.format;

  var marked = require('marked');
  marked.setOptions({
    smartypants: true
  });

  // Just require extension and languages as far as config is concerned
  var Backend = function(config){

    // Do parse
    this.process = function(vinyl){
      var data = vinyl.contents.toString('utf8');

      // Parse
      var sections = parse(vinyl.path, data, config);

      // Format
      format(vinyl.path, sections, config);

      // Compute new title
      var first = marked.lexer(sections[0].docsText)[0];
      var hasTitle = first && first.type === 'heading' && first.depth === 1;
      var title = hasTitle ? first.text : path.basename(vinyl.path);

      // Change extension
      vinyl.path = vinyl.path.replace(/([.][^.]+)?$/, '.html');

      // Generate html from template
      var relativeIt = path.relative(path.dirname(vinyl.relative), '.')
      var relativeCss = path.join(relativeIt, path.basename(config.css));

      var html = config.template({
        sources: []/*config.sources*/,
        css: relativeCss,
        title: title,
        hasTitle: hasTitle,
        sections: sections,
        path: path/*,
        destination: vinyl.relative//.path*/
      });
      // Replace normalize bogus links: public/stylesheets/normalize.css
      html = html.replace(/(public\/stylesheets\/normalize\.css)/, path.join(relativeIt, "$1"));

      // Spoof it back in
      vinyl.contents = new Buffer(html);
    };
  };

  module.exports = Backend;

}());
