var _ = require("lodash");

/**
 * @constructor
 */
var EasyExtender = function (plugins, hooks) {

    this.plugins        = {};
    this.pluginOptions  = {};

    this.hooks          = hooks;
    this.defaultPlugins = plugins;

    return this;
};

/**
 * @returns {EasyExtender}
 */
EasyExtender.prototype.init = function () {

    var required = Object.keys(this.defaultPlugins);
    required.forEach(function (name) {
        if (_.isUndefined(this.plugins[name])) {
            this.plugins[name] = this.defaultPlugins[name];
        }
    }, this);

    return this;
};

/**
 * Call the '.plugin()' method of all registered plugins
 */
EasyExtender.prototype.initUserPlugins = function () {

    var args = Array.prototype.slice.call(arguments);

    var userPlugins = _.difference(Object.keys(this.plugins), Object.keys(this.defaultPlugins));

    if (userPlugins.length) {

        userPlugins.forEach(function (plugin) {

            var pluginOptions = {};

            if (this.pluginOptions) {
                pluginOptions = this.pluginOptions[plugin];
            }

            this.get(plugin).apply(null, [pluginOptions].concat(args));

        }, this);
    }
};

/**
 * @param {String} name
 * @returns {Function|Boolean}
 */
EasyExtender.prototype.get = function (name) {

    if (!_.isUndefined(this.plugins[name])) {
        return this.plugins[name].plugin || false;
    }

    return false;
};

/**
 * @param {Object} module
 * @param {Object} [opts]
 * @param {Function} [cb]
 */
EasyExtender.prototype.registerPlugin = function (module, opts, cb) {

    var pluginOptions;

    if (!_.isFunction(module.plugin)) {
        return _.isFunction(cb) ? cb("Module must implement a .plugin() method") : false;
    }

    if (!cb && opts) {
        if (_.isFunction(opts)) {
            cb = opts;
        } else {
            pluginOptions = opts;
        }
    }

    var name = _.isUndefined(module["plugin:name"]) ? _.uniqueId() : module["plugin:name"];

    this.pluginOptions[name] = pluginOptions;

    this.plugins[name] = module;

    if (_.isFunction(cb)) {
        cb(null);
    }

    return this;
};

/**
 * @param name
 * @returns {*}
 */
EasyExtender.prototype.hook = function (name) {

    // Get any extra args
    var args  = Array.prototype.slice.call(arguments, 1);

    // build a list of hook funcs
    var funcs = [];

    _.each(this.plugins, function (plugin) {
        if (plugin.hooks) {
            if (!_.isUndefined(plugin.hooks[name])) {
                funcs.push(plugin.hooks[name]);
            }
        }
    });

    return this.hooks[name].apply(this, [funcs].concat(args));
};

module.exports = EasyExtender;