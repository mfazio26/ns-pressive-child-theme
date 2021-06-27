<?php 

/**
 * content-grid.php
 * Derived from themes/pressive/partials/content-grid.php
 *
 * This file serves a very special purpose. Thrive Themes does not support Focus Areas "between" posts 
 * on archive pages when the layout is set to anything other than full-width. It also doesn't support 
 * those Focus Areas on any archive page other than the `is_home()`. There also isn't a hook we can 
 * use to tap into when the archive list renders or count which post is currently being rendered...so, 
 * we are bypassing that limitation here.
 *
 * The way we do this is – we are proxying the `content-grid.php` template and using that to "count" 
 * which index is currently being rendered. Then, we have created a duplicate `check()` method of our 
 * own (minus the Thrive limitations), and finally calling the `thrive_render_top_focus_area()` to 
 * actually do our rendering. Hopefully, if Thrive ever adds support, we should only have to delete 
 * this template.
 */

use notsalmon\utils\ThriveThemeUtil;
use notsalmon\utils\PostTypeUtil;

// thrive global options
global $options;

// notsalmon global options
// if ["archive"] doesn't exist, it means this is out first post in the loop!
global $ns_options;
if (!isset($ns_options["archive"])) $ns_options["archive"] = array("index" => 0);

//proxy: load the parent theme's partial file (we aren't actually changing anything!)
include get_template_directory() . "/partials/content-grid.php";

// what types of archives should this be allowed on...or not be allowed on?
if (!PostTypeUtil::is_book_archive() && !PostTypeUtil::is_legacy_course_archive()){

	// if this is set to `grid_sidebar`, we are going to check if there are any "between focus areas" 
	if ($options["blog_layout"] == "grid_sidebar"){
		$ns_cur_index = $ns_options["archive"]["index"];

		if (ThriveThemeUtil::check_blog_between_focus_area($ns_options["archive"]["index"] + 1)){
			thrive_render_top_focus_area( "between_posts", $ns_options["archive"]["index"] + 1 );
		}

		// increment the index
		$ns_options["archive"]["index"]++;
	}
}