
/**
 * Module dependencies
 */

var csv = require('./')
 , i = 10


function bench (ops) {
	var data = []

	for (var i = 0; i < ops; ++i) {
		data.push({ id: Math.random().toString(16).slice(2), value: data.length % 2 });
	}

	console.time(ops + ' ops in');
	csv(data);
	console.timeEnd(ops + ' ops in');
}


while (i <= 10e3) {
	bench(i*=10);
}
