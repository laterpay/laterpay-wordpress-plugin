gulp-closure-compiler-service
=============================
Gulp plugin to compile JavaScript with the Google
[Closure compiler service](https://developers.google.com/closure/compiler/docs/api-ref).
No Java dependency.


Installation
------------

    $ npm install gulp-closure-compiler-service


Usage
-----

    var closure = require('gulp-closure-compiler-service');

    gulp.task('compile', function() {
      return gulp.src('src/*.js')
        .pipe(closure())
        .pipe(gulp.dest('dist'));
    });


[Options](https://github.com/gavinhungry/closure-compiler-service/blob/master/README.md#default-options)
can be passed to the API:

    .pipe(closure({
      compilation_level: 'WHITESPACE_ONLY'
    }))


License
-------
Released under the terms of the
[MIT license](http://tldrlegal.com/license/mit-license). See **LICENSE**.
