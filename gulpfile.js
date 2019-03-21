/*jslint node: true */
var gulp                        = require('gulp'),
    plugins                     = require('gulp-load-plugins')(),
    del                         = require('del'),
    runSequence                 = require('run-sequence'),
    Github                      = require('github'),
    minimist                    = require('minimist'),
    Q                           = require('q'),
    prompt                      = require('prompt'),
    dateFormat                  = require('dateformat'),
    zip                         = require('gulp-zip'),
    wpPot                       = require('gulp-wp-pot'),
    plumber                     = require('gulp-plumber'),
    tinypng                     = require('gulp-tinypng-extended'),
    p                           = {
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
                                        distSVN         : './svn-working-copy/',
                                        svnURL          : 'http://plugins.svn.wordpress.org/laterpay',
                                        nonVIP          : './vip-build/laterpay/application/Compatibility',
                                        distVIP         : './vip-build',
                                    };
// OPTIONS -------------------------------------------------------------------------------------------------------------
var gulpKnownOptions = {
    string: 'version',
    default: { version: '2.5.1' }
};
var gulpOptions = minimist(process.argv.slice(2), gulpKnownOptions);
gulpOptions.svn = {};
gulpOptions.git = {};

// TASKS ---------------------------------------------------------------------------------------------------------------
// clean up all files in the target directories
gulp.task('clean', function(cb) {
    return del([
            p.distJS + '*.js',
            p.distJS + 'maps/*.map',
            p.distCSS + '*.css',
            p.distCSS + '*.scss',
            p.distCSS + 'maps/*.map',
            p.distIMG + '*.svg',
            p.distPlugin + 'vendor'
        ], cb);
});

// create laterpay.po file.
gulp.task('makepot', function () {
    return gulp.src(p.phpfiles)
        .pipe(wpPot( {
            domain: 'laterpay',
            package: 'LaterPay'
        } ))
        .pipe(gulp.dest( p.distPlugin + 'languages/' + 'laterpay.po'));
});

// remove non-vip files.
gulp.task( 'vip-clean', function( cb ) {
    return del( [
        p.distVIP,
        p.distPlugin + 'laterpay.zip'
    ], cb );
});

// copy plugin files to vip build.
gulp.task('vip-copy', function(){
    return gulp.src(p.distPlugin + '**/*')
        .pipe(gulp.dest( p.distVIP + '/laterpay' ));
});

// remove un-wanted files from vip build.
gulp.task( 'vip-filter', function( cb ) {
    return del( [
        p.nonVIP
    ], cb );
});

// create zip for vip build
gulp.task( 'vip-zip', function() {
    return gulp.src('./vip-build/laterpay/**')
        .pipe(zip('laterpay.zip'))
        .pipe(gulp.dest('./'))
});

