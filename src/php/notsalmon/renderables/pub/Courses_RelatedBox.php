<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\TeachableUtil;

/**
 * Renderable Template:
 * Renders the current Legacy Course's related MIL Courses and Books. Please note, 
 * related MIL Courses point to MastersInLife.com course and not our internal legacy courses.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Courses_RelatedBox extends Renderable {

	/**
     * Overriden method
     * Renders a Legacy Courses's related books and courses box.
     *
     * Please note, WP has a bug where it is adding extra `</p>` tags at the end of 
     * `<img>` tags and wrapping all `<a>` tags. To work around this behavior/bug, we 
     * are adding extra wrapping/TOUCHING `<div>`s and `<p>` tags ourselves. For some 
     * reason WP leaves theses alone....so, we'd rather have SOME control over the 
     * ouput than none at all. For this reason – our output markup is a little ugly and 
     * more complicated than we'd normally need it to be. Err.
     *
     * Please note, this renders identically to `Books_RelatedBox`. The only reason 
     * why they are separate is there may be a need in the future to have them render 
     * differently – and its better to be a little more verbose now, then to have to 
     * rip it apart later.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		$title = (array_key_exists("title", $context) && $context["title"]) ? $context["title"] : null;
		$related = get_field("courses_related");
		$mil_url = TeachableUtil::get_url("", TeachableUtil::get_tracking_query());
		$mil_name = TeachableUtil::get_display_name();
		$mil_show_icon_button = false;

		if ($related){
			// ======= BEGIN OUTPUT ====================================================	
			?>
				
				<aside class="ns-related-covers">
					<?php if ($title): ?>
						<h2 class="ns-related-covers__heading"><?php echo $title; ?></h2>
					<?php endif; ?>
					<div class="ns-related-covers__listing">
						<?php foreach($related as $id): ?>
							<?php
								// get per-post properties
								$post_permalink = get_the_permalink($id);
								$post_type = get_post_type($id);
								$post_title = get_the_title($id);
								$post_image = (has_post_thumbnail($id)) ? get_the_post_thumbnail($id, "ns-book-related", array()) : null;
							?>
							<article class="ns-related-covers-item ns-related-covers-item--<?php echo $post_type; ?>">
								<?php if ($post_image): ?>
									<div class="ns-related-covers-item__figure">
										<div class="ns-related-covers-item__figure-inner">
											<div class="ns-related-covers-item__image"><?php echo $post_image; ?></div>
											<?php if ($post_type == "mil_courses" && $mil_show_icon_button): ?>
												<div class="ns-related-covers-item__mil"><a href="<?php echo $mil_url; ?>"><span><?php echo "Visit " . $mil_name; ?></span></a></div>
											<?php endif; ?>
										</div>
									</div>
								<?php endif; ?>
								<p class="ns-related-covers-item__title"><a href="<?php echo $post_permalink; ?>"><?php echo $post_title; ?></a></p>
								<p class="ns-related-covers-item__cta" ><a href="<?php echo $post_permalink; ?>"><span>Learn More</span></a></p>
							</article>
						<?php endforeach; ?>
					</div>
				</aside>
				
			<?php 
			// ======= BEGIN OUTPUT ====================================================
		}
	}
}
