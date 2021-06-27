// our images settings
var settings 			= require("../settings/images");

// build dependencies
var gulp 				= require("gulp");
var sequence 			= require("gulp-sequence").use(gulp);
var watcher				= require("gulp-watch");
var browsersync			= require("browser-sync").get(require("../settings/browsersync").name);
var imagemin			= require("gulp-imagemin");


// ============================================================================================================
// =============== SINGLE IMAGES ==============================================================================
// ============================================================================================================
// This tasks process all of our theme-specific single image files (svg, jpg, jpeg, png).

// Note: When using svgs, you need to include the width and height attributes for them to function properly
// in Internet Explorer 9,10,11. Be aware, for some reason, Adobe Illustrator CC does not include these attributes
//  on export, so you may need to manually enter them. (Not tested in CC 2015+ with new export features)
gulp.task("images:singles", function(){
	return gulp.src(settings.singles.src)
		.pipe(imagemin({
			svgoPlugins: [{ removeViewBox: false }]
		}))
		.pipe(gulp.dest(settings.singles.build))
		.pipe(imagemin({
			progressive: true,
			optimizationLevel: 3
		}))
		.pipe(gulp.dest(settings.singles.build));
});

// ============================================================================================================
// =============== SPRITESHEETS ===============================================================================
// ============================================================================================================
// TODO: Possible implement svg-spritesheets? Not entirely sure it'd ever be useful in a theme, but you
// never know....maybe?

// ============================================================================================================
// =============== BUILD : COMBINED TASKS =====================================================================
// ============================================================================================================
// This task runs the above tasks in parallel.
gulp.task("build:images", sequence(["images:singles"]));

// ============================================================================================================
// =============== WATCH ======================================================================================
// ============================================================================================================
// This task watches for changes and triggers the individual/corresponding tasks.
gulp.task("watch:images", function(){

	// Watches all single image files.
	watcher(settings.singles.src, function(events, done){
		sequence("images:singles", browsersync.reload)(done);
	});

});