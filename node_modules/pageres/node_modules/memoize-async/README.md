## memoize-async [![Build Status](https://travis-ci.org/azer/memoize-async.png)](https://travis-ci.org/azer/memoize-async)

Async function memoizer.

### Install

```bash
$ npm install memoize-async
```

### Usage

```js
memoize  = require('memoize-async')
readFile = require('fs').readFile
memoized = memoize(readFile)

memoized('docs/readme', console.log)
// doing some work
// => read me first!

memoized('docs/readme', console.log)
// => read me first!
```

### Storage

It stores the values returned in an object by default. You can pass read & write methods to choose your own:

```js
memoized = memoize(fn, { read: read, write: write, hash: ifexists })

function read (key, callback) {
}

function write (key, value, callback) {
}
```
