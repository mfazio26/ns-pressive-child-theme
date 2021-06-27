<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;

/**
 * Renderable Template:
 * Renders a single Book callout with a title, book cover image, and book title label. 
 * This is intended to be used inside a "post" to callout a "featured" or "related" 
 * book that navigates straight to that book's page.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Books_SingleInlineCTA extends Renderable {

	/**
     * Overriden method
     * Renders a single Book callout with a title, book cover image, and book title label. 
     * This is intended to be used inside a "post" to callout a "featured" or "related" 
     * book that navigates straight to that book's page, or optionally, navigate directly 
     * to a preferred retailer url.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		
		// capture attributes if they exist, otherwise provide defaults.
		
		// callout heading
		$title = (array_key_exists("title", $context) && $context["title"]) ? $context["title"] : "Related Book";
		// book id
		$post_id = (array_key_exists("id", $context) && $context["id"]) ? $context["id"] : -1;
		// alignment - can be ["left", "right", "center"]. If not specified or invalid, defaults to "center".
		$align = (array_key_exists("align", $context) && $context["align"]) ? strtolower($context["align"]) : null;
		$align = (in_array($align, ["left", "right", "center"])) ? $align : "center";
		// preferred retailer name (ie..."amazon"). if not set or available, it will navigate to the corresponding NS book url.
		$retailer = (array_key_exists("preferred_retailer", $context) && $context["preferred_retailer"]) ? $context["preferred_retailer"] : null;

		// If the book id is undefined or is not a "book", don't render anything!
		if (!PostTypeUtil::is_book_single($id=$post_id)) return "";

		// get the specified book url and title. at this point, we know these have to exist.
		$book_url = get_the_permalink($post_id);
		$book_title = get_the_title($post_id);
		$book_image = get_the_post_thumbnail($post_id, "ns-book-cover", array("class" => "ns-book-single-inline-cta__image"));
		
		// if a retailer was provided, attempt to retrieve the corresponding url. Please note, "none" is intended to 
		// navigate to the ns book sales pages.
		if ($retailer && ($retailer != "none")){
			// create lowercase "needle"
			$preferred_retailer = strtolower($retailer);


			// get the ACF repeater field for this book
			if (have_rows("book_retailers", $post_id)){

				// note: we have to use rows instead of a while loop because there is an ACF issue when you have multiple 
				// of the same type of query.
				$rows = get_field("book_retailers", $post_id);

				$matched_book_url = null;

				foreach ($rows as $row){
					// get the retailer name - used as "haystack"
					$retailer_name = strtolower($row["book_retailer_name"]);
					// get the retailer url
					$retailer_url = $row["book_retailer_url"];

					// If the mode is not set to "auto", try to find the matching retailer item.
					if ($preferred_retailer != "auto"){
						// check if the "needle" exists in the "haystack"
						$found = strrpos($retailer_name, $preferred_retailer);

						// if we found a match, set the book url and exit the while loop!
						if ($found !== false){
							$matched_book_url = $retailer_url;
							break 1;
						}
					}
					// If the mode is set to "auto", we just need to retrieve the first "preferred" retailer item.
					else {
						$matched_book_url = $retailer_url;
						break 1;
					}
				}

				// Finally, determine if we found a match and apply it. Please note, if a specific retailer is provided 
				// (ex: "amazon"), but not found, we are not assuming this should fallback to the first retailer ("auto" mode). 
				// Instead, it should default to the ns book sales page instead.
				if ($matched_book_url){
					$book_url = $matched_book_url;
				}
			}
		}


		// If book id is defined, render it!
		// Basically, if we made it this far, we're fine to render.
		if ($post_id) {
			// ======= BEGIN OUTPUT ====================================================
			?>

				<aside class="ns-book-single-inline-cta ns-book-single-inline-cta--<?php echo $align; ?>">
					<h2 class="ns-book-single-inline-cta__heading"><?php echo $title; ?></h2>
					<a href="<?php echo $book_url; ?>" class="ns-book-single-inline-cta__btn" aria-label="Navigate to <?php echo $book_title; ?> page.">
						<figure class="ns-book-single-inline-cta__media">
							<?php echo $book_image ?>
						</figure>
						<span class="ns-book-single-inline-cta__caption"><?php echo $book_title; ?></span>
					</a>
				</aside>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
