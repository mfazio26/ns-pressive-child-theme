// our vendorassets settings
var settings 			= require("../settings/vendorassets.js");

// build dependencies
var gulp 				= require("gulp");
var sequence 			= require("gulp-sequence").use(gulp);
var watcher				= require("gulp-watch");
var browsersync			= require("browser-sync").get(require("../settings/browsersync.js").name);
var merge				= require("merge-stream");


// TODO: Possibly incorporate changed() so we don't have to copy every file every time a single file is
// updated...even though that should never really happen in the first place.

// TODO: Maybe each "package" should be broken out into its own watch task/routine so you can't override one
// source glob with another? (ie....*.svg then !*.svg in another)

// ============================================================================================================
// =============== VENDOR ASSETS ==============================================================================
// ============================================================================================================
// Vendor assets are additional resources such as images, spritesheets, fonts, etc that simply need to be moved
// into our build /assets directories for their js/css counterparts to work properly.
// 
// In most scenarios, vendor resources such as css, scss, and js will be handed by other gulp tasks. But, in 
// the event those aren't suitable or appropriate solutions, you can consider this a "catch-all". For this 
// reason, the task below (and corresponding settings) are structured differently than many other tasks. This
// task is separated by package/component and can have different destinations directories if you want. So, we
// need to map them all into separate sub-tasks and merge them so Gulp gets notified the task has completed.
// 
// Keep in mind, you may need to change a where a package is looking for these resources. For example, if using
// font-awesome's sass, you'll need to change where it is looking for its fonts.
gulp.task("build:vendorassets", function(){
	var tasks = settings.packages.map(function(obj){
		return gulp.src(obj.src)
			.pipe(gulp.dest(obj.build));
	});
	return merge(tasks);
});

// ============================================================================================================
// =============== WATCH ======================================================================================
// ============================================================================================================
// This task watches for changes and triggers the individual/corresponding tasks.
gulp.task("watch:vendorassets", function(){
	// grab all our sources and combine them into one big set of globs.
	// IMPORTANT: Because of the way we are combining globs, you should take extra care to not assign 
	// contradictory src values.
	var globs = [];
	for (var i=0; i<settings.packages.length; i++) {
		var package = settings.packages[i];
		if (package.src) globs = globs.concat(package.src);
	}

	// Watches are moves our vendor assets.
	// IMPORTANT: There really should ever be a need for this to fire, but if it does, keep in mind that it
	// is copying all the assets and not just the onces that have changed.
	watcher(globs, function(events, done){
		sequence("build:vendorassets", browsersync.reload)(done);
	});
});