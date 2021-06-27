// our clean settings
var settings 			= require("../settings/clean");

// build dependencies
var gulp 				= require("gulp");
var del					= require("del");

// ============================================================================================================
// =============== CLEAN TASK =================================================================================
// ============================================================================================================
// This task is intended to delete the build directory and all built/compiled files. This is so we have a nice
// clean slate and no stale files each time we start up our gulp dev environment.
// 
// Important: Unfortunately, this task currently and intentionally does absolutely nothing (per our settings).
// The reason is – the del() module has a safeguard that will not allow you to delete files above your gulp 
// project directory. This is a very good thing. Again, unfortunately, our project requires us to put our built
// files (which would usually be deleted upon build tasks) one level above our gulp project directory so they
// git-deploy properly. It is too unsafe to circumvent such a safeguard (by using another module) because even
// a tiny typo could be catostrophic – potentially wiping out your hard-drive. Not fun.
// 
// So why is this task even here, then? Good question. If, and only if, I can find a suitable and 100% safe way
// of deleting/cleaning our built files on build tasks, then I will re-introduce the feature. When that happens,
// I should only have to update this task and its corresponding setting as it is already baked in to the master
// gulp tasks in the gulpfile.js
// 
// Possible workarounds???
gulp.task("clean", function(){
	return del(settings.src);
});