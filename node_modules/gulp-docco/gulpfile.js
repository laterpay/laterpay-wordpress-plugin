(function(){
  'use strict';

  // Gulp
  var gulp = require('gulp');
  var gutil = require('gulp-util');

  // Some basic linting
  var eslint = require('gulp-eslint');
  var jshint = require('gulp-jshint');

  var jsreporter = require('jshint-stylish');
  var esreporter = require('eslint-stylish');

  var fs = require('fs');
  var jsconfig = JSON.parse(fs.readFileSync('./.jshintrc'));
  var esconfig = JSON.parse(fs.readFileSync('./.eslintrc'));

  gulp.task('lint', function(){
    gulp.src([
      'gulpfile.js',
      'index.js',
      'lib/**/*.js'
    ])
    .pipe(gutil.combine(
      eslint(esconfig),
      eslint.formatEach(esreporter),
      jshint(jsconfig),
      jshint.reporter(jsreporter)
    )());
  });

  var gulco = require('./');

  gulp.task('gulco-classic', function() {
    gulp.src([
      './test/**/*.litcoffee'
    ])
    .pipe(gulco({layout: 'classic'}))
    .pipe(gulp.dest('./doc/docco-classic'));
  });

  gulp.task('gulco-linear', function() {
    gulp.src([
      './test/**/*.litcoffee'
    ])
    .pipe(gulco({layout: 'linear'}))
    .pipe(gulp.dest('./doc/docco-linear'));
  });

  gulp.task('gulco-parallel', function() {
    gulp.src([
      './test/**/*.litcoffee'
    ])
    .pipe(gulco({layout: 'parallel'}))
    .pipe(gulp.dest('./doc/docco-parallel'));
  });


  gulp.task('gulco', ['gulco-parallel', 'gulco-linear', 'gulco-classic']);


}());
