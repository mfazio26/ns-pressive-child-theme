var dir 			= require("./project").dir;

// ============================================================================================================
// =============== SCSS SETTINGS ==============================================================================
// ============================================================================================================

module.exports = {

	// common settings for both stylesheets
	"common": {
		"includes": [dir.bower("foundation-sites/scss")],
		"watch": [dir.src("scss/**/*.scss")],
		"build": dir.theme(),
		"autoprefixer": ["last 2 versions", "ios >= 8", "ie >= 9", "and_chr >= 2.3"]
	},

	// our main styles.css file
	"main": {
		"src": [dir.src("scss/main.scss")],
		"filename": "style",

	},

	// our admin editor.style.css file
	"editor": {
		"src": [dir.src("scss/editor.scss")],
		"filename": "editor-style"
	},

	// These settings configure our SassDoc v2 documentation. For more information on the available 
	// options, please visit...
	// http://sassdoc.com/getting-started/
	"docs": {
		"src": [dir.src("scss/**/*")],
		"sassdoc": {
			"dest": dir.docs("scss/"),
			"display": {
				"access": ["public", "private"],
				"alias": true
			},
			// "exclude": [],
			// package: "./package.json",
			// "theme": "default",
			// "autofill": [],
			// "groups": {
			// 		"undefined": "General"
			// },
			// "no-update-notifier": false,
			// "verbose": false,
			// "strict" false
		}
	}
};