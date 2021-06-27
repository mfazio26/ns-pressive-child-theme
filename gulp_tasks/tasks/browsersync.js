// our browsersync settings
var settings 			= require("../settings/browsersync");

// build dependencies
var gulp 				= require("gulp");
var browsersync			= require("browser-sync").get(settings.name);

// ============================================================================================================
// =============== BROWSER-SYNC TASK ==========================================================================
// ============================================================================================================
// This tasks starts up a local server and listens for live-reloads.
gulp.task("browsersync", function(){
	browsersync.init(settings.settings);
});