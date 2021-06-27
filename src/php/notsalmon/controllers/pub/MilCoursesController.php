<?php

namespace notsalmon\controllers\pub;
use notsalmon\bases\Controller;
use notsalmon\utils\TeachableUtil;
use notsalmon\utils\PostTypeUtil;
use notsalmon\utils\ThriveThemeUtil;

/**
 * This class defines functionality specific to and available on Single MastersInLife 
 * Courses and Archive/Category pages. Please note, Single Courses and Archive/Category 
 * pages are intended to redirectly immediately to their corresponding MastersInLife.com
 * urls. We DO NOT want to have to maintain actual MastersInLife content on this website.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class MilCoursesController extends Controller {

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("wp", array($this, "action_redirect"));
		add_action("tha_html_before", array($this, "action_single_show_featured_image"));
		add_action("tha_html_before", array($this, "action_single_remove_author_box"));
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		add_filter("the_content", array($this, "filter_single_content_add_external_links"), 0);
	}

	// ======= SINGLE : REDIRECT =======================================================
	/**
	 * Single MastersInLife Courses (and Categories/Archives?) are intended to immediately 
	 * redirect to their MastersInLife.com counterpart url.
	 *
	 * Updated 03/02/19: As MastersInLife is temporarily (permanently?) retired - we are 
	 * now supporting the use of internal redirects as well. Please note the inclusion of 
	 * a hard-coded default url just in case.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function action_redirect(){
		if (!is_admin()){
			$redirect_url = null;
			$default_redirect_url = "/courses/";

			// Is the current URL a single MIL Course?
			if (PostTypeUtil::is_mil_course_single()){
				// get the current post id so we can retrieve the ACF fields.
				global $post;
				$post_id = $post->ID;

				// is the field currently set to be an interal redirect? or an external redirect?
				$is_external = get_field("mil_course_redirect_type", $post_id);

				// get the proper field and assign the redirect url. please note, because ACF 
				// can't "require" conditional fields, we're going to provide a hard-coded 
				// default just in case.
				if ($is_external) {
					$external_url = get_field("mil_course_external_url", $post_id);
					$redirect_url = ($external_url) ? $external_url : $default_redirect_url;
				}
				else {
					$internal_obj = get_field("mil_course_internal_url", $post_id);
					$internal_url = ($internal_obj) ? get_the_permalink($internal_obj) : null;
					$redirect_url = ($internal_url) ? $internal_url : $default_redirect_url;
				}
			}

			// Is the current URL a MIL Category?
			if (PostTypeUtil::is_mil_course_archive()){
				// We have to use the queried object because the global "post id" will give us 
				// an incorrect result.
				$obj = get_queried_object();

				// is the field currently set to be an interal redirect? or an external redirect?
				$is_external = get_field("mil_category_redirect_type", $obj);

				// get the proper field and assign the redirect url. please note, because ACF 
				// can't "require" conditional fields, we're going to provide a hard-coded 
				// default just in case.
				if ($is_external) {
					$external_url = get_field("mil_category_external_url", $obj);
					$redirect_url = ($external_url) ? $external_url : $default_redirect_url;
				}
				else {
					$internal_obj = get_field("mil_category_internal_url", $obj);
					$internal_url = ($internal_obj) ? get_the_permalink($internal_obj) : null;
					$redirect_url = ($internal_url) ? $internal_url : $default_redirect_url;
				}
			}

			// if we found a valid redirect url, do it immediately!
			if ($redirect_url) wp_redirect($redirect_url, 301);
		}
	}

	// ======= SINGLE : FEATURED IMAGE =================================================
	/**
	 * Shows the course's featured image. We need to do this manually because the 
	 * display of featured images associated with "posts" is currently turned off – per 
	 * the design and convention of the site. We accomplish this by setting a Thrive 
	 * Theme Option at runtime.
	 *
	 * Please note, Single Courses are intended to redirect immediately to their 
	 * corresponding MastersInLife.com url – therefore, this is largely unnecessary 
	 * because it should never have a chance to be seen. However, we're including it 
	 * anyways for development purposes – so we can see any related queries/info.
	 *
	 * @since    1.0.0
     * @access   public
	 * @see themes/pressive/single.php on line 39-40.
	 */
	public function action_single_show_featured_image(){
		if (PostTypeUtil::is_mil_course_single()){
			ThriveThemeUtil::set_option("featured_image_style", "wide");
		}
	}

	// ======= SINGLE : CONTENT ========================================================
	/**
	 * Generates the corresponding MastersInLife.com links for the current course and 
	 * all its associated categories.
	 *
	 * Please note the filter's `priority`. We need to do this so our content appears 
	 * before any parent theme or plugin additions.
	 *
	 * Please note, Single Courses are intended to redirect immediately to their 
	 * corresponding MastersInLife.com url – therefore, this is largely unnecessary 
	 * because it should never have a chance to be seen. However, we're including it 
	 * anyways for development purposes – so we can see any related queries/info.
	 *
	 * @since    1.0.0
     * @access   public
	 * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_single_content_add_external_links($content){
		if (PostTypeUtil::is_mil_course_single()){
			if ($course_url = get_field("mil_course_external_url")){
				$categories = get_the_terms(get_the_ID(), "mil_category");
				$category_output = "";
				// generate the mil course link
				$course_output = "<p><a href=\"" . $course_url . "\" target=\"_self\">View this course on " . TeachableUtil::get_display_name() . "</a></p>";

				// generate a list of all related mil category links (if applicable)
				if ($categories && (count($categories) > 0)){
					$category_output .= "<p>View Categories on " . TeachableUtil::get_display_name() . ":<br>";
					foreach ($categories as $key => $category){
						if ($category_url = get_field("mil_category_external_url", $category)){
							$category_output .= "<a href=\"" . $category_url . "\" target=\"_self\">" . $category->name . "</a>, ";	
						}
					}
					$category_output = rtrim($category_output, ", ") . "</p>";
				}
				return $content . $course_output . $category_output;	
			}
		}
		return $content;
	}

	// ======= SINGLE : AUTHOR BOX =====================================================
	/**
     * Removes the Author Box at the bottom of the content. We accomplish this by 
     * setting a Thrive Theme Option at runtime.
     *
     * @since    1.0.0
     * @access   public
     * @see themes/pressive/single.php on line 173.
     */
	public function action_single_remove_author_box(){
		if (PostTypeUtil::is_mil_course_single()){
			ThriveThemeUtil::set_option("bottom_about_author", false);
		}
	}
}