// clean up after zip creation
gulp.task( 'vip-final', function( cb ) {
    return del( [
        './vip-build'
    ], cb );
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
        }).on('error', plugins.notify.onError()))
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
gulp.task('img-build-png',function(){
    return gulp.src(p.srcPNG)
               .pipe(plumber())
               .pipe(tinypng({                                                    // compress with TinyPNG
                   key     : 'zHVYVbFVusUoUxg4qs6GnrIMnHhPfGfG',
                   sigFile : './laterpay/asset_sources/.tinypng-sigs',
                   log     : true,
               }))
               .pipe(gulp.dest(p.distIMG));                                       // move to target folder
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
    runSequence(['img-build','css-build','js-build','composer'], function(error){
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

// common functions
var getMilestoneNumber = function() {
        var github = new Github({
                version: '3.0.0'
            }),
            deferred = Q.defer(),
            options = {
                'user': 'laterpay',
                'repo': 'laterpay-wordpress-plugin',
                'state': 'open'
            };
        github.issues.getAllMilestones(options, function(error, data) {
            if (!error) {
                if(data[0]) {
                    deferred.resolve({milestone: data[0]});
                    return;
                }
            }
            var err = 'Error has been appeared while getting milestone';
            console.log(error);
            deferred.reject(err);
        });
        return deferred.promise;
    },
    getMilestoneIssues = function(result) {
        var github = new Github({
                version: '3.0.0'
            }),
            deferred = Q.defer(),
            options = {
                'user': 'laterpay',
                'repo': 'laterpay-wordpress-plugin',
                'milestone': result.milestone.number,
                'state': 'all'
            };
        github.issues.repoIssues(options, function(error, data) {
            if (!error) {
                result.issues = data;
                deferred.resolve(result);
            } else {
                var err = 'Error has been appeared while getting issues';
                console.log(error);
                deferred.reject(err);
            }
        });
        return deferred.promise;
    },
    promptUsernamePassword = function(namespace){
        var schema = {
                properties: {
                    username: {
                        'default': 'laterpay',
                        'required': true
                    },
                    password: {
                        'hidden': true
                    }
                }
            },
            deferred = Q.defer();
        prompt.get(schema, function (error, result) {
            if (!error) {
                gulpOptions[namespace].username = result.username;
                gulpOptions[namespace].password = result.password;
                deferred.resolve();
            } else {
                var err = 'Error has been appeared while getting ' + namespace + ' Username and Password';
                console.log(error);
                deferred.reject(err);
            }
        });
        return deferred.promise;
    },
    svnPropset = function(type, path){
        var deferred = Q.defer();
        plugins.svn2.exec({
            cwd: p.distSVN,
            args: 'propset svn:mime-type ' + type + ' ' + path
        }, function(err){
            if(err) {
                console.log(err);
                deferred.reject(err);
            } else {
                deferred.resolve();
            }
        });
        return deferred.promise;
    };
// RELEASE TASKS
gulp.task('changelog', function () {
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
                .pipe(plugins.replace(/(==\s*Changelog\s*==\n*)/g, changelog.join('')))
                .pipe(gulp.dest(p.distPlugin));
        });

});

gulp.task('bump-version-json', function() {
    return gulp.src(p.jsonfiles)
        .pipe(plugins.bump({version:gulpOptions.version}).on('error', plugins.util.log))
        .pipe(gulp.dest('./'));
});

gulp.task('bump-version-php', function() {
    return gulp.src([p.mainPhpFile])
        .pipe(plugins.replace(/Version:\s*(.*)/g, 'Version: ' + gulpOptions.version))
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

gulp.task('composer', function () {
    return plugins.composer('update', {'no-autoloader':true, 'no-dev':true});
});

gulp.task('github-release', function() {
    return promptUsernamePassword('git')
        .then(getMilestoneNumber)
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
            var github = new Github({
                    version: '3.0.0'
                }),
                deferred = Q.defer(),
                options = {
                    'owner': 'laterpay',
                    'repo': 'laterpay-wordpress-plugin',
                    'tag_name': 'v' + gulpOptions.version,
                    'name': result.milestone.description,
                    'body': result.formated
                };
            prompt.start();

            github.authenticate({
                type: 'basic',
                username: gulpOptions.git.username,
                password: gulpOptions.git.password
            });
            github.releases.createRelease(options, function(error, data) {
                if (!error) {
                    result.issues = data;
                    deferred.resolve(result);
                } else {
                    var err = 'Error has been appeared while creating github release';
                    console.log(error);
                    deferred.reject(err);
                }
            });
        });
});

gulp.task('git-commit-changes', function () {
    return gulp.src('.')
        .pipe(plugins.git.commit('[Prerelease] Bumped version number ' + gulpOptions.version));
});

gulp.task('git-push-changes', function (cb) {
    plugins.git.push('origin', 'master', cb);
});

gulp.task('git-create-new-tag', function (cb) {
    plugins.git.tag('v' + gulpOptions.version, 'Created Tag for version: ' + gulpOptions.version, function (error) {
        if (error) {
            return cb(error);
        }
        plugins.git.push('origin', 'master', {args: '--tags'}, cb);
    });
});

// SVN tasks
// Run svn add
gulp.task('svn-add', function(){
   var svnChangeList = function(types){
           var deferred = Q.defer();
           console.log('Started SVN changelist...');
           plugins.svn2.exec({
               args: 'st | grep "^[' + types + ']" | cut -c9-',
               cwd: p.distSVN
           }, function(err, response){
               if(err) {
                   console.log(err);
                   deferred.reject(err);
               } else {
                   var data = [];
                   if(response) {
                       data = response.split(/\r\n|\r|\n/g);
                   }
                   deferred.resolve(data);
               }
           });
           return deferred.promise;
       },
       svnAdd = function () {
           var deferred = Q.defer();
           console.log('Started SVN adding of the new and modified files...');
           plugins.svn2.add('*', {
               args: '--force',
               cwd: p.distSVN
           }, function(err){
               if(err) {
                   console.log(err);
                   deferred.reject(err);
               } else {
                   deferred.resolve();
               }
           });
           return deferred.promise;
       },
       svnDelete = function (file) {
           var deferred = Q.defer();
           plugins.svn2.delete(file, {
               cwd: p.distSVN
           }, function(err){
               if(err) {
                   console.log(err);
                   deferred.reject(err);
               } else {
                   deferred.resolve();
               }
           });
           return deferred.promise;
       },
       svnDeleteMissed = function (data) {
           var deferred = Q.defer(),
               chain;
           console.log('Started SVN removing of the missed files...');
           data.forEach(function(file){
               if(!file) {
                   return;
               }
               if(!chain){
                   chain = svnDelete(file);
               } else {
                   chain = chain.then(function(file){ return function(){
                       return svnDelete(file);
                   };}(file));
               }
           });
           if(chain){
               chain.done(function(){
                   deferred.resolve();
               });
               chain.catch(function(){
                   deferred.reject('Error while removing missed files!');
               });
           } else {
               deferred.resolve();
           }

           return deferred.promise;
       };
    return svnAdd()
        .then(function(){
            return svnChangeList('!');
        })
        .then(svnDeleteMissed);

});

// Run svn commit
gulp.task('svn-commit', ['svn-prompt-credentials'], function(){
    var deferred = Q.defer();
    plugins.svn2.commit('Release ' + gulpOptions.version, {
        cwd: p.distSVN,
        username: gulpOptions.svn.username,
        password: gulpOptions.svn.password
    }, function(err){
        if(err) {
            console.log(err);
            deferred.reject(err);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});

// Run svn tag
gulp.task('svn-tag', ['svn-prompt-credentials'], function(){
    var deferred = Q.defer();
    plugins.svn2.tag('v' + gulpOptions.version, 'Release ' + gulpOptions.version,{
        cwd: p.distSVN,
        projectRoot: p.svnURL,
        username: gulpOptions.svn.username,
        password: gulpOptions.svn.password
    }, function(err){
        if(err) {
            console.log(err);
            deferred.reject(err);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});

gulp.task('svn-prompt-credentials', function(){
    if(!gulpOptions.svn.username || !gulpOptions.svn.password) {
        return promptUsernamePassword('svn');
    }
});

// clean up all files in the target directories
gulp.task('svn-clean', function(cb) {
    return del([
        p.distSVN
    ], cb);
});
// clean up all files in the target directories
gulp.task('svn-clean-trunk', function(cb) {
    return del([
        p.distSVN + 'trunk'
    ], cb);
});
gulp.task('svn-copy-laterpay', function(){
    return gulp.src(p.distPlugin + '**/*')
        .pipe(gulp.dest(p.distSVN + 'trunk'));
});
gulp.task('svn-checkout', function(){
    var deferred = Q.defer();
    console.log('Fetching SVN repo[' + p.svnURL + ']...');
    plugins.svn2.checkout(p.svnURL, p.distSVN,{
        username: gulpOptions.svn.username,
        password: gulpOptions.svn.password
    }, function(err){
        if(err) {
            console.log(err);
            deferred.reject(err);
        } else {
            deferred.resolve();
        }
    });
    return deferred.promise;
});

gulp.task('svn-fix-assets', function(){
    return svnPropset('image/jpeg', p.distSVN + 'assets/*.jpg')
        .then(function(){
            return svnPropset('image/jpeg', p.distSVN + 'assets/*.jpeg');
        })
        .then(function(){
            return svnPropset('image/png', p.distSVN + 'assets/*.png');
        });
});

gulp.task('svn-release', function () {
    var deferred = Q.defer();
    runSequence(
        'svn-clean',
        'svn-checkout',
        'svn-clean-trunk',
        'svn-copy-laterpay',
        'svn-add',
        'svn-commit',
        'svn-tag',
        'svn-clean',
        function (error) {
            if (error) {
                deferred.reject(error.message);
                console.log(error.message);
            } else {
                deferred.resolve();
                console.log('SVN deployment FINISHED');
            }
        });
    return deferred.promise;
});

gulp.task('release:production', function () {
    var deferred = Q.defer();
    runSequence(
        'build',
        'bump-version',
        'changelog',
        'composer',
        'git-commit-changes',
        'git-push-changes',
        'git-create-new-tag',
        'github-release',
        'svn-release',
        function (error) {
            if (error) {
                deferred.reject(error.message);
                console.log(error.message);
            } else {
                deferred.resolve();
                console.log('RELEASE FINISHED SUCCESSFULLY');
            }
        });
    return deferred.promise;
});

gulp.task('vip', ['vip-clean'], function () {
    var deferred = Q.defer();
    runSequence(
        'build',
        'composer',
        'vip-copy',
        'vip-filter',
        'vip-zip',
        'vip-final',
        function (error) {
            if (error) {
                deferred.reject(error.message);
                console.log(error.message);
            } else {
                deferred.resolve();
                console.log('VIP BUILD COMPLETE');
            }
        });
    return deferred.promise;
});
