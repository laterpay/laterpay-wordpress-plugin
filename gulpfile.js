/*jslint node: true */
var gulp                        = require('gulp'),
    plugins                     = require('gulp-load-plugins')(),
    del                         = require('del'),
    runSequence                 = require('run-sequence'),
    conventionalGithubReleaser  = require('conventional-github-releaser'),
    bump                        = require('gulp-bump'),
    minimist                    = require('minimist'),
    Q                           = require('q'),
    git                         = require('gulp-git'),
    gutil                       = require('gulp-util'),
    replace                     = require('gulp-replace'),
    request                     = require('request'),
    dateFormat                  = require('dateformat'),
    fs                          = require('fs'),
    p                           = {
        githubMilestones: 'https://api.github.com/repos/laterpay/laterpay-wordpress-plugin/milestones',
        githubIssues    : 'https://api.github.com/repos/laterpay/laterpay-wordpress-plugin/issues?state=all&milestone=',
        allfiles        : [
                            './laterpay/**/*.php',
                            './laterpay/asset_sources/scss/**/*.scss',
                            './laterpay/asset_sources/js/*.js'
                        ],
        mainPhpFile     : './laterpay/laterpay.php',
        changelogFile   : './laterpay/README.txt',
        jsonfiles       : ['./composer.json', './package.json'],
        phpfiles        : ['./laterpay/**/*.php', '!./laterpay/library/**/*.php'],
        srcSCSS         : './laterpay/asset_sources/scss/*.scss',
        srcJS           : './laterpay/asset_sources/js/',
        srcSVG          : './laterpay/asset_sources/img/**/*.svg',
        srcPNG          : './laterpay/asset_sources/img/**/*.png',
        distJS          : './laterpay/built_assets/js/',
        distCSS         : './laterpay/built_assets/css/',
        distIMG         : './laterpay/built_assets/img/',
        distPlugin      : './laterpay/',
    };
// OPTIONS -------------------------------------------------------------------------------------------------------------
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
                mode    : 'VERIFY_AND_WRITE'
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
            console.log(error.message);
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
                newlineMaximum  : 2
            }))
            .pipe(plugins.lintspaces.reporter());
});

