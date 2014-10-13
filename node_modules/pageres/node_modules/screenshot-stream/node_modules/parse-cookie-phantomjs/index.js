'use strict';
var toughCookie = require('tough-cookie');

module.exports = function (str) {
	if (typeof str !== 'string') {
		throw new TypeError('Expected a string');
	}

	var cookie = toughCookie.parse(str);

	return {
		// the touch-cookie output is slightly
		// different from what PhantomJS accepts
		name: cookie.key,
		value: cookie.value,
		domain: cookie.domain,
		path: cookie.path,
		httponly: cookie.httpOnly,
		secure: cookie.secure,
		expires: cookie.expires
	};
};
