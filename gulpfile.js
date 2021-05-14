'use strict';

// 1. Install modules with npm install command (see package.json)
// 2. Configure your project data folder (theme, sass, etc...)
// 3. Setup your ftp info in the gulp-private file (see example in gulp-private-example.js)
// 4. Run "gulp watcher" to automate sass compilation and ftp push on save


var gulp = require('gulp');
var gutil = require( 'gulp-util' );
var ftp = require( 'vinyl-ftp' );
var ftpinfo = require('./gulp-private');



var projectdata = {
  "sync" : [
    './woocommerce-apb-promo/**',
  ]
};




// helper function to build an FTP connection based on our configuration
function getFtpConnection() {
    return ftp.create({
        host: ftpinfo.host,
        user: ftpinfo.user,
        password: ftpinfo.password,
        parallel: 5,
        log: gutil.log
    });
}

gulp.task('ftp-deploy', function() {

    var conn = getFtpConnection();

    return gulp.src(projectdata.sync, { base: '.', buffer: false })
        .pipe( conn.newer( ftpinfo.remoteFolder ) ) // only upload newer files
        .pipe( conn.dest( ftpinfo.remoteFolder ) )
    ;
});

gulp.task('ftp-deploy-watch', function() {

    var conn = getFtpConnection();

    gulp.watch(projectdata.sync)
      .on('change', function(event) {

        console.log('Changes detected! Uploading file "' + event);

        return gulp.src( [event], { base: '.', buffer: false } )
          //.pipe( conn.newer( remoteFolder ) ) // only upload newer files
          .pipe( conn.dest( ftpinfo.remoteFolder ) )
        ;
      });
});


gulp.task('watcher', gulp.parallel ('ftp-deploy-watch')); 


  