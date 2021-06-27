// our scss settings
var settings 			= require("../settings/scss");

// build dependencies
var gulp				= require("gulp");
var scss				= require("gulp-sass");
var sassdoc 			= require("sassdoc");
var rename				= require("gulp-rename");
var sourcemaps 			= require("gulp-sourcemaps");
var autoprefixer 		= require("gulp-autoprefixer");
var cssnano 			= require("gulp-cssnano");
var gzip 				= require("gulp-gzip");
var merge 				= require("merge-stream");
var clone 				= require("gulp-clone");
var sequence 			= require("gulp-sequence").use(gulp);
var watcher				= require("gulp-watch");
var browsersync			= require("browser-sync").get(require("../settings/browsersync").name);

// ============================================================================================================
// =============== PRIVATE : ABSTRACT COMPILE =================================================================
// ============================================================================================================
// This function is used by both "build" tasks below. The intention is – we have two different files we want 
// to create, but with almost identical settings. This allows us to write the logic once and call reuse it.
// 
// Note: The first time we call sourcemaps.write(".") we need to include the {sourceRoot: null} option. This 
// fixes a nasty bug where cssnano will fail and/or scss includePaths will fail to make it into the minified
// sourcemaps. (see https://github.com/scniro/gulp-clean-css/issues/1)
function _compile(target){
	var source = gulp.src(target.src)
		.pipe(rename({ basename: target.filename }))
		.pipe(sourcemaps.init())
		.pipe(scss({ includePaths: settings.common.includes }))
		.on("error", function(err){
			console.log(err);
			this.emit("end");
		})
		.pipe(autoprefixer({ browsers: settings.common.autoprefixer }));

	// create development version w/ sourcemaps
	var dev = source.pipe(clone())
		.pipe(sourcemaps.write(".", {sourceRoot: null} )) 
		.pipe(gulp.dest(settings.common.build))
		.pipe(browsersync.stream({ match: "**/*.css" }));

	// create production/minified version w/ sourcemaps
	var prod = source.pipe(clone())
		.pipe(rename( { suffix: ".min" } ))
		.pipe(cssnano())
		.pipe(sourcemaps.write("."))
		.pipe(gulp.dest(settings.common.build))
		.pipe(browsersync.stream({ match: "**/*.css" }));

	// create a reference gzipped version without sourcemaps
	var ref = source.pipe(clone())
		.pipe(rename( { suffix: ".min" } ))
		.pipe(cssnano())
		.pipe(gzip())
		.pipe(gulp.dest(settings.common.build));

	return merge(dev, prod, ref);		
}
// ============================================================================================================
// =============== TASKS ======================================================================================
// ============================================================================================================
// Compiles SCSS into three different versions: development, production, and minified gzipped reference.

// Creates our main styles.css file.
gulp.task("build:scss:main", function(){
	return _compile(settings.main);
});

// Creates our admin editor-styles.scss file.
gulp.task("build:scss:editor", function(){
	return _compile(settings.editor);
});

// builds the sass documentation
gulp.task("build:scss:docs", function(){
	var stream = sassdoc(settings.docs.sassdoc);

	gulp.src(settings.docs.src)
		.pipe(stream)
		.on("end", function(){
			console.log("End of SassDoc parsing phase.");
		});

	return stream.promise.then(function(){
		console.log("End of SassDoc documentation phase.");
	});
});

// ============================================================================================================
// =============== BUILD : COMBINED TASKS =====================================================================
// ============================================================================================================
// This task runs the above tasks in parallel.
gulp.task("build:scss", function(done){
	sequence(["build:scss:main", "build:scss:editor", "build:scss:docs"])(done);
});

// ============================================================================================================
// =============== WATCH ======================================================================================
// ============================================================================================================
// This task watches for changes and triggers the individual/corresponding tasks.
gulp.task("watch:scss", function(){

	// Watches for any *.scss changes and recompiles.
	watcher(settings.common.watch, function(events, done){
		sequence("build:scss")(done);
	});

});

