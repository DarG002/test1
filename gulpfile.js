var gulp = require('gulp'),
    wiredep      = require('wiredep').stream,
    browserSync  = require('browser-sync').create(),
    concatCSS    = require('gulp-concat-css'),
    autoprefixer = require('gulp-autoprefixer'),
    rename       = require('gulp-rename'),
    minifyCSS    = require('gulp-minify-css'),
    less         = require('gulp-less'),
    uncss        = require('gulp-uncss'),
    uglify       = require('gulp-uglify'),
    minifyHTML   = require('gulp-minify-html'),
    imagemin     = require('gulp-imagemin'),
    clean        = require('gulp-clean');

//bower-wtrdep подключаем библиотеки
gulp.task('bower', function () {
  gulp.src('./app/index.html')
    .pipe(wiredep({
      directory : "app/components"
    }))
    .pipe(gulp.dest('app'));
});

gulp.task('browser-sync', ['less', 'less_bootstrap'], function() {
    browserSync.init({
            proxy: "test-1.local",
            notify: false
    });
});

//less компилируем
gulp.task('less', function () {
  gulp.src('less/main.less')
    .pipe(less())
    .pipe(autoprefixer({browsers: ['last 15 versions'], cascade: false}))
    .pipe(gulp.dest('app/css/'))
    .pipe(browserSync.stream())
});
 
//clean
gulp.task('clean', function () {
    return gulp.src('dist', {read: false})
        .pipe(clean());
});

//uncss - убираем все что не используеться
gulp.task('un_css', function() {
    return gulp.src('app/css/style.css')
        .pipe(uncss({
            html: ['app/index.html']
        }))
        .pipe(gulp.dest('app/css'));
});

//uncss bootstrap - убираем все что не используеться
gulp.task('un_css_bootstrap', function() {
    return gulp.src('app/css/bootstrap.css')
        .pipe(uncss({
            html: ['app/index.html']
        }))
        .pipe(gulp.dest('app/css'));
});

//less_bootstrap компилируем
gulp.task('less_bootstrap', function () {
  gulp.src('app/components/bootstrap/less/bootstrap.less')
    .pipe(less())
    .pipe(gulp.dest('app/css/'))
});

// wath
gulp.task('watch', function () {
    gulp.watch('less/*.less', ['less']);
    gulp.watch('app/js/*.js').on("change", browserSync.reload);
    gulp.watch('app/*.html').on('change', browserSync.reload);
    gulp.watch('app/**/*.php').on('change', browserSync.reload);
});

// image compress
gulp.task('compress', function() {
  gulp.src('app/img/**/*')
  .pipe(imagemin())
  .pipe(gulp.dest('dist/img'))
});

//build
gulp.task('build', ['clean', 'un_css', 'compress'], function () {
    var assets = useref.assets(),
        opts = {
            conditionals: true,
            spare:true
        };

    return gulp.src('app/*.html')
        .pipe(assets)
        .pipe(gulpif('*.js', uglify()))
        .pipe(gulpif('*.css', minifyCSS()))
        .pipe(assets.restore())
        .pipe(useref())
        .pipe(gulpif('*.html', minifyHTML(opts)))
        .pipe(gulp.dest('dist'));
});

// dev task
gulp.task('dev', ['browser-sync', 'watch']);