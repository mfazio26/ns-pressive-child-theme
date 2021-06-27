<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;
use \WP_Query;

/**
 * Renderable Template:
 * 
 * Renders a simple 3-column Video Courses listing box. Currently, this is only used 
 * on the "/courses" page, and renders ALL the available courses. If, in the future, 
 * we want to use this any place else, we should consider making this shortcode more 
 * robust and customizable.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class VideoCourses_ListingBox extends Renderable {

	/**
     * Overriden method
     * Renders a simple 3-column Video Courses listing box. Currently, this is only 
     * used on the "/courses" page, and renders ALL the available courses. If, in the 
     * future, we want to use this any place else, we should consider making this 
     * shortcode more robust and customizable.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		// collect context settings
		$title = (array_key_exists("title", $context) && $context["title"]) ? $context["title"] : null;
		$cta = (array_key_exists("cta", $context) && $context["cta"]) ? $context["cta"] : "Get started now!";
		$article_htag = (array_key_exists("article_htag", $context) && $context["article_htag"]) ? $context["article_htag"] : "h2";
		$show_price = (array_key_exists("show_price", $context)) ? (bool) $context["show_price"] : true;

		// Because "Video Courses" are "pages" not a post or custom post type, we have to
		// query for them differently. Please refer to PostTypeUtil::get_video_courses_parent_page_id() 
		// method for more information.
		
		// query all child pages of the Video Courses parent page.
		$query = new WP_Query(array(
			"post_status"       => "publish",
	        "post_type"         => "page",
	        "posts_per_page"    => -1,
	        "post_parent"       => PostTypeUtil::get_video_courses_parent_page_id(),
	        "order"             => "ASC",
	        "orderby" 			=> "date"
		));

		// If we found any courses, go ahead and render it!
		if ($query->have_posts()){

			// ======= BEGIN OUTPUT ====================================================	
			?>

				<aside class="ns-videocourses-listing">
					<?php if ($title): ?>
						<h2 class="ns-videocourses-listing__heading"><?php echo $title; ?></h2>
					<?php endif; ?>
					<div class="ns-videocourses-listing__list">
						<?php while($query->have_posts()): $query->the_post(); ?>
							<?php
								$post_id = get_the_ID();

								// validate that this is an actual course - and not an offer/ancillary page.
								if (!PostTypeUtil::is_video_course_page($post_id)){
									continue;
								}
								// collect article attributes
								$title = get_the_title();
								$permalink = get_the_permalink();
								$thumbnail = get_the_post_thumbnail($post_id, "post-thumbnail", ["class" => "ns-videocourses-listing-item__img"]);
								$excerpt = get_field("videocourse_excerpt");
								$price = get_field("videocourse_regular_price");
								$price_text = "Only <strong>$" . $price . "</strong> for lifetime access!";
							?>

							<article class="ns-videocourses-listing-item">
								<div class="ns-videocourses-listing-item__container">
									<a href="<?php echo $permalink; ?>" class="ns-videocourses-listing-item__btn" aria-label="Navigate to <?php echo $title; ?> video course.">
										<?php if ($thumbnail): ?>
											<div class="ns-videocourses-listing-item__figure">
												<?php echo $thumbnail; ?>
											</div>
										<?php endif; ?>
										<div class="ns-videocourses-listing-item__content">
											<?php echo "<" . $article_htag . " class=\"ns-videocourses-listing-item__title\">" . $title . "</" . $article_htag . ">"; ?>
											<p class="ns-videocourses-listing-item__desc"><?php echo $excerpt; ?></p>
										</div>
									</a>
									<div class="ns-videocourses-listing-item__footer">
										<a href="<?php echo $permalink; ?>" class="ns-videocourses-listing-item__ctabtn" aria-label="Navigate to <?php echo $title; ?> video course.">
											<span><?php echo $cta; ?></span>
										</a>
										<?php if ($show_price): ?>
											<span class="ns-videocourses-listing-item__price"><?php echo $price_text; ?></span>
										<?php endif; ?>
									</div>
								</div>
							</article>

						<?php endwhile; ?>
						<?php wp_reset_query(); // always remember to reset the main query! ?>
					</div>
				</aside>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
