<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;

/**
 * Renderable Template:
 * Renders a single Video Course callout with a title, featured image (in book cover 
 * format), and course title label. This is intended to be used inside a "post" to 
 * callout a "featured" or "related" video course that navigates straight to that 
 * video course's sales pages. Please note, this uses the actual Video Course WordPress 
 * "Sales Page" hierarchy and does NOT use the legacy MastersInLife Custom Post Type.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Courses_SingleInlineCTA extends Renderable {

	/**
     * Overriden method
     * Renders a single Video Course callout with a title, featured image (in book cover 
     * format), and course title label. This is intended to be used inside a "post" to 
     * callout a "featured" or "related" video course that navigates straight to that 
     * video course's sales pages. Please note, this uses the actual Video Course WordPress 
     * "Sales Page" hierarchy and does NOT use the legacy MastersInLife Custom Post Type.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		
		// Capture attributes if they exist, otherwise provide defaults.
		
		// Callout heading
		$title = (array_key_exists("title", $context) && $context["title"]) ? $context["title"] : "Related Video Course";
		// Video course id
		$post_id = (array_key_exists("id", $context) && $context["id"]) ? $context["id"] : -1;
		// Alignment - can be ["left", "right", "center"]. If not specified or invalid, defaults to "center".
		$align = (array_key_exists("align", $context) && $context["align"]) ? strtolower($context["align"]) : null;
		$align = (in_array($align, ["left", "right", "center"])) ? $align : "center";
		
		// If the video course id is undefined or is not a "vide course", don't render anything!
		if (!PostTypeUtil::is_video_course_page($id=$post_id)) return "";

		// Get the specified video course url and title. At this point, we know these have to exist.
		$course_url = get_the_permalink($post_id);
		$course_title = get_the_title($post_id);
		$course_image = get_the_post_thumbnail($post_id, "ns-book-related", array("class" => "ns-videocourse-single-inline-cta__image"));

		// If video course id is defined, render it!
		// Basically, if we made it this far, we're fine to render.
		if ($post_id) {
			// ======= BEGIN OUTPUT ====================================================
			?>
				<aside class="ns-videocourse-single-inline-cta ns-videocourse-single-inline-cta--<?php echo $align; ?>">
					<h2 class="ns-videocourse-single-inline-cta__heading"><?php echo $title; ?></h2>
					<a href="<?php echo $course_url; ?>" class="ns-videocourse-single-inline-cta__btn" aria-label="Navigate to <?php echo $course_title; ?> page.">
						<figure class="ns-videocourse-single-inline-cta__media">
							<?php echo $course_image; ?>
						</figure>
						<span class="ns-videocourse-single-inline-cta__caption"><?php echo $course_title; ?></span>
					</a>
				</aside>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
