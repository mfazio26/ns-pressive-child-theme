<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;

/**
 * Renderable Template:
 * Renders a specific Book's call-to-action that will create buttons to all the 
 * available retainers that sell/pre-order this Book. Please note, this component is 
 * configurable and can display in a number of ways and places...
 * 		- Can render in `the_content` or as a sidebar widget.
 * 		- It can render the current Book (if applicable) or by specifying an id...allowing 
 * 		  you to add these CTAs to/from any other page.
 * 		- It can be configured to include or exclude the Book's cover image if you 
 * 		  want an expanded or condensed box.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Books_CtaBox extends Renderable {

	/**
     * Overriden method
     * Renders a Book's retailer call-to-action. Please note, if this is rendered on a 
     * Single Book page and no id is specified, this will render based on that current 
     * book entry. (set in the shortcode atts)
     *
     * @since    1.0.0
     * @access   public
     * @todo 	 Do we want to create a version that only shows up on mobile at the very top?
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		// shortcuts to context variables
		$id = $context["id"];
		$cta = $context["cta"];
		$style = $context["style"];
		$show_image = $context["show_image"];
		$classes = (array_key_exists("classes", $context) && $context["classes"]) ? $context["classes"] : "";

		if ($id && PostTypeUtil::is_book_single($id)){
			// which tag to apply to the container
			$tag = ($style == "sidebar") ? "section" : "aside";
			// if the cta is defined, it will override the cta that belongs to the book.
			$cta = ($cta) ? $cta : get_field("book_cta", $id);
			// does the book have at least one retailer?
			$has_retailers = have_rows("book_retailers", $id);
			// get the book's cover image
			$image = get_the_post_thumbnail($id, "ns-book-cover", array("class" => "ns-book-cta__image"));
			// if the current page isn't this book's page, capture the book's permalink.
			$permalink = ($id != get_the_ID()) ? get_the_permalink($id) : false;
			// currently the only times we want to disable the image is if its explicitly 
			// set not to show (0), or if its rendering in the sidebar and "auto".
			$show_image = ($image && $show_image)
				? (($show_image == "auto") && ($style == "sidebar")) ? false : true 
				: false;

			// if it has a cta or at least one retailer, render it!
			if ($cta || $has_retailers){
				// ======= BEGIN OUTPUT ================================================
				?>
				
					<<?php echo $tag; ?> class="ns-book-cta ns-book-cta--<?php echo $style; ?> <?php echo $classes; ?>">
						<div class="ns-book-cta__container">
							<?php if ($show_image): ?>
								<div class="ns-book-cta__media">
									<?php if ($permalink): ?><a href="<?php echo $permalink; ?>"><?php endif; ?>
										<?php echo $image; ?>
									<?php if ($permalink): ?></a><?php endif; ?>
								</div>
							<?php endif; ?>

							<div class="ns-book-cta__content">
								<?php if ($cta): ?>
									<p class="ns-book-cta__heading"><?php echo $cta; ?></p>
								<?php endif; ?>

								<?php if ($has_retailers): ?>
									<ul class="ns-book-cta__list">
										<?php while (have_rows("book_retailers", $id)): the_row() ?>
											<li class="ns-book-cta__item">
												<a class="ns-book-cta__btn" href="<?php echo get_sub_field('book_retailer_url'); ?>" target="_self">
													<span><?php echo get_sub_field("book_retailer_name"); ?></span>
												</a>
											</li>
										<?php endwhile; ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					</<?php echo $tag; ?>>
				
				<?php 
				// ======= END OUTPUT ==================================================
			}
		}
	}
}
