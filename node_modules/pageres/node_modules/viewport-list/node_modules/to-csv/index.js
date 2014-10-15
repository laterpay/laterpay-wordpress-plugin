
/**
 * Module dependencies
 */

var isArray = Array.isArray
  , keys = Object.keys;


CSV.CHAR_RETURN = 0xd;
CSV.CHAR_NEWLINE = 0xa;
CSV.DELIMITER = 0x2c;
CSV.CHAR_ENCAPSULATE = 0x22;


function head (a) {
  return a[0];
}

function tail (a) {
  return a[a.length -1];
}

function char (c) {
  return 'number' === typeof c
           ? String.fromCharCode.apply(null, arguments)
           : c;
}

function needsEncapsulation (string) {
  return string.toString().indexOf(char(CSV.DELIMITER)) >= 0;
}

function encapsulate (string, wrapperChar) {
  var replaceWith = '\\' + wrapperChar;
  var escapedValue = string.toString()
                           .replace(new RegExp(wrapperChar, "g"), replaceWith);

  return wrapperChar + escapedValue + wrapperChar;
}

/**
 * Parses an array of objects to a CSV output
 */

module.exports = CSV;
function CSV (objects, opts) {
  if ('object' !== typeof objects) throw new TypeError("expecting an array");

  opts = 'object' === typeof opts
          ? opts
          : {};

  objects = isArray(objects)
             ? objects
             : [objects];

  if (!objects.length) throw new Error("expecting at least one object");

  var headers = keys(head(objects))
    , buf = [];

  while (objects.length) {
    var lbuf = []
      , object = objects.shift();

    for (var i = 0 ;i < headers.length; ++i) {
      var header = headers[i];

      if (lbuf.length) lbuf.push(char(CSV.DELIMITER));
      object[header] = needsEncapsulation(object[header])
                        ? encapsulate(object[header], char(CSV.CHAR_ENCAPSULATE))
                        : object[header];


      lbuf.push(object[header]);
    }

    buf.push(lbuf.join(''));
    buf.push(char(CSV.CHAR_RETURN, CSV.CHAR_NEWLINE));
  }

  return false !== opts.headers
          ? [].concat(headers.join(','), char(CSV.CHAR_NEWLINE)).concat(buf).join('')
          : buf.join('');
}