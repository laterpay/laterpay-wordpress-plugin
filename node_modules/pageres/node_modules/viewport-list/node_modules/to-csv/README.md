to-csv
=====

Convert objects to CSV format

## install

```sh
$ component install jwerle/to-csv
```

## api

### csv(array, opts)

Accepts an array of objects and converts them to CSV format

## example

```js
var data = [];

for (var i = 0; i < 10; ++i) {
	data.push({ 
		id: Math.random().toString(16).slice(2), 
		value: data.length % 2 
	});
}

console.log(csv(data));
/**
 	id,value
	22dd67dd,0
	8d18327,1
	adfcb2bb,0
	5424635b,1
	f8049525,0
	49493c6d,1
	af99d0f1,0
	d9f82d24,1
	e7f38d52,0
	47ef5c67,1
**/
```

## License
MIT
