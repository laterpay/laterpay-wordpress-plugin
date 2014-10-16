var map = require('map-stream')

module.exports = function soften(size) {
  var hardTab = /\t/g
    , softTab = new Array(size + 1).join(' ')
  
  return map(function(file, callback) {
    try {
      file.contents = new Buffer(String(file.contents).replace(hardTab, softTab))
    } catch (e) {
      console.warn('Error: ' + e.message)
    }
    callback(null, file);
  })
}