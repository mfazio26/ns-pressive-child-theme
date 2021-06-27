var dir = require("./project").dir;

// ============================================================================================================
// =============== IMAGES SETTINGS ============================================================================
// ============================================================================================================
module.exports = {

	// Note: When using svgs, you need to include the width and height attributes for them to function properly
	// in Internet Explorer 9,10,11. Be aware, for some reason, Adobe Illustrator CC does not include these attributes
	//  on export, so you may need to manually enter them. (Not tested in CC 2015+ with new export features)
	"singles": {
		"src": [dir.src("media/**/*.{svg,jpg,jpeg,gif,png}")],
		"build": dir.theme()
	}
	
};