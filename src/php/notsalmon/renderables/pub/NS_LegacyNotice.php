<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;

/**
 * Renderable Template:
 * Renders a simple Legacy Notice. This is intended to be used as a `.ns-legacy-notice` 
 * that informs a user regarding things like Legacy Courses, where they should go to 
 * request access, and a large callout button to MastersInLife.com. 
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class NS_LegacyNotice extends Renderable {

	/**
     * Overriden method
     * Renders the Legacy Notice `.ns-legacy-notice` component.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		// shortcuts to context variables
		$heading = $context["heading"];
		$cta = $context["cta"];
		$cta_url = $context["cta_url"];
		$cta_target = $context["cta_target"];
		$style = $context["style"];
		$content = $context["content"];

		// we need at least a heading or cta url or content to render this. Otherwise, 
		// there's really nothing to show but a blank background
		if ($heading || $cta_url || $content){

			// ======= BEGIN OUTPUT ====================================================
			?>

				<aside class="ns-legacy-notice ns-legacy-notice--<?php echo $style; ?>">
					<?php if ($heading): ?>
						<h2 class="ns-legacy-notice__heading"><?php echo $heading; ?></h2>
					<?php endif; ?>
					<?php if ($content): ?>
						<div class="ns-legacy-notice__content"><?php echo wpautop($content); ?></div>
					<?php endif; ?>
					<?php if ($cta_url): ?>
						<div class="ns-legacy-notice__actions">
							<a class="ns-legacy-notice__btn" href="<?php echo $cta_url; ?>" target="<?php echo $cta_target; ?>">
								<span><?php echo ($cta) ? $cta : $cta_url; ?></span>
							</a>
						</div>
					<?php endif; ?>
				</aside>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
