// build dependencies
var gulp 			= require("gulp");
var sequence 		= require("gulp-sequence").use(gulp);
var browsersync		= require("browser-sync").create(require("./gulp_tasks/settings/browsersync").name);
var requiredir 		= require("require-dir")("./gulp_tasks/tasks");


// ============================================================================================================
// =============== TASK : BUILD ===============================================================================
// ============================================================================================================
// Use this to manually trigger a build.
// 
// NOTE: Clean is currently a placeholder until we can find a 100% safe solution to deleting files above the 
// Gulp project root directory.
gulp.task("build", sequence(
		"clean",
		[
			"build:php",
			"build:images",
			"build:fonts",
			"build:scss",
			"build:wpconfig",
			"build:htaccess",
			"build:vendorassets"
		]
	)
);

// ============================================================================================================
// =============== TASK : WATCH (DEV) =========================================================================
// ============================================================================================================
// This watches the source files for changes and triggers the corresponding tasks. For example, "watch:images" 
// will trigger image tasks inside "gulp_tasks/images.js"
// 
// NOTE: This task is exclusively used by the "serve" and "preview" tasks in this file. You really shouldn't 
// ever have a need to manually trigger this task on your own.
gulp.task("watch", sequence(
		[
			"watch:php",
			"watch:images",
			"watch:fonts",
			"watch:scss",
			"watch:wpconfig",
			"watch:htaccess",
			"watch:vendorassets"
		]
	)
);

// ============================================================================================================
// =============== TASK : SERVE (DEV) =========================================================================
// ============================================================================================================
// This starts with a fresh build, starts up a local server, and begins watching our source files for changes.
gulp.task("serve", sequence(
		"build",
		"browsersync",
		"watch"
	)
);