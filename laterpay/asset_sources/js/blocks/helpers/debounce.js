// Throttle the execution of a function by a given delay.

const debounce = function( fn, delay ) {
	let timer;
	return function() {
		const context = this,
			args = arguments;

		clearTimeout( timer );

		timer = setTimeout( function() {
			fn.apply( context, args );
		}, delay );
	};
};

export default debounce;
