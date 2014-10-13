var autoprefixer    = require('gulp-autoprefixer'),
    // browserSync     = require('browser-sync'),
    cached          = require('gulp-cached'),
    // changed         = require('gulp-changed'),
    concat          = require('gulp-concat'),
    csso            = require('gulp-csso'),
    csslint         = require('gulp-csslint'),
    del             = require('del'),
    fixmyjs         = require('gulp-fixmyjs'),
    git             = require('gulp-git'),
    gulp            = require('gulp'),
    // include         = require('gulp-file-include'),
    jshint          = require('gulp-jshint'),
    lintspaces      = require('gulp-lintspaces'),
    notify          = require('gulp-notify'),
    // reload          = browserSync.reload,
    rename          = require('gulp-rename'),
    size            = require('gulp-size'),
    // sourcemaps      = require('gulp-sourcemaps'),
    soften          = require('gulp-soften'),
    stripDebug      = require('gulp-strip-debug'),
    stylish         = require('jshint-stylish'),
    stylus          = require('gulp-stylus'),
    // svgmin          = requre('gulp-svgmin'),
    uglify          = require('gulp-uglify'),
    // uncss           = require('gulp-uncss'),
    p               = {
                        allfiles    : ['./laterpay/**/*.php', './laterpay/assets/stylus/**/*.styl', './laterpay/assets/js/*.js'],
                        srcStylus   : './laterpay/assets/stylus/**/*.styl', // TODO: not sure about the **; I'd rather exclude subfolders like 'vendor'
                        srcJS       : './laterpay/assets/js_src/**/*.js',
                        distJs      : './laterpay/assets/js',
                        distCss     : './laterpay/assets/css',
                    };


// TASKS -----------------------------------------------------------------------
// clean up the target directories
gulp.task('clean', function(cb) {
    del([p.distJs, p.distCss], cb);
});

// CSS related tasks
gulp.task('css', function() {
    gulp.src(p.srcStylus)
        .pipe(soften(4))
        .pipe(stylus({                                              // process Stylus sources to CSS
            linenos: true,                                          // make line numbers available in browser dev tools
            compress: true,
            // urlFunc: 'inline-image',                                // inline images where defined by background-image inline-image([url])
            // TODO: generate sourcemap
        }))
        .on('error', notify.onError())
        // .pipe(csslint())                                            // lint with CSSLint
        // .pipe(csslint.reporter())
        // .pipe(autoprefixer('last 3 versions', '> 2%', 'ff > 23', 'ie > 7')) // vendorize properties for supported browsers
        // .pipe(csso())                                               // compress with csso
        .pipe(gulp.dest(p.distCss))                                 // move to target folder
        .pipe(size())                                               // output size of created files
        .pipe(notify({message: 'CSS task complete :-)'}));
        // .pipe(reload({stream: true}));
});

// Javascript related tasks
gulp.task('js', function() {
    gulp.src(p.srcJS)
        .pipe(soften(4))
        .pipe(cached('hinting'))                                // only process modified files
            // .pipe(stripDebug())                                 // remove console, alert, and debugger statements
            // .pipe(jshint('.jshintrc'))                          // lint with JSHint
            // .pipe(jshint.reporter(stylish))                     // output JSHint results
            // .pipe(fixmyjs())                                    // fix JSHint errors if possible
            // .pipe(concat('main.js'))                         // concatenate files
            // .pipe(uglify())                                     // compress with uglify
            // .pipe(rename({suffix: '.min'}))                     // add '.min' suffix to compressed files
            .pipe(gulp.dest(p.distJs))                          // move to target folder
            .pipe(size())                                       // output size of created files
            .pipe(notify({message: 'JS task complete :-)'}));
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

// update git submodules
gulp.task('updateSubmodules', function() {
    git.updateSubmodule({args: '--init'});
});


// COMMANDS --------------------------------------------------------------------
// gulp.task('browserSync', function() {
//     browserSync({
//         server: {
//             baseDir: './laterpay/'
//         }
//     });
// });

gulp.task('default', ['clean', 'css', 'js'], function() {
    // watch for changes
    gulp.watch(p.allfiles,  ['fileformat']);
    gulp.watch(p.stylus,    ['css']);
    gulp.watch(p.srcJS,     ['js']);
});

// build project for release
// gulp.task('build', ['clean', 'updateSubmodules', 'css', 'js'], function() {
gulp.task('build', ['clean', 'updateSubmodules'], function() {
    // git archive is the right option to export the entire repo
    gulp.start('css');
    gulp.start('js');
});
