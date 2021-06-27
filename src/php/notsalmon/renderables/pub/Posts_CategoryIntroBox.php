<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;

/**
 * Renderable Template:
 * Some of the Posts Categories have a nice little image and html formatted description. 
 * If either of those things exists on the current category, we're goign to add our 
 * Category Intro Box.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Posts_CategoryIntroBox extends Renderable {

	/**
     * Overriden method
     * Renders the Category Intro Box HTML component.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		$image = null;
		$description = get_the_archive_description();

		// Get the image id from ACFPro. If its defined, capture the image.
		if ($image_id = get_field("category_image_id", get_queried_object())){
			$image = wp_get_attachment_image($image_id, "ns-category-image", false, array(
					"class" => "category-intro__img"
				)
			);
		}

		// If either is defined, render it!
		if ($image || $description) {
			// ======= BEGIN OUTPUT ====================================================
			?>

				<aside class="ns-category-intro">
					<div class="ns-category-intro__container">
						<?php if ($image): ?>
							<figure class="ns-category-intro__figure">
								<?php echo $image; ?>
							</figure>
						<?php endif; ?>
						<?php if ($description): ?>
							<div class="ns-category-intro__content">
								<div class="ns-category-intro__description">
									<?php echo $description; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</aside>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
