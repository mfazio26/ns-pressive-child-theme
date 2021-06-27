// our wp-config settings
var settings 			= require("../settings/wpconfig");

// build dependencies
var gulp 				= require("gulp");
var sequence 			= require("gulp-sequence").use(gulp);
var watcher				= require("gulp-watch");
var browsersync			= require("browser-sync").get(require("../settings/browsersync").name);


// ============================================================================================================
// =============== WP-CONFIG ==================================================================================
// ============================================================================================================
// This taask simply moves all of our wp-config files into the root child-theme directory.
gulp.task("build:wpconfig", function(){
	return gulp.src(settings.src)
		.pipe(gulp.dest(settings.build));
});

// ============================================================================================================
// =============== WATCH ======================================================================================
// ============================================================================================================
// This task watches for changes and triggers the individual/corresponding tasks.
gulp.task("watch:wpconfig", function(){

	// Watches and builds all wp-config files
	watcher(settings.src, function(events, done){
		sequence("build:wpconfig", browsersync.reload)(done);
	});

});
