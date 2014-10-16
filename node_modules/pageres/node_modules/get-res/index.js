'use strict';

var w3counter = require('w3counter');

/**
 * Get ten most popular screen resolutions
 *
 * @param {Function} cb
 * @api public
 */

module.exports = function (cb) {
    w3counter('res', function (err, data) {
        if (err) {
            return cb(err);
        }

        return cb(null, data);
    });
};