// check PHP coding standards
gulp.task('sniffphp', function() {
    return gulp.src(p.phpfiles)
            .pipe(plugins.phpcs({
                bin             : '/usr/local/bin/phpcs',
                standard        : 'WordPress',
                warningSeverity : 0
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
gulp.task('precommit-css', function() {
    return gulp.src(p.distCSS + '*.css')
        .pipe(plugins.csslint())
        .pipe(plugins.csslint.reporter());
});

gulp.task('precommit-js', function() {
    return gulp.src(p.srcJS + '*.js')
        .pipe(plugins.jshint('.jshintrc'))
        .pipe(plugins.jshint.reporter(plugins.stylish));
});

gulp.task('precommit', ['sniffphp', 'js-format'], function() {
    var deferred = Q.defer();
    runSequence(['precommit-css','precommit-js'], function(error){
        if (error) {
            deferred.reject(error.message);
            console.log(error.message);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});

// build project for release
gulp.task('build', ['clean'], function() {
    var deferred = Q.defer();
    runSequence(['img-build','css-build','js-build'], function(error){
        if (error) {
            deferred.reject(error.message);
            console.log(error.message);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});

// RELEASE -------------------------------------------------------------------------------------------------------------
gulp.task('changelog', function () {
    var getMilestoneNumber = function() {
            var deferred = Q.defer(),
                options = {
                    url: p.githubMilestones,
                    headers: {
                        'User-Agent': 'request'
                    }
                };
            request
                .get(options, function(error, response, body) {
                    if (!error && response.statusCode === 200) {
                        var data = JSON.parse(body);
                        if(data[0]) {
                            deferred.resolve({milestone: data[0]});
                            return;
                        }
                    }
                    var err = 'Error has been appeared while getting milestone';
                    console.log(err);
                    deferred.reject(err);
                })
                .on('error', function(err) {
                    deferred.reject(err);
                    console.log(err);
                });
            return deferred.promise;
        },
        getMilestoneIssues = function(result) {
            var deferred = Q.defer(),
                options = {
                    url: p.githubIssues + result.milestone.number,
                    headers: {
                        'User-Agent': 'request'
                    }
                };
            request
                .get(options, function(error, response, body) {
                    if (!error && response.statusCode === 200) {
                        result.issues = JSON.parse(body);
                        deferred.resolve(result);
                    } else {
                        var err = 'Error has been appeared while getting issues';
                        console.log(err);
                        deferred.reject(err);
                    }
                })
                .on('error', function(err) {
                    deferred.reject(err);
                    console.log(err);
                });
            return deferred.promise;
        };

    return getMilestoneNumber()
        .then(getMilestoneIssues)
        .then(function(result){
            if(result.issues){
                result.formated = result.issues.map(function(issue){
                    return '* ' + issue.title;
                });
                result.formated = result.formated.join('\n');
                return result;
            }
        })
        .then(function(result){
            var changelog = [
                '$1== ',
                gulpOptions.version,
                '( ', dateFormat(new Date(),'mmmm d, yyyy'), ' )',
                ': ' + result.milestone.description,
                ' ==\n',
                result.formated,
                '\n\n'];
            return gulp.src(p.changelogFile)
                .pipe(replace(/(==\s*Changelog\s*==\n*)/g, changelog.join('')))
                .pipe(gulp.dest(p.distPlugin));
        });

});

gulp.task('bump-version-json', function() {
    return gulp.src(p.jsonfiles)
        .pipe(bump({version:gulpOptions.version}).on('error', gutil.log))
        .pipe(gulp.dest('./'));
});

gulp.task('bump-version-php', function() {
    return gulp.src([p.mainPhpFile])
        .pipe(replace(/Version:\s*(.*)/g, 'Version: ' + gulpOptions.version))
        .pipe(gulp.dest(p.distPlugin));
});

gulp.task('bump-version', function() {
    var deferred = Q.defer();
    runSequence(['bump-version-json','bump-version-php'], function(error){
        if (error) {
            deferred.reject(error.message);
            console.log(error.message);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});

gulp.task('github-release', function(done) {
    var deferred = Q.defer();
    conventionalGithubReleaser({
            type: 'oauth',
            key: 'clientID',
            secret: 'clientSecret'
        }, {
            //preset: 'angular' // Or to any other commit message convention you use.
        }, function() {
            deferred.resolve();
            done();
        }
    );
    return deferred.promise;
});

gulp.task('commit-changes', function () {
    return gulp.src('.')
        .pipe(git.commit('[Prerelease] Bumped version number ' + gulpOptions.version));
});

gulp.task('push-changes', function (cb) {
    git.push('origin', 'master', cb);
});

gulp.task('create-new-tag', function (cb) {
    var version = getPackageJsonVersion();
    git.tag(version, 'Created Tag for version: ' + version, function (error) {
        if (error) {
            return cb(error);
        }
        git.push('origin', 'master', {args: '--tags'}, cb);
    });

    function getPackageJsonVersion () {
        // We parse the json file instead of using require because require caches
        // multiple calls so the version number won't be updated
        return JSON.parse(fs.readFileSync('./package.json', 'utf8')).version;
    }
});

gulp.task('release:production', function (callback) {
    var deferred = Q.defer();
    runSequence(
        'build',
        'bump-version',
        'changelog',
        'commit-changes',
        'push-changes',
        'create-new-tag',
        'github-release',
        function (error) {
            if (error) {
                deferred.reject(error.message);
                console.log(error.message);
            } else {
                deferred.resolve();
                console.log('RELEASE FINISHED SUCCESSFULLY');
            }
            callback(error);
        });
    return deferred.promise;
});
