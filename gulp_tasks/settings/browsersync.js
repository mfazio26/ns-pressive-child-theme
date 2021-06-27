var project 			= require("./project");
var dir 				= project.dir;

// ============================================================================================================
// =============== BROWSER-SYNC SETTINGS ======================================================================
// ============================================================================================================
module.exports = {
	// id to be used to identify the instance
	"name" : project.name,

	// ref: https://www.browsersync.io/docs/options/
	"settings" : {
		// "server" : {
		// 	"baseDir" : [
		// 		dir.deploy()
		// 	],
		// 	"index": "templates/index.html"
		// }
		"proxy": project.dev.domain
	}
};