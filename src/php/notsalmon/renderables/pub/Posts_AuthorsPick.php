<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;

/**
 * Renderable Template:
 * Renders the Author's Pick box which includes a title ("Karen’s Pick") and an article 
 * title that links to a given article. Please note, this shortcode is intended to be 
 * used automatically inside Article posts by using - `[ns_authorspick]`. This will 
 * automatically use the Advanced Custom Fields inside an article to render the pick. 
 * You can also use this shortcode on any other page, but you will need to specify the 
 * article id manually as a shortcode attribute.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Posts_AuthorsPick extends Renderable {

	/**
     * Overriden method
     * Renders the Author's Pick box which includes a title ("Karen’s Pick") and an 
     * article title that links to a given article.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){

		// capture attributes if they exist, otherwise provide defaults.
		$title = (array_key_exists("title", $context) && $context["title"]) ? $context["title"] : "Karen’s Pick";
		$post_id = (array_key_exists("id", $context) && $context["id"]) ? $context["id"] : -1;
		$show_image = (array_key_exists("show_image", $context)) ? (bool) $context["show_image"] : true;

		// If the post id is undefined or is not an "article", don't render anything!
		if (!PostTypeUtil::is_post_single($id=$post_id)) return "";


		// get the specified post url and title. at this point, we know these have to exist.
		$post_url = get_the_permalink($post_id);
		$post_title = get_the_title($post_id);
		
		// get the post's featured image. note, this may not exist.
		$image_id = get_post_thumbnail_id($post_id);
		$image_url = null;
		$image_alt = null;

		// if the featured image exists, get the url and alt text. note, alt text may not exist.
		if ($image_id) {
			$image_url = get_the_post_thumbnail_url($post_id, $size="ns-category-image");
			$image_alt = get_post_meta($image_id, "_wp_attachment_image_alt", true);
		}
		// if the featured image doesn't exist, we shouldn't attempt to show an image at all!
		else {
			$show_image = false;
		}
		
		// If post id is defined, render it!
		if ($post_id) {
			// ======= BEGIN OUTPUT ====================================================
			?>
				
				<aside class="ns-authorspick">
					<a class="ns-authorspick__btn" href="<?php echo $post_url; ?>">
						<?php if ($show_image): ?>
							<figure class="ns-authorspick__figure">
								<img class="ns-authorspick__img" src="<?php echo $image_url ?>" <?php echo ($image_alt) ? ("alt=\"" . $image_alt . "\"") : ""; ?>/>
							</figure>
						<?php endif; ?>
						<div class="ns-authorspick__content">
							<h3 class="ns-authorspick__heading"><?php echo $title; ?></h3>
							<p class="ns-authorspick__article"><?php echo $post_title; ?></p>
						</div>
					</a>
				</aside>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
