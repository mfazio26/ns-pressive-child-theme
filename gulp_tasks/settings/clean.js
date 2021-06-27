var dir = require("./project").dir;

// ============================================================================================================
// =============== CLEAN SETTINGS =============================================================================
// ============================================================================================================
module.exports = {

	// Important: An empty results in nothing happening. The reason for this is – 
	// the del() module won't allow you to delete files and directories above or outside 
	// the gulp project root folder...which is very good thing. Unfortunately, our project
	// requires us to put our built files (which would usually be deleted upon 
	// build task) one level above our gulp project directory. It is too unsafe to
	// circumvent this safeguard as a tiny typo could erase your hard-drive. Not fun.
	//
	// The reason this settings still exists is, if I can find a suitable and 100%
	// safe way of deleting/clean our built files on build tasks, then I will re-introduce
	// the feature...requiring this setting to be here.
	// 
	// What do we want to delete before our build process tarts?
	"src": ""
		
};