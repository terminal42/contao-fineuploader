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

// Copy the FineUploader
gulp.task('copy-fineuploader', function () {
    return gulp.src(
        ['node_modules/fine-uploader/fine-uploader/**/*'],
        {'base': 'node_modules/fine-uploader/fine-uploader'}
    )
        .pipe(gulp.dest('src/Resources/public/fine-uploader'));
});

// Copy the Sortable
gulp.task('copy-sortable', function () {
    return gulp.src(
        [
            'node_modules/sortablejs/Sortable.js',
            'node_modules/sortablejs/Sortable.min.js'
        ],
        {'base': 'node_modules/sortablejs'}
    )
        .pipe(rename(function (path) {
            path.basename = path.basename.toLowerCase();
        }))
        .pipe(gulp.dest('src/Resources/public/sortable'));
});

// Prepare JavaScript files
gulp.task('scripts', function () {
    return gulp.src(
        [
            'src/Resources/public/backend/backend.js',
            'src/Resources/public/frontend/frontend.js',
            'src/Resources/public/handler/handler.js'
        ],
        {'base': '.'}
    )
        .pipe(production ? uglify() : gutil.noop())
        .pipe(rename(function (path) {
            path.extname = '.min' + path.extname;
        }))
        .on('error', gutil.log)
        .pipe(production ? sourcemaps.write() : gutil.noop())
        .pipe(gulp.dest('./'));
});

// Prepare CSS files
gulp.task('styles', function () {
    return gulp.src([
            'src/Resources/public/backend/backend.css',
            'src/Resources/public/frontend/frontend.css',
            'src/Resources/public/handler/handler.css'
        ],
        {'base': '.'}
    )
        .pipe(production ? sourcemaps.init() : gutil.noop())
        .pipe(postcss([autoprefixer({browsers: ['> 5%']})]))
        .pipe(production ? sourcemaps.write() : gutil.noop())
        .pipe(production ? cleanCSS() : gutil.noop())
        .pipe(rename(function (path) {
            path.extname = '.min' + path.extname;
        }))
        .pipe(gulp.dest('./'));
});

// Watch task
gulp.task('watch', function () {
    gulp.watch(['src/Resources/public/**/*.js'], ['scripts']);
    gulp.watch(['src/Resources/public/**/*.css'], ['styles']);
});

// Build task
gulp.task('build', ['copy-fineuploader', 'copy-sortable', 'scripts', 'styles']);

// Build by default
gulp.task('default', ['build']);

// Build and watch task
gulp.task('build:watch', ['build', 'watch']);
