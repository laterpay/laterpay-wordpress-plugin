/**
 * Helpers
 * =======
 *
 * In order to prevent docco from using fs, a number of its internal functions have to be
 * duplicated here.
 */

(function(){
  'use strict';

  var _ = require('underscore');
  var path = require('path');
  var doccoModuleRoot = path.dirname(require.resolve('docco'));

  var __slice = [].slice;

  var defaults = {
    // Template engine
    layout: 'parallel',
    template: null,
    css: null,
    // Parser only
    extension: null,
    languages: {
      ".coffee":      {"name": "coffeescript", "symbol": "#"},
      ".litcoffee":   {"name": "coffeescript", "symbol": "#", "literate": true},
      "Cakefile":     {"name": "coffeescript", "symbol": "#"},
      ".rb":          {"name": "ruby", "symbol": "#"},
      ".py":          {"name": "python", "symbol": "#"},
      ".tex":         {"name": "tex", "symbol": "%"},
      ".latex":       {"name": "tex", "symbol": "%"},
      ".dtx":         {"name": "tex", "symbol": "%"},
      ".sty":         {"name": "tex", "symbol": "%"},
      ".cls":         {"name": "tex", "symbol": "%"},
      ".js":          {"name": "javascript", "symbol": "//"},
      ".java":        {"name": "java", "symbol": "//"},
      ".groovy":      {"name": "groovy", "symbol": "//"},
      ".scss":        {"name": "scss", "symbol": "//"},
      ".cpp":         {"name": "cpp", "symbol": "//"},
      ".php":         {"name": "php", "symbol": "//"},
      ".hs":          {"name": "haskell", "symbol": "--"},
      ".erl":         {"name": "erlang", "symbol": "%"},
      ".hrl":         {"name": "erlang", "symbol": "%"},
      ".md":          {"name": "markdown", "symbol": ""},
      ".markdown":    {"name": "markdown", "symbol": ""},
      ".less":        {"name": "less", "symbol": "//"},
      ".h":           {"name": "objectivec", "symbol": "//"},
      ".m":           {"name": "objectivec", "symbol": "//"},
      ".n":           {"name": "nemerle", "symbol": "//"},
      ".mm":          {"name": "objectivec", "symbol": "//"},
      ".scala":       {"name": "scala", "symbol": "//"},
      ".cs":          {"name": "cs", "symbol": "//"},
      ".as":          {"name": "actionscript", "symbol": "//"},
      ".scpt":        {"name": "applescript", "symbol": "--"},
      ".applescript": {"name": "applescript", "symbol": "--"},
      ".sh":          {"name": "bash", "symbol": "#"},
      ".clj":         {"name": "clojure", "symbol": ";"},
      ".cmake":       {"name": "cmake", "symbol": "#"},
      ".d":           {"name": "d", "symbol": "//"},
      ".p":           {"name": "delphi", "symbol": "//"},
      ".pp":          {"name": "delphi", "symbol": "//"},
      ".pas":         {"name": "delphi", "symbol": "//"},
      ".bat":         {"name": "dos", "symbol": "@?rem"},
      ".btm":         {"name": "dos", "symbol": "@?rem"},
      ".cmd":         {"name": "dos", "symbol": "@?rem"},
      ".go":          {"name": "go", "symbol": "//"},
      ".ini":         {"name": "ini", "symbol": ";"},
      ".lisp":        {"name": "lisp", "symbol": ";"},
      ".lua":         {"name": "lua", "symbol": "--"},
      ".mel":         {"name": "mel", "symbol": "//"},
      ".pl":          {"name": "perl", "symbol": "#"},
      ".pm":          {"name": "perl", "symbol": "#"},
      ".pod":         {"name": "perl", "symbol": "#"},
      ".t":           {"name": "perl", "symbol": "#"},
      ".r":           {"name": "r", "symbol": "#"},
      ".rc":          {"name": "rust", "symbol": "//"},
      ".rs":          {"name": "rust", "symbol": "//"},
      ".sql":         {"name": "sql", "symbol": "--"},
      ".vala":        {"name": "vala", "symbol": "//"},
      ".vapi":        {"name": "vala", "symbol": "//"},
      ".vbe":         {"name": "vbscript", "symbol": "'"},
      ".vbs":         {"name": "vbscript", "symbol": "'"},
      ".wsc":         {"name": "vbscript", "symbol": "'"},
      ".wsf":         {"name": "vbscript", "symbol": "'"},
      ".vhdl":        {"name": "vhdl", "symbol": "--"}
    }
  };

  var buildMatchers = function(languages) {
    var ext, l;
    for (ext in languages) {
      l = languages[ext];
      l.commentMatcher = RegExp("^\\s*" + l.symbol + "\\s?");
      l.commentFilter = /(^#![/]|^\s*#\{)/;
    }
    return languages;
  };

  var configure = function(options){
    var config, dir;
    config = _.extend({}, defaults, _.pick.apply(_, [options].concat(__slice.call(_.keys(defaults)))));

    config.languages = buildMatchers(config.languages);

    if (options.template) {
      config.layout = null;
    } else {
      dir = config.layout = path.join(doccoModuleRoot, 'resources', config.layout);
      config["public"] = path.join(dir, 'public');
      config.template = path.join(dir, 'docco.jst');
      config.css = options.css || path.join(dir, 'docco.css');
    }
    return config;
  };

  var getLanguage = function(source, config) {
    var codeExt, codeLang, ext, lang;
    ext = config.extension || path.extname(source) || path.basename(source);
    lang = config.languages[ext] || defaults.languages[ext];
    if (lang && lang.name === 'markdown') {
      codeExt = path.extname(path.basename(source, ext));
      if (codeExt && (codeLang = defaults.languages[codeExt])) {
        lang = _.extend({}, codeLang, {
          literate: true
        });
      }
    }
    return lang;
  };

  module.exports = {
    isSupported: getLanguage,
    configure: configure
  };
}());
