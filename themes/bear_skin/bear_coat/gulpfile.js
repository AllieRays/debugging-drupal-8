'use strict';

// core utilities
var gulp = require('gulp'),
  gutil = require('gulp-util'),
  notify = require('gulp-notify'),
  argv = require('yargs').argv,
  gulpif = require('gulp-if'),
  livereload = require('gulp-livereload');

// css utilities
var sass = require('gulp-sass'),
  cssGlobbing = require('gulp-css-globbing'),
  postcss = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  mqpacker = require('css-mqpacker'),
  sourcemaps = require('gulp-sourcemaps');

// js utilities
var jshint = require('gulp-jshint'),
  stylish = require('jshint-stylish');

// image utilities
var imagemin = require('gulp-imagemin');

//  should we build sourcemaps? "gulp build --sourcemaps"
var buildSourceMaps = !!argv.sourcemaps;

// post CSS processors
var processors = [
  autoprefixer({browsers: ['last 2 version', 'IE 9']}), // specify browser compatibility with https://github.com/ai/browserslist
  mqpacker // this will reorganize css into media query groups, better for performance
];

// error notifications
var handleError = function (task) {
  return function (err) {
    gutil.beep();

    notify.onError({
      title: task,
      message: err.message,
      sound: false,
      icon: false
    })(err);

    gutil.log(gutil.colors.bgRed(task + ' error:'), gutil.colors.red(err));

    this.emit('end');
  };
};

gulp.task('sass', function () {
  gutil.log(gutil.colors.yellow('Compiling the theme CSS!'));
  return gulp.src('./sass/*.scss')
    .pipe(cssGlobbing({
      extensions: ['.scss']
    }))
    .pipe(gulpif(buildSourceMaps, sourcemaps.init()))
    .pipe(sass())
    .on('error', handleError('Sass Compiling'))
    .pipe(gulpif(buildSourceMaps, sourcemaps.write()))
    .pipe(postcss(processors))
    .on('error', handleError('Post CSS Processing'))
    .pipe(gulp.dest('./css'))
    .pipe(livereload());
});

gulp.task('panels', function () {
  gutil.log(gutil.colors.yellow('Compiling the panel layouts CSS!'));
  return gulp.src('./panels-layouts/**/*.scss')
    .pipe(cssGlobbing({
      extensions: ['.scss']
    }))
    .pipe(gulpif(buildSourceMaps, sourcemaps.init()))
    .pipe(sass())
    .on('error', handleError('Sass Compiling'))
    .pipe(gulpif(buildSourceMaps, sourcemaps.write()))
    .pipe(postcss(processors))
    .pipe(gulp.dest('./panels-layouts'))
    .on('error', handleError('Post CSS Processing'))
    .pipe(livereload());
});

gulp.task('scripts', function () {
  gutil.log(gutil.colors.yellow('Reviewing JavaScript files!'));
  return gulp.src('./js/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter(stylish))
    .on('error', handleError('JS Linting'));
});

gulp.task('images', function () {
  gutil.log(gutil.colors.yellow('Crunching images!'));
  return gulp.src('./images/**/*.{gif,jpg,png}')
    .pipe(imagemin({
      progressive: true,
      svgoPlugins: [{removeViewBox: false}]
    }))
    .on('error', handleError('Image optimization'))
    .pipe(gulp.dest('./images/'))
    .pipe(livereload());
});

gulp.task('watch', function() {
  livereload.listen(4002);

  gulp.watch("./sass/**/*.scss", ['sass']);
  gulp.watch("./js/*.js", ['scripts']);
  gulp.watch("./images/**/*.{gif,jpg,png}", ['images']);
  gulp.watch("./templates/**/*.php").on('change', function() { livereload.reload() });
});

gulp.task('default', ['sass', 'panels', 'watch']);
gulp.task('styles', ['sass', 'panels']);
gulp.task('build', ['sass', 'panels', 'scripts', 'images']);