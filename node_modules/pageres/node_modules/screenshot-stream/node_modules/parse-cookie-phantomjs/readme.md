# parse-cookie-phantomjs [![Build Status](https://travis-ci.org/sindresorhus/parse-cookie-phantomjs.svg?branch=master)](https://travis-ci.org/sindresorhus/parse-cookie-phantomjs)

> Parse a cookie for use in PhantomJS

Accepts a cookie string and returns an object ready to be passed into [`phantom.addCookie()`](http://phantomjs.org/api/phantom/method/add-cookie.html).


## Install

```sh
$ npm install --save parse-cookie-phantomjs
```


## Usage

```js
var parseCookiePhantomjs = require('parse-cookie-phantomjs');

parseCookiePhantomjs('foo=bar; Path=/; Domain=localhost');
/*
{
	name: 'foo',
	value: 'bar',
	domain: 'localhost',
	path: '/',
	httponly: false,
	secure: false,
	expires: 'Infinity'
}
*/
```


## License

MIT © [Sindre Sorhus](http://sindresorhus.com)
