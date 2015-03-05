var autoprefixer    = require('gulp-autoprefixer'),
    cached          = require('gulp-cached'),
    csslint         = require('gulp-csslint'),
    del             = require('del'),
    git             = require('gulp-git'),
    gulp            = require('gulp'),
    jshint          = require('gulp-jshint'),
    lintspaces      = require('gulp-lintspaces'),
    notify          = require('gulp-notify'),
    phpcs           = require('gulp-phpcs'),
    prettify        = require('gulp-jsbeautifier'),
    sourcemaps      = require('gulp-sourcemaps'),
    stylish         = require('jshint-stylish'),
    sass            = require('gulp-sass'),
    svgmin          = require('gulp-svgmin'),
    tinypng         = require('gulp-tinypng'),
    uglify          = require('gulp-uglify'),
    p               = {
                        allfiles    : [
                                        './laterpay/**/*.php',
                                        './laterpay/asset_sources/stylus/**/*.styl',
                                        './laterpay/asset_sources/js/*.js'
                                      ],
                        phpfiles    : ['./laterpay/**/*.php', '!./laterpay/library/**/*.php'],
                        srcSCSS     : './laterpay/asset_sources/scss/*.scss',
                        srcJS       : './laterpay/asset_sources/js/',
                        srcSVG      : './laterpay/asset_sources/img/**/*.svg',
                        srcPNG      : './laterpay/asset_sources/img/**/*.png',
                        distJS      : './laterpay/built_assets/js/',
                        distCSS     : './laterpay/built_assets/css/',
                        distIMG     : './laterpay/built_assets/img/',
                    };


// TASKS ---------------------------------------------------------------------------------------------------------------
// clean up all files in the target directories
gulp.task('clean', function(cb) {
    del([p.distJS + '*.js', p.distCSS + '*.css'], cb);
});

// CSS-related tasks
gulp.task('css-watch', function() {
    gulp.src(p.srcSCSS)
        .pipe(sourcemaps.init())
        .pipe(sass({
            errLogToConsole : true,
            sourceComments  : 'normal'
        }))
        .pipe(autoprefixer('last 3 versions', '> 2%', 'ff > 23', 'ie > 8'))     // vendorize properties for supported browsers
        .on('error', notify.onError())
        .pipe(sourcemaps.write('./maps'))                                       // write sourcemaps
        .pipe(gulp.dest(p.distCSS));                                            // move to target folder
});

gulp.task('css-build', function() {
    gulp.src(p.srcSCSS)
        .pipe(sourcemaps.init())
        .pipe(sass({
            errLogToConsole : true,
            sourceComments  : 'normal'
        }))
        .on('error', notify.onError())
        .pipe(autoprefixer('last 3 versions', '> 2%', 'ff > 23', 'ie > 8'))     // vendorize properties for supported browsers
        .pipe(gulp.dest(p.distCSS));                                            // move to target folder
});

// Javascript-related tasks
gulp.task('js-watch', function() {
    gulp.src(p.srcJS + '*.js')
        .pipe(cached('hinting'))                                                // only process modified files
            .pipe(jshint('.jshintrc'))
            .pipe(jshint.reporter(stylish))
            .pipe(sourcemaps.init())
            .pipe(sourcemaps.write('./maps'))                                   // write sourcemaps
            .pipe(gulp.dest(p.distJS));                                         // move to target folder
});

gulp.task('js-build', function() {
    gulp.src(p.srcJS + '*.js')
        .pipe(jshint('.jshintrc'))
        .pipe(jshint.reporter(stylish))
        .pipe(uglify())                                                         // compress with uglify
        .pipe(gulp.dest(p.distJS));                                             // move to target folder
});

gulp.task('js-format', function() {
    return gulp.src(p.srcJS + '*.js')
            .pipe(sourcemaps.init())
            .pipe(prettify({
                config  : '.jsbeautifyrc',
                mode    : 'VERIFY_AND_WRITE',
            }))
            .pipe(sourcemaps.write('./maps'))                                   // write sourcemaps
            .pipe(gulp.dest(p.srcJS));
});

// Image-related tasks
gulp.task('img-build', function() {
    gulp.src(p.srcSVG)
        .pipe(svgmin())                                                         // compress with svgmin
        .pipe(gulp.dest(p.distIMG));                                            // move to target folder

    gulp.src(p.srcPNG)
        .pipe(tinypng('6r1BdukU9EqrtUQ5obGa-6VpaH2ZlI-a'))                      // compress with TinyPNG
        .pipe(gulp.dest(p.distIMG));                                            // move to target folder
});

// ensure consistent whitespace etc. in files
gulp.task('fileformat', function() {
    return gulp.src(p.allfiles)
            .pipe(lintspaces({
                indentation     : 'spaces',
                spaces          : 4,
                trailingspaces  : true,
                newline         : true,
                newlineMaximum  : 2,
            }))
            .pipe(lintspaces.reporter());
});

// check PHP coding standards
gulp.task('sniffphp', function() {
    return gulp.src(p.phpfiles)
            .pipe(phpcs({
                bin             : '/usr/local/bin/phpcs',
                standard        : 'WordPress',
                warningSeverity : 0,
            }))
            .pipe(phpcs.reporter('log'));
});

// update git submodules
gulp.task('updateSubmodules', function() {
    git.updateSubmodule({args: '--init'});
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
        .pipe(jshint('.jshintrc'))
        .pipe(jshint.reporter(stylish));

    gulp.src(p.distCSS + '*.css')
        .pipe(csslint())
        .pipe(csslint.reporter());
});

// build project for release
gulp.task('build', ['clean', 'updateSubmodules'], function() {
    gulp.start('img-build');
    gulp.start('css-build');
    gulp.start('js-build');
});
