# rocambole-node-update [![Build Status](https://travis-ci.org/sindresorhus/rocambole-node-update.png?branch=master)](https://travis-ci.org/sindresorhus/rocambole-node-update)

> Update a [rocambole](https://github.com/millermedeiros/rocambole) AST node


## Install

```
npm install --save rocambole-node-update
```


## Example

```js
var rocambole = require('rocambole');
var updateNode = require('rocambole-node-update');

rocambole.moonwalk('if (true) { foo() }', function (node) {
	if (node.type === 'CallExpression') {
		updateNode(node, 'bar()');
	}
}).toString();
//=> if (true) { bar() }
```


## License

MIT © [Sindre Sorhus](http://sindresorhus.com)
