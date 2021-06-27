// our templates settings
var settings 			= require("../settings/fonts");

// build dependencies
var gulp 				= require("gulp");
var sequence 			= require("gulp-sequence").use(gulp);
var watcher				= require("gulp-watch");
var browsersync			= require("browser-sync").get(require("../settings/browsersync").name);


// ============================================================================================================
// =============== FONTS ======================================================================================
// ============================================================================================================
// This taask simply moves all of our font files into the root child-theme directory.
gulp.task("build:fonts", function(){
	return gulp.src(settings.src)
		.pipe(gulp.dest(settings.build));
});

// ============================================================================================================
// =============== WATCH ======================================================================================
// ============================================================================================================
// This task watches for changes and triggers the individual/corresponding tasks.
gulp.task("watch:fonts", function(){

	// Watches and builds all html files, templates, and partials.
	watcher(settings.src, function(events, done){
		sequence("build:fonts", browsersync.reload)(done);
	});

});
