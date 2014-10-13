# easydate

### Overview

Returns the date according to a pattern.

### Installation

Install using npm:

```
$ npm install easydate
```

### API

```javascript
var easydate = require('easydate');

easydate('d-M-y'); // "28-01-14"
easydate('d/M/Y'); // "28/01/2014"
easydate('Y.M.d'); // "2014.01.28"
easydate('M'); // "01"
easydate('d-M-Y @ h:m:s.l'); // "29-01-2014 @ 07:22:37.418"
```

### Pattern Options

_Case sensitive._

* ```Y``` Full year (nnnn)
* ```y``` Year (nn)
* ```M``` Month (nn)
* ```d``` Day (nn)
* ```h``` Hour (nn)
* ```m``` Minute (nn)
* ```s``` Second (nn)
* ```l``` Millisecond (nnn)

### Caveats

Any instances of the above characters will be replaced with the relevant numbers. It is recommended to not use words within the pattern string.