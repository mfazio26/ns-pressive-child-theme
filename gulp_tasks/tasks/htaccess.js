// our htaccess settings
var settings 			= require("../settings/htaccess");

// build dependencies
var gulp 				= require("gulp");
var rename				= require("gulp-rename");
var sequence 			= require("gulp-sequence").use(gulp);
var watcher				= require("gulp-watch");
var browsersync			= require("browser-sync").get(require("../settings/browsersync").name);


// ============================================================================================================
// =============== WP-CONFIG ==================================================================================
// ============================================================================================================
// This task simply renames and moves all of our htaccess files into the root child-theme directory.
gulp.task("build:htaccess", function(){
	return gulp.src(settings.src)
		.pipe(rename({ "prefix": ".", "extname": "" }))
		.pipe(gulp.dest(settings.build));
});

// ============================================================================================================
// =============== WATCH ======================================================================================
// ============================================================================================================
// This task watches for changes and triggers the individual/corresponding tasks.
gulp.task("watch:htaccess", function(){

	// Watches and builds all htaccess files
	watcher(settings.src, function(events, done){
		sequence("build:htaccess", browsersync.reload)(done);
	});

});
