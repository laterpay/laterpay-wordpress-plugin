'use strict';
module.exports = function (node, str) {
	var newToken = {
		type: 'custom',
		value: str
	};

	if (node.startToken.prev) {
		node.startToken.prev.next = newToken;
		newToken.prev = node.startToken.prev;
	}

	if (node.endToken.next) {
		node.endToken.next.prev = newToken;
		newToken.next = node.endToken.next;
	}

	node.startToken = node.endToken = newToken;
};
