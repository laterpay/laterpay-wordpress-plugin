/*jslint node: true */
var gulp        = require('gulp'),
    plugins     = require('gulp-load-plugins')(),
    del         = require('del'),
    runSequence = require('run-sequence'),
    bump        = require('gulp-bump'),
    minimist    = require('minimist'),
    Q           = require('q'),
    p           = {
                allfiles    : [
                                './laterpay/**/*.php',
                                './laterpay/asset_sources/scss/**/*.scss',
                                './laterpay/asset_sources/js/*.js'
                              ],
                jsonfiles   : ['./composer.json', './package.json'],
                phpfiles    : ['./laterpay/**/*.php', '!./laterpay/library/**/*.php'],
                srcSCSS     : './laterpay/asset_sources/scss/*.scss',
                srcJS       : './laterpay/asset_sources/js/',
                srcSVG      : './laterpay/asset_sources/img/**/*.svg',
                srcPNG      : './laterpay/asset_sources/img/**/*.png',
                distJS      : './laterpay/built_assets/js/',
                distCSS     : './laterpay/built_assets/css/',
                distIMG     : './laterpay/built_assets/img/',
            };

var gulpKnownOptions = {
    string: 'version',
    default: { version: '1.0' }
};
var gulpOptions = minimist(process.argv.slice(2), gulpKnownOptions);

// TASKS ---------------------------------------------------------------------------------------------------------------
// clean up all files in the target directories
gulp.task('clean', function(cb) {
    return del([p.distJS + '*.js', p.distCSS + '*.css'], cb);
});

// CSS-related tasks
gulp.task('css-watch', function() {
    return gulp.src(p.srcSCSS)
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.sass({
            errLogToConsole : true,
            sourceComments  : 'normal'
        }))
        // vendorize properties for supported browsers
        .pipe(plugins.autoprefixer('last 3 versions', '> 2%', 'ff > 23', 'ie > 8'))
        .on('error', plugins.notify.onError())
        .pipe(plugins.sourcemaps.write('./maps'))                               // write sourcemaps
        .pipe(gulp.dest(p.distCSS));                                            // move to target folder
});

gulp.task('css-build', function() {
    return gulp.src(p.srcSCSS)
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.sass({
            errLogToConsole : true,
            sourceComments  : 'normal'
        }))
        .on('error', plugins.notify.onError())
        // vendorize properties for supported browsers
        .pipe(plugins.autoprefixer('last 3 versions', '> 2%', 'ff > 23', 'ie > 8'))
        .pipe(plugins.csso())                                                   // compress
        .pipe(gulp.dest(p.distCSS));                                            // move to target folder
});

// Javascript-related tasks
gulp.task('js-watch', function() {
    return gulp.src(p.srcJS + '*.js')
        .pipe(plugins.cached('hinting'))                                        // only process modified files
        .pipe(plugins.jshint('.jshintrc'))
        .pipe(plugins.jshint.reporter(plugins.stylish))
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.sourcemaps.write('./maps'))                               // write sourcemaps
        .pipe(gulp.dest(p.distJS));                                             // move to target folder
});

gulp.task('js-build', function() {
    return gulp.src(p.srcJS + '*.js')
        .pipe(plugins.jshint('.jshintrc'))
        .pipe(plugins.jshint.reporter(plugins.stylish))
        .pipe(plugins.uglify())                                                 // compress with uglify
        .pipe(gulp.dest(p.distJS));                                             // move to target folder
});

gulp.task('js-format', function() {
    return gulp.src(p.srcJS + '*.js')
            .pipe(plugins.sourcemaps.init())
            .pipe(plugins.prettify({
                config  : '.jsbeautifyrc',
                mode    : 'VERIFY_AND_WRITE',
            }))
            .pipe(plugins.sourcemaps.write('./maps'))                           // write sourcemaps
            .pipe(gulp.dest(p.srcJS));
});

// Image-related tasks
gulp.task('img-build-svg', function() {
    return gulp.src(p.srcSVG)
            .pipe(plugins.svgmin())                                                 // compress with svgmin
            .pipe(gulp.dest(p.distIMG));                                            // move to target folder
});
gulp.task('img-build-png', function() {
    return gulp.src(p.srcPNG)
        .pipe(plugins.tinypng('6r1BdukU9EqrtUQ5obGa-6VpaH2ZlI-a'))              // compress with TinyPNG
        .pipe(gulp.dest(p.distIMG));                                            // move to target folder
});
gulp.task('img-build', function() {
    var deferred = Q.defer();
    runSequence(['img-build-svg', 'img-build-png'], function(error){
        if (error) {
            deferred.reject(error);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});

// ensure consistent whitespace etc. in files
gulp.task('fileformat', function() {
    return gulp.src(p.allfiles)
            .pipe(plugins.lintspaces({
                indentation     : 'spaces',
                spaces          : 4,
                trailingspaces  : true,
                newline         : true,
                newlineMaximum  : 2,
            }))
            .pipe(plugins.lintspaces.reporter());
});

// check PHP coding standards
gulp.task('sniffphp', function() {
    return gulp.src(p.phpfiles)
            .pipe(plugins.phpcs({
                bin             : '/usr/local/bin/phpcs',
                standard        : 'WordPress',
                warningSeverity : 0,
            }))
            .pipe(plugins.phpcs.reporter('log'));
});


// COMMANDS ------------------------------------------------------------------------------------------------------------
gulp.task('default', ['clean', 'img-build', 'css-watch', 'js-watch'], function() {
    // watch for changes
    gulp.watch(p.allfiles,          ['fileformat']);
    gulp.watch(p.srcSCSS,           ['css-watch']);
    gulp.watch(p.srcJS + '*.js',    ['js-watch']);
});

// check code quality before git commit
gulp.task('precommit', ['sniffphp', 'js-format'], function() {
    gulp.src(p.srcJS + '*.js')
        .pipe(plugins.jshint('.jshintrc'))
        .pipe(plugins.jshint.reporter(plugins.stylish));

    gulp.src(p.distCSS + '*.css')
        .pipe(plugins.csslint())
        .pipe(plugins.csslint.reporter());
});

// build project for release
gulp.task('build', ['clean'], function() {
    var deferred = Q.defer();
    runSequence(['img-build','css-build','js-build'], function(error){
        if (error) {
            deferred.reject(error);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});

gulp.task('bump', function() {
    return gulp.src(p.jsonfiles)
        .pipe(bump({version:gulpOptions.version}))
        .pipe(gulp.dest('./'));
});

gulp.task('release:production', ['build'], function() {
    var deferred = Q.defer();
    runSequence(['bump'], function(error){
        if (error) {
            deferred.reject(error);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});
