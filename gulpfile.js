'use strict';

const gulp = require('gulp');
const gutil = require('gulp-util');
const uglify = require('gulp-uglify');
const sourcemaps = require('gulp-sourcemaps');
const rename = require('gulp-rename');
const cleanCSS = require('gulp-clean-css');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');

const production = !gutil.env.development;

// Copy the files
gulp.task('copy', function () {
    return gulp.src(['node_modules/fine-uploader/fine-uploader/**/*'], {'base': 'node_modules/fine-uploader/fine-uploader'})
        .pipe(gulp.dest('assets/fine-uploader'));
});

// Build app.js
gulp.task('scripts', function () {
    return gulp.src('assets/handler.js')
        .pipe(production ? uglify() : gutil.noop())
        .pipe(rename('handler.min.js'))
        .on('error', gutil.log)
        .pipe(production ? sourcemaps.write() : gutil.noop())
        .pipe(gulp.dest('assets'));
});

// Build bundle.css
gulp.task('styles', function () {
    return gulp.src('assets/handler.css')
        .pipe(production ? sourcemaps.init() : gutil.noop())
        .pipe(postcss([autoprefixer({browsers: ['> 5%']})]))
        .pipe(production ? sourcemaps.write() : gutil.noop())
        .pipe(production ? cleanCSS() : gutil.noop())
        .pipe(rename('handler.min.css'))
        .pipe(gulp.dest('assets'));
});

// Watch task
gulp.task('watch', function () {
    gulp.watch(['assets/*.js'], ['scripts']);
    gulp.watch(['assets/*.css'], ['styles']);
});

// Build task
gulp.task('build', ['copy', 'scripts', 'styles']);

// Build by default
gulp.task('default', ['build']);

// Build and watch task
gulp.task('build:watch', ['build', 'watch']);