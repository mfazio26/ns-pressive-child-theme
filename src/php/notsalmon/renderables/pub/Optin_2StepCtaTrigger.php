<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;

/**
 * Renderable Template:
 *
 * Renders a customizable CTA box that will trigger a specific Thrive Leads Lightbox 
 * by id. By default, this shortcode will render the "General Subscribe/Happy Inbox" 
 * lightbox, but it can be configured to open any valid Thrive 2step Lightbox post type. 
 * This shortcode can be used on any page/post, and has a few customizable options - 
 * such as layout (boxed, inline, nobox), which optin lightbox id, theme/color styles, 
 * and how much vertical spacing is applied.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Optin_2StepCtaTrigger extends Renderable {

	/**
     * Overriden method
     * Renders a customizable CTA box that will trigger a specific Thrive Leads Lightbox 
     * by id. By default, this shortcode will render the "General Subscribe/Happy Inbox" 
     * lightbox, but it can be configured to open any valid Thrive 2step Lightbox post type. 
     * This shortcode can be used on any page/post, and has a few customizable options - 
     * such as layout (boxed, inline, nobox), which optin lightbox id, theme/color styles, 
     * and how much vertical spacing is applied.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){

		// collect context properties
		$content = (array_key_exists("content", $context) && $context["content"]) ? trim($context["content"]) : null;
		$optin_id = (array_key_exists("optin_id", $context) && $context["optin_id"]) ? $context["optin_id"] : -1;
		$layout_id = (array_key_exists("layout", $context) && $context["layout"]) ? $context["layout"] : -1;
		$theme_id = (array_key_exists("style", $context) && $context["style"]) ? $context["style"] : 1;
		// any truthy value is valid for expanded!
		$expanded = (array_key_exists("expanded", $context) && $context["expanded"]) ? true : false; 


		// available/valid layout choices (applied as class modifiers!)
		$layouts = array("inline", "nobox", "boxed");
		

		// can we render the cta trigger? all required criteria must be met!
		$is_valid = true;

		// is the content defined and valid?
		if (!$content) {
			$is_valid = false;
		}

		// is the optin defined and a valid Thrive 2step optin post type?
		if ($optin_id && ($optin_id != -1)){
			$optin_post = get_post($optin_id);

			if (!$optin_post || ($optin_post->post_type != "tve_lead_2s_lightbox")){
				$is_valid = false;
			}
		}
		else {
			$is_valid = false;
		}

		// is it a valid layout? must be a valid index (plus 1 because zero is falsey)
		if (($layout_id <= 0) || ($layout_id > count($layouts))) {
			$is_valid = false;
		}

		// if anything was invalid, don't render anything!
		if (!$is_valid) return "";



		// which layout should we use? subtract one from the id index so its a valid array index
		$layout_style = $layouts[$layout_id - 1];

		// which tag should we use? ie..."inline" layout has to be a span!
		$tag_type = ($layout_id == 1) ? "span" : "aside";

		// which text style/theme should we use? defaults to "primary".
		$themes = array("primary");
		$theme_style = (in_array($theme_id - 1, $themes)) ? $themes[$theme_id - 1] : $themes[0];

		// is it expanded? or condensed? (default)
		$expanded_class = ($expanded) ? "expanded" : "condensed";


		
		// apply modifier classes
		$modifier_classes = "ns-optin-2stepctatrigger--" . $layout_style . " ns-optin-2stepctatrigger--" . $theme_style . " ns-optin-2stepctatrigger--" . $expanded_class;

		// create the content container that will be placed inside the thrive shortcode
		$cta = "<span class=\"ns-optin-2stepctatrigger__cta\">" . $content . "</span>";

		// render the thrive shortcode to a string
		$thrive_shortcode = do_shortcode("[thrive_2step id=\"" . $optin_id . "\"]" . $cta . "[/thrive_2step]");
		
		
		// If all is valid, render it!
		if ($is_valid) {
			// ======= BEGIN OUTPUT ====================================================
			?>

				<<?php echo $tag_type; ?> class="ns-optin-2stepctatrigger <?php echo $modifier_classes; ?>">
					<?php echo $thrive_shortcode; ?>
				</<?php echo $tag_type; ?>>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
