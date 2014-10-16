/*global describe, it*/
"use strict";

var fs = require("fs"),
  es = require("event-stream"),
  should = require("should");

require("mocha");

delete require.cache[require.resolve("../")];

var gutil = require("gulp-util"),
  docco = require("../");

describe("gulp-docco", function () {

  var expectedFile = new gutil.File({
    path: "test/expected/hello.txt",
    cwd: "test/",
    base: "test/expected",
    contents: fs.readFileSync("test/expected/hello.txt")
  });

  xit("should produce expected file via buffer", function (done) {

    var srcFile = new gutil.File({
      path: "test/fixtures/docco.litcoffee",
      cwd: "test/",
      base: "test/fixtures",
      contents: fs.readFileSync("test/fixtures/docco.litcoffee")
    });

    var stream = docco();

    stream.on("error", function(err) {
      should.exist(err);
      done(err);
    });

    stream.on("data", function (newFile) {

      should.exist(newFile);
      should.exist(newFile.contents);

      String(newFile.contents).should.equal(String(expectedFile.contents));
      done();
    });

    stream.write(srcFile);
    stream.end();
  });

  it("should error on stream", function (done) {

    var srcFile = new gutil.File({
      path: "test/fixtures/docco.litcoffee",
      cwd: "test/",
      base: "test/fixtures",
      contents: fs.createReadStream("test/fixtures/docco.litcoffee")
    });

    var stream = docco();

    stream.on("error", function(err) {
      should.exist(err);
      done();
    });

    stream.on("data", function (newFile) {
      newFile.contents.pipe(es.wait(function(err, data) {
        done(err);
      }));
    });

    stream.write(srcFile);
    stream.end();
  });

  /*
  it("should produce expected file via stream", function (done) {

    var srcFile = new gutil.File({
      path: "test/fixtures/hello.txt",
      cwd: "test/",
      base: "test/fixtures",
      contents: fs.createReadStream("test/fixtures/hello.txt")
    });

    var stream = docco("World");

    stream.on("error", function(err) {
      should.exist(err);
      done();
    });

    stream.on("data", function (newFile) {

      should.exist(newFile);
      should.exist(newFile.contents);

      newFile.contents.pipe(es.wait(function(err, data) {
        should.not.exist(err);
        data.should.equal(String(expectedFile.contents));
        done();
      }));
    });

    stream.write(srcFile);
    stream.end();
  });
  */
});
