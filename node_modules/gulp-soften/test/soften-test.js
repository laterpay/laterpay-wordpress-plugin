var should = require('should')
  , soften = require('../index')
  , gulp = require('gulp')
  , map = require('map-stream')
  , fs = require('fs')
  , path = require('path')
  
describe('soften', function () {
  var stream
    , result
    , tmp = path.join(__dirname, '/tmp')
  
  before(function () {
    fs.createWriteStream(tmp).end('\t\t\t\t')
  })
  
  it('should receive the stream', function () {
    stream = gulp.src(tmp).pipe(soften(2))
  })
  
  it('should pass along the stream', function () {
    stream.pipe(gulp.dest(__dirname))
  })
  
  it('should convert tabs to spaces', function (done) {
    stream.pipe(map(function (file, callback) {
      result = String(file.contents)
      result.should.equal('        ')
      done()
    }))
  })
  
  after(function () {
    fs.unlink(tmp)
  })
})