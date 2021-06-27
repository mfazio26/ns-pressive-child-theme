<?php
/**
 * header-title.php
 * Derived from themes/pressive/partials/header-title.php
 *
 * This renders the header-title of almost every file – archives, posts, authors, search, etc. We have 
 * some specific formats we'd like to follow based on the type, so we're overriding the parent theme's 
 * output only if we need a custom title. This may seem messy, but its much better than opening and 
 * closing a bunch of php tags in the actual output. 
 *
 * If the page/post type doesn't require us to override the title/description – this file simply acts 
 * as a proxy and loads the parent theme's `header-title.php` file anyways.
 */

/**
 * This class is only intended to encapsulate our helper functions in this file only. All methods are 
 * and intended to be used in as `static` class methods.
 *
 * Example: NSHeadTitle::get_meta();
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class NSHeaderTitle {

	 /**
     * Gets if the current page is a single Post. If an `id` is provided, returns if the 
     * post id is a valid Post entry.
     *
     * @since    1.0.0
     * @static 
     * @access   public
     * @return   Array                        	An Array with a "title" and optional "description" keys. `null` if 
     *                                          we don't need to override the title.
     */
	public static function get_meta(){

		// post archive
		if (is_home()){
			return array(
				"title" => get_queried_object()->post_title
			);
		}

		// poster archive
		if (is_post_type_archive("poster")){
			return array(
				"title" => __(get_post_type_object("poster")->label, "notsalmon")
			);
		}

		// book archive
		if (is_post_type_archive("book")){
			return array(
				"title" => __(get_post_type_object("book")->label, "notsalmon")
			);
		}

		// mil courses archive
		if (is_post_type_archive("mil_courses")){
			return array(
				"title" => __(get_post_type_object("mil_courses")->label, "notsalmon")
			);
		}

		// is a post category (please note, posters "tags" are actually a "category")
		if (is_category() || is_tax()){
			$title = __(single_cat_title("", false), "notsalmon");
			if (is_tax("poster_tag")) $title = "" . ucwords($title) . " Quotes";

			return array(
				"title" => $title
			);
		}

		// a tag
		if (is_tag()){
			return array(
				"title" => __("Tagged with " . single_tag_title("", false) . "", "notsalmon")
			);
		}

		// search page
		if (is_search()){
			global $wp_query;
			return array(
				"title" => __("Search Results: " . get_search_query() . "", "notsalmon"),
				"description" => (isset($wp_query)) ? __($wp_query->found_posts . " results found.", "notsalmon") : null
			);
		}

		// author page
		if (is_author()){
			return array(
				"title" => __("All Posts by ", "notsalmon") . get_the_author()
			);
		}

		// post archive
		if (is_archive()){
			$title = null;

			// Date-specific Archive
			if (is_day()) $title = get_the_date();
			// Month-specific Archive
			elseif (is_month()) $title = get_the_date(_x("F Y", "monthly archives date format", "notsamon"));
			// Year-specific Archive
			elseif (is_year()) $title = get_the_date(_x("Y", "yearly archives date format", "notsamon"));
			// Normal ol' Archive
			else $title = __("Archives", "notsalmon");

			return array(
				"title" => $title
			);
		}

		// didn't find anything to override!!!
		return null;
	}

	 /**
     * Checks if we have the proper meta data to require us to override the title/description. At a minimum, 
     * the `$meta` array must be non-null and have a `title` key that is also non-null.
     *
     * @since    1.0.0
     * @static 
     * @access   public
     * @param    Array                        	An Array with a "title" and optional "description" keys.
     * @return   Boolean						True if it has the minimum requirements to override. False if not.
     *                                          
     */
	public static function get_overrideable($meta){
		return (($meta != null) && (array_key_exists("title", $meta)) && (isset($meta["title"]))) ? true : false;
	}
}


// generate the meta (if applicable)
$_ns_header_meta = NSHeaderTitle::get_meta();
// does the meta require us to override the header???
$_ns_header_overrideable = NSHeaderTitle::get_overrideable($_ns_header_meta);

// if we aren't overriding, just load the parent theme's `header-title.php` file.
if (!$_ns_header_overrideable){
	include get_template_directory() . "/partials/header-title.php";
}
// if we are overriding, go ahead and render it!!!
else {
	// ======= BEGIN OUTPUT ================================================
	?>

		<h1 class="entry-title"><?php echo $_ns_header_meta["title"]; ?></h1>
		<?php if (array_key_exists("description", $_ns_header_meta) && isset($_ns_header_meta["description"])): ?>
			<p><?php echo $_ns_header_meta["description"]; ?>
		<?php endif; ?>

	<?php 
	// ======= END OUTPUT ==================================================
}