<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;

/**
 * Renderable Template:
 * Renders a list of Poster Tags. 
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Posters_TagListBox extends Renderable {

	/**
     * Overriden method
     * Renders the Poster Tags List component.
     *
     * @since    1.0.0
     * @access   public
     * @todo 	 Need to determine if this is a single or archive (before/after, box/no-box)
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		$tags = wp_list_categories(array(
				"echo" => 0,
				"taxonomy" => "poster_tag",
				"title_li" => "",
				"style" => "none",
				"separator" => "",
				"hide_empty" => true
			)
		);

		// is this an archive page? or a single page? each has a different style associated
		$is_archive = ($context && array_key_exists("is_archive", $context)) ? $context["is_archive"] : false;

		// ======= BEGIN OUTPUT ========================================================
		?>
		
			<aside class="ns-poster-tags ns-poster-tags--<?php echo ($is_archive) ? "archive" : "single" ?>">
				<div class="ns-poster-tags__container">
					<span class="ns-poster-tags__heading">Browse by topic:</span>
					<p class="ns-poster-tags__list"><?php echo $tags ?></p>
					<p class="ns-poster-tags__message">Share &amp; Pass on the Positivity!</p>
				</div>
			</aside>	

		<?php 
		// ======= END OUTPUT ==========================================================
	}
}
