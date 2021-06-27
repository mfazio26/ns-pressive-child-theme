<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;

/**
 * Renderable Template:
 * Renders the Quotes Intro on archive pages. Please note, this section is populated 
 * via an Advanced Custom Fields Options page attached to "Quotes > Quotes Settings".
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Posters_ArchiveIntro extends Renderable {

	/**
     * Overriden method
     * Renders the Quotes Intro on archive pages. Please note, this section is populated 
     * via an Advanced Custom Fields Options page attached to "Quotes > Quotes Settings".
     *
     * @since    1.0.0
     * @access   public
     * @todo 	 Need to determine if this is a single or archive (before/after, box/no-box)
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){

		// If context is undefined, don't do anything!
		if (!$context) return;

		// get the values from the array
		$headline = (array_key_exists("headline", $context) && $context["headline"]) ? $context["headline"] : null;
		$content = (array_key_exists("content", $context) && $context["content"]) ? $context["content"] : null;
		$tagtype = (array_key_exists("tagtype", $context) && $context["tagtype"]) ? $context["tagtype"] : "aside";

		// if both values are "null", don't do anything!
		if (!$headline && !$content) return;


		// ======= BEGIN OUTPUT ========================================================
		?>
		
			<<?php echo $tagtype; ?> class="ns-poster-archiveintro">
				<div class="ns-poster-archiveintro__container">
					<?php if ($headline): ?>
						<h2 class="ns-poster-archiveintro__headline"><?php echo $headline; ?></h2>
					<?php endif; ?>
					<?php if ($content): ?>
						<div class="ns-poster-archiveintro__content">
							<?php echo $content; ?>
						</div>
					<?php endif; ?>
				</div>
			</<?php echo $tagtype; ?>>

		<?php 
		// ======= END OUTPUT ==========================================================
	}
}
