<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;

/**
 * Renderable Template:
 * Renders content inside a theme-able colored box. Authors have a choice of using 
 * pre-defined "themes" (1-4) or determining their own text and background color 
 * scheme by providing the correct variables. 
 *
 * Please note, by default, themes will take precedence over color variables. So, if 
 * a valid theme number is provided, custom color variables will be ignored. If 
 * custom color values are provided and invalid for any reason, a default color scheme 
 * will be applied (theme 1).
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class NS_Colorbox extends Renderable {

	/**
     * Validates a given color by adding the leading hash (#) if not provided, and then 
     * checking if the color is a valid hex color. If it isn't, the default color will 
     * be used instead. Please note, because this is a private function, a default color 
     * MUST ALWAYS be provided because there is no DEFAULT for the DEFAULT...because 
     * there is no way for us to guess a reasonable value here.
     *
     * @since    1.0.0
     * @access   private
     * @param 	 String 			$color 			A valid hex color.
     * @param 	 String 		    $def 			A valid hex color to use as a default.
     * @return 	 String 							The valid hex color or default provided.
     */
	private function _get_valid_color($color, $def){
		if ($color) {
			// if it doesn't start with a hash, add it.
			$color = (substr($color, 0, 1) !== "#") ? ("#" . $color) : $color;
			// is the color a valid hex color? If so, return it.
			if (preg_match("/#([a-f0-9]{3}){1,2}\b/i", $color, $matches) === 1) {
				if (count($matches) > 0) {
					return $color;
				}
			}
		}
		// if the provided color is not valid, return the default color!
		return $def;
	}

	/**
     * Overriden method
     * Renders content inside a theme-able colored box. Authors have a choice of using 
     * pre-defined "themes" (1-4) or determining their own text and background color 
     * scheme by providing the correct variables. 
     * 
     * Please note, by default, themes will take precedence over color variables. So, if 
     * a valid theme number is provided, custom color variables will be ignored. If 
     * custom color values are provided and invalid for any reason, a default color scheme 
     * will be applied (theme 1).
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		// get parameters and provide defaults if invalid
		$text_color = (array_key_exists("color", $context) && $context["color"]) ? $context["color"] : null;
		$bg_color = (array_key_exists("bgcolor", $context) && $context["bgcolor"]) ? $context["bgcolor"] : null;
		$theme = (array_key_exists("theme", $context) && $context["theme"]) ? $context["theme"] : -1;
		$content = (array_key_exists("content", $context) && $context["content"]) ? $context["content"] : null;

		// holds the theme modifier (if applicable)
		$modifier = "";
		// holds the style="" attribute (if applicable)
		$styles = "";
		// was a valid theme provided? if so, this takes precedence over custom color variables
		$is_theme = false;
		// is the content valid? should always be true because it will always be wrapped in `<p></p>` tags.
		$is_valid = ($content && (trim($content) !== "")) ? true : false;


		// is the theme variable provided and a number?
		if ($theme && is_numeric($theme)) {
			// cast it to an integer
			$theme = (int) $theme;
			// is the theme variable 1,2,3 or 4? (we only have 4 theme presets for now) if so, 
			// create the css modifier class and mark it as using the theme
			if (($theme >= 1) && ($theme <= 4)){
				$modifier = "ns-colorbox--theme" . $theme;
				$is_theme = true;
			}
		}

		// are we not using a preset theme? if not, we are going to try and apply custom color variables, 
		// or apply defaults (theme 1) if not provided or valid.
		if (!$is_theme) {
			// validate the custom colors, or get the defaults.
			$text_color = $this->_get_valid_color($text_color, "#393E41");
			$bg_color = $this->_get_valid_color($bg_color, "#CEEFE3");
			// create the styles attribute
			$styles = " style=\"color: " . $text_color . "; background-color: " . $bg_color . ";\" ";
		}

		// this should always be true!
		if ($is_valid){

			// ======= BEGIN OUTPUT ====================================================
			?>

				<aside class="ns-colorbox<?php echo " " . $modifier; ?>"<?php echo $styles; ?>>
					<div class="ns-colorbox__inner">
						<p><?php echo $content; ?></p>
					</div>
				</aside>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
