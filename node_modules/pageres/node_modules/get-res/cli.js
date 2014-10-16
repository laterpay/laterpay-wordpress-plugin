#!/usr/bin/env node
'use strict';

var getRes = require('./');
var pkg = require('./package.json');

/**
 * Help screen
 */

function help() {
    console.log(pkg.description);
    console.log('');
    console.log('Usage');
    console.log('  $ get-res');
}

/**
 * Show help
 */

if (process.argv.indexOf('-h') !== -1 || process.argv.indexOf('--help') !== -1) {
    help();
    return;
}

/**
 * Show package version
 */

if (process.argv.indexOf('-v') !== -1 || process.argv.indexOf('--version') !== -1) {
    console.log(pkg.version);
    return;
}

/**
 * Run
 */

getRes(function (err, resolutions) {
    if (err) {
        throw err;
    }

    resolutions.forEach(function (res, i) {
        i = i + 1;
        console.log(i + '. ' + res.item);
    });
});
