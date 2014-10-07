var autoprefixer    = require('gulp-autoprefixer'),
    // browserSync     = require('browser-sync'),
    // cache           = require('gulp-cache'),
    // changed         = require('gulp-changed'),
    concat          = require('gulp-concat'),
    csso            = require('gulp-csso'),
    del             = require('del'),
    fixmyjs         = require('gulp-fixmyjs'),
    gulp            = require('gulp'),
    jshint          = require('gulp-jshint'),
    notify          = require('gulp-notify'),
    // reload          = browserSync.reload,
    rename          = require('gulp-rename'),
    // sourcemaps      = require('gulp-sourcemaps'),
    stripDebug      = require('gulp-strip-debug'),
    stylus          = require('gulp-stylus'),
    // svgmin          = requre('gulp-svgmin'),
    uglify          = require('gulp-uglify'),
    // uncss           = require('gulp-uncss'),
    p               = {
                        stylus      : './laterpay/assets/stylus/*.styl',
                        sourceJS    : './laterpay/assets/js/*.js',
                        distJs      : './laterpay/assets/js',
                        distCss     : './laterpay/assets/css',
                    };


// TASKS -----------------------------------------------------------------------
// // clean up the target directories
// gulp.task('clean', function(cb) {
//     del([p.distCss, p.distJs], cb)
// });

// CSS related tasks
gulp.task('css', function() {
    return gulp.src(p.stylus)
    .pipe(stylus())                                             // process Stylus sources to CSS
    .on('error', notify.onError())
    // .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
    .pipe(csso())                                               // compress with csso
    .pipe(gulp.dest(p.distCss))                                 // move to target folder
    .pipe(notify({ message: 'CSS task complete :-)' }));
    // .pipe(reload({stream: true}));
});

// Javascript related tasks
gulp.task('js', function() {
    gulp.src(p.sourceJS)
        // .pipe(stripDebug())                                     // remove console, alert, and debugger statements
        .pipe(jshint('.jshintrc'))                              // lint with JSHint
        .pipe(jshint.reporter('default'))
        // .pipe(fixmyjs())                                        // fix JSHint errors if possible
        // .pipe(concat('main.js'))                             // concatenate files
        // .pipe(uglify())                                         // compress with uglify
        // .pipe(rename({suffix: '.min'}))                         // add '.min' suffix to compressed files
        .pipe(gulp.dest(p.distJs))                              // move to target folder
        .pipe(notify({ message: 'JS task complete :-)' }));
});


// COMMANDS --------------------------------------------------------------------
// gulp.task('browserSync', function() {
//     browserSync({
//         server: {
//             baseDir: './laterpay/'
//         }
//     });
// });

// gulp.task('watch', ['clean'], function() {
gulp.task('watch', function() {
    gulp.watch(p.stylus,    ['css']);
    gulp.watch(p.sourceJS,  ['js']);
});

gulp.task('default', function() {
    console.log('Run "gulp watch" or "gulp build" instead!');
});
