<?php

namespace notsalmon\controllers\pub;
use notsalmon\bases\Controller;
use notsalmon\utils\PostTypeUtil;
use notsalmon\utils\ThriveThemeUtil;
use notsalmon\renderables\pub\Courses_RelatedBox;

/**
 * This class defines functionality specific to and available on Legacy Course and Legacy 
 * Course Archives pages.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class CoursesController extends Controller {

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("tha_html_before", array($this, "action_single_remove_author_box"));
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		add_filter("the_content", array($this, "filter_single_content_after"), 0);
	}
	
	// ======= SINGLE : CONTENT ========================================================
	/**
     * Renders our related mil courses and books after the content.
     *
     * @since    1.0.0
     * @access   public
     * @param    String 			  $content   	The currently rendered content string.
     * @return 	 String 							The updated content string.
     */
	public function filter_single_content_after($content){
		if (PostTypeUtil::is_legacy_course_single()){
			// related books and courses
			$related_renderer = new Courses_RelatedBox();
			$related = $related_renderer->render_to_var(array("title" => "You may also like..."));
			return $content . $related;
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
		if (PostTypeUtil::is_legacy_course_single()){
			ThriveThemeUtil::set_option("bottom_about_author", false);
		}
	}
}
