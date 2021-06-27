var dir = require("./project.js").dir;

// ============================================================================================================
// =============== VENDOR ASSETS SETTINGS =====================================================================
// ============================================================================================================
module.exports = {

	// Vendor Assets are additional resource such as images, 
	// spritesheets, fonts, etc that simply need to be moved
	// into our built /assets directories.
	// 
	// In most scenarios, vendor resources such as css, scss,
	// and js will be handed by other gulp tasks. But, in the
	// event those aren't suitable or appropriate solutions,
	// you can consider this a "catch-all". For this reason,
	// the below settings are different than other tasks. These
	// settings are separated by package/component and can 
	// have different destinations directories if you want.
	// 
	// Keep in mind, you may need to change a where a package
	// is looking for these resources. For example, if using
	// font-awesome's sass, you'll need to change where it is
	// looking for its fonts.
	// 
	// NOTE: Because of the way the source are combined and 
	// "watched" in one big set of globs (even though there 
	// really isn't a need to be watched at all), you should
	// take special care to not write contradictory src values
	// from package-to-package. For example, *.svg in one 
	// package and !*.svg in another.
	"packages": [
		{
			// font-awesome fonts
			"src": [dir.bower("font-awesome/fonts/**/*")],
			"build": dir.theme("fonts/font-awesome/")
		}
	]
};