// our templates settings
var settings 			= require("../settings/php");

// build dependencies
var gulp 				= require("gulp");
var sequence 			= require("gulp-sequence").use(gulp);
var watcher				= require("gulp-watch");
var browsersync			= require("browser-sync").get(require("../settings/browsersync").name);


// ============================================================================================================
// =============== PHP ========================================================================================
// ============================================================================================================
// This taask simply moves all of our php files into the root child-theme directory.
gulp.task("build:php", function(){
	return gulp.src(settings.src)
		.pipe(gulp.dest(settings.build));
});

// ============================================================================================================
// =============== WATCH ======================================================================================
// ============================================================================================================
// This task watches for changes and triggers the individual/corresponding tasks.
gulp.task("watch:php", function(){

	// Watches and builds all html files, templates, and partials.
	watcher(settings.src, function(events, done){
		sequence("build:php", browsersync.reload)(done);
	});

});
