// This module is intended to store values and functions to be used anywhere in our gulp task "ecosystem".
// If you find yourself repeating yourself in gulp tasks or settings, in need of some global utility methods,
// or wish you had one central place for reusable business logic – this is where it should go.
// 
// ============================================================================================================
// =============== SETTINGS ===================================================================================
// ============================================================================================================
// Private: By including settings outside of our exported objects, they remain private and are directly 
// accessible to any object in this module.
var _settings = {
	"name": "notsalmon",
	"version": "1.0.0",
	
	"directory": {
		"src": "./src/",
		"build": {
			"base": "../public_html/",
			"theme": "../public_html/wp-content/themes/pressive-child/",
			"docs": "../public_html/wp-content/themes/pressive-child/docs/",
		},
		"bower": "./bower_components/"
	},

	"dev": {
		"domain": "http://notsalmon.dev"
	}
};

// ============================================================================================================
// =============== PROJECT ====================================================================================
// ============================================================================================================
// Public: This is our main singleton instance that gets exported. Feel free to include any project specific
// utilities, settings, variables etc here for easy access. Its best if you ecapsulate your utilities, but if 
// it is truly at the project-level, feel free to include methods directly on the prototype.
var Project = function(){
	this.name = _settings.name;
	this.dev = _settings.dev;
	
	this.dir = new Directory();
};

Project.prototype = {
	// no class-level methods yet!
};

// ============================================================================================================
// =============== DIRECTORY ==================================================================================
// ============================================================================================================
// Private: project.directory
// The intention here is to provide a single place that can prepend source and build paths to settings used
// throughout our gulp tasks and settings instead of needing to edit every single task. This will make it a 
// little easier in the future to move things around (should it become necessary) without completely breaking
// the entire build system.

var Directory = function(){
	// no instance-level variables yet!
};

Directory.prototype = {
	// @param   string   path     a file path relative to our src directory.
	// @return  string            a complete source file path relative to our main gulp directory.           
	src: function(path){
		return _settings.directory.src + (path || "");
	},

	// @param   string   path     a file path relative to our src directory.
	// @return  string            a complete root file path relative to our main gulp directory.
	base: function(path){
		return _settings.directory.build.base + (path || "");
	},

	// @param   string   path     a file path relative to our src directory.
	// @return  string            a complete theme file path relative to our main gulp directory.
	theme: function(path){
		return _settings.directory.build.theme + (path || "");
	},

	// @param   string   path     a file path relative to our src directory.
	// @return  string            a complete docs build file path relative to our main gulp directory.
	docs: function(path){
		return _settings.directory.build.docs + (path || "");
	},

	// @param   string   path     a file path relative to our bower directory.
	// @return  string            a complete bower file path relative to our main gulp directory.
	bower: function(path){
		return _settings.directory.bower + (path || "");
	}
};

// singleton
module.exports = new Project();

