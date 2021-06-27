<?php

namespace notsalmon\controllers\pub;
use notsalmon\bases\Controller;
use notsalmon\utils\PostTypeUtil;
use notsalmon\utils\ThriveThemeUtil;
// use notsalmon\renderables\pub\Posters_TagListBox;
// use notsalmon\renderables\pub\Posters_ArchiveIntro;


/**
 * This class defines functionality specific to and available on Single Video Courses
 * Pages and the parent Video Courses listing page.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class VideoCoursesController extends Controller {

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		// add_action("tha_html_before", array($this, "action_single_register_monarch_type"));
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		add_filter("the_content", array($this, "filter_replace_acf_placholders"), 9999);
	}

	// ======= PRIVATE HELPER METHODS ==================================================
	/**
	 * Single Video Course pages are "pages" not "posts" and are not implemented as 
	 * Custom Post Types. For this reason, we can't use the standard "get_post_type($id)"
	 * and "is_singlular('videocourse')" methods. Instead, we have a custom method in 
	 * PostTypeUtils to handle this. 
	 *
	 * Also - ACF recommends using the "get_field()" function to both retrieve the value 
	 * AND check if the field even exists. For this reason, we can avoid running the same 
	 * query again later by just returning the value here - if it exists. So, we kill two 
	 * birds with one stone.
	 *
	 * @since    1.0.0
     * @access   public
     * @return   Boolean|String                		The regular price (string) if it exists and is defined, or `false` if the field doesn't exist on the current page.
	 */
	private function _is_single_page_videocourse_and_get_regular_price(){
		if (PostTypeUtil::is_video_course_page()){
			return get_field("videocourse_regular_price");
		}
		return false;
	}

	// ======= SINGLE : REPLACE ACF PLACEHOLDERS =======================================
	/**
	 * A video course's regular price, checkout url, etc are stored as custom fields via 
	 * the Advanced Custom Fields Plugin. If the current page is a single video course 
	 * page, this filter will replace the "placeholders" in the content with their 
	 * ACF custom field equivalents. For example, you can automatically replace all 
	 * instances of "##REGULAR_PRICE##" with "12.57".
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_replace_acf_placholders($content){
		// Get the course's regular price. If this is not a video course page, this will 
		// be set to "false".
		$regular_price = $this->_is_single_page_videocourse_and_get_regular_price();

		// If this is a video course page and the price is defined, go ahead and replace 
		// the placeholders.
		if ($regular_price){
			$checkout_url = get_field("videocourse_checkout_url");

			$content = str_replace("##REGULAR_PRICE##", "$" . $regular_price, $content);
			$content = str_replace("##CHECKOUT_URL##", $checkout_url, $content);
		}
		return $content;
	}
}