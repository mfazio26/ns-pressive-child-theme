<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;
use notsalmon\utils\TeachableUtil;

/**
 * Renderable Template:
 * Renders a specified list of Books and MastersInLife Courses. Optionally, you can 
 * include a listing title, content, list of specified MIL Categories, and an MIL 
 * Categories caption/description. Please note, both Courses and Categories are 
 * intended to redirect immeidately to their corresponding MastersInLife.com URLs.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Books_MilCourses_ListingBox extends Renderable {

	/**
     * Overriden method
     * Renders a specified list of Books and MastersInLife Courses.
     *
     * Please note, this renders identically to `Courses_RelatedBox` and 
     * `Books_RelatedBox`. The only reason why they are separate is there may be a 
     * need in the future to have them render differently – and its better to be a 
     * little more verbose now, then to have to rip it apart later.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		if ($context){


			// modifier component classes
			$classes = array();
			// boxed or transparent bg: 0=transparent, 1=boxed
			if ($context["boxed"]) $classes[] = "ns-related-covers--boxed";
			// fullwidth or limited: 0=limited, 1=fullwidth
			if ($context["fullwidth"]) $classes[] = "ns-related-covers--full";
			// number of columns: currently can only be 2, 3 or 4. any other number will default to 2.
			$columns = (array_key_exists("columns", $context) && $context["columns"]) ? $context["columns"] : 2;

			// add columns class
			if ($columns == 3) { $classes[] = "ns-related-covers--thirds"; }
			elseif ($columns == 4) { $classes[] = "ns-related-covers--fourths"; }
			

			// generate the modifier classes string
			$classes_str = (count($classes) > 0) ? implode(" ", $classes) : null;

			// capture properties
			$title = (array_key_exists("title", $context) && $context["title"]) ? $context["title"] : null;
			$content = (array_key_exists("content", $context) && $context["content"]) ? $context["content"] : null;
			$ids = (array_key_exists("ids", $context) && $context["ids"]) ? explode(",", $context["ids"]) : null;
			$mil_category_ids = (array_key_exists("milcourse_category_ids", $context) && $context["milcourse_category_ids"]) ? explode(",", $context["milcourse_category_ids"]) : null;
			$mil_category_caption = (array_key_exists("milcourse_category_caption", $context) && $context["milcourse_category_caption"]) ? $context["milcourse_category_caption"] : null;

			// mastersinlife properties
			$mil_url = TeachableUtil::get_url("", TeachableUtil::get_tracking_query());
			$mil_name = TeachableUtil::get_display_name();
			$mil_show_icon_button = false;

			// we will use these to contain only valid ids
			$ids_filtered = array();
			$mil_category_ids_filtered = array();

			// filter ids that only belong to Books and MIL Courses
			if ($ids && (count($ids) > 0)) {
				foreach ($ids as $id){
					$id = trim($id);
					$is_book = PostTypeUtil::is_book_single((int)$id);
					$is_milcourse = PostTypeUtil::is_mil_course_single((int)$id);
					// if it has a regular price, its a valid video course page!
					$is_video_course = (get_field("videocourse_regular_price", (int)$id)) ? true : false;

					if ($id && ($id != "") && ($is_book || $is_milcourse || $is_video_course)){
						$ids_filtered[] = array("id" => $id);
					}
				}
			}

			// filter ids that only belong to MIL Course Categories
			if ($mil_category_ids && (count($mil_category_ids) > 0)) {
				foreach ($mil_category_ids as $id){
					$id = trim($id);
					if ($id && ($id != "") && PostTypeUtil::is_mil_course_category((int)$id)){
						$mil_category_ids_filtered[] = (int)$id;
					}
				}
			}

			// shortcuts so we don't have to check more than once
			$has_entries = (count($ids_filtered) > 0) ? true : false;
			$has_mil_categories = (count($mil_category_ids_filtered) > 0) ? true : false;


			// requires at least one valid entry or mil category to render
			if ($has_entries || $has_mil_categories) {

				// ======= BEGIN OUTPUT ================================================
				?>

					<aside class="ns-related-covers<?php echo ($classes_str) ? (" " . $classes_str) : "" ?>">
						<?php if ($title): ?>
							<h2 class="ns-related-covers__heading"><?php echo $title; ?></h2>
						<?php endif; ?>
						<?php if ($content): ?>
							<div class="ns-related-covers__content">
								<?php echo wpautop($content, true); ?>
							</div>
						<?php endif; ?>

						<?php if ($has_entries): ?>
							<div class="ns-related-covers__listing">
								<?php foreach ($ids_filtered as $entry): ?>
									<?php 
										$post_id = $entry["id"];
										$post_permalink = get_the_permalink($post_id);
										$post_type = get_post_type($post_id);
										$post_title = get_the_title($post_id);
										$post_image = (has_post_thumbnail($post_id)) ? get_the_post_thumbnail($post_id, "ns-book-related", array()) : null;
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
						<?php endif; ?>

						<?php if ($has_mil_categories): ?>
							<div class="ns-related-covers-footer">
								<?php if ($mil_category_caption): ?>
									<h3 class="ns-related-covers-footer__caption"><?php echo $mil_category_caption; ?></h3>
								<?php endif; ?>
								<ul class="ns-related-covers-footer__list">
									<?php foreach ($mil_category_ids_filtered as $id): 
										$category = get_term($id, "mil_category");
										$category_permalink = get_term_link($category);
										// $category_permalink = get_field("mil_category_external_url", $category);
									?>
										<li class="ns-related-covers-footer__item">
											<a class="ns-related-covers-footer__btn" href="<?php echo $category_permalink; ?>"><?php echo $category->name; ?></a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</aside>

				<?php 
				// ======= BEGIN OUTPUT ================================================
			}
		}
	}
}
