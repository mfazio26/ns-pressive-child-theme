<?php

namespace notsalmon\bases;

/**
 * This class allows us to create a subclass that directly represents a renderable HTML 
 * component. Think of it like a pseudo-template or an extremely lite Templating Engine. 
 * We are intentionally not using WordPress's `get_template_part()` because it has the 
 * unwanted side-effect of making all variables globally accessible...which makes 
 * naming variables way too verbose and opens the door to naming collisions with parent 
 * themes and plugins.
 *
 * By using this setup instead, we can keep all of our processing and variables scoped 
 * to only the subclass that is doing the work. Trust me, its better.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Renderable {

	/**
     * Intended to be overriden by a subclass component.
     * Will immediately output the renderable content within this method. Please note, 
     * this is accomplished by opening and closing the PHP doctype tag. Anything 
     * in-between will act and render like a standard WordPress template – however, any 
     * variables defined within the method will be scoped to the method itself...and 
     * not globally. Yay!!!
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()) {
		?><?php 
	}

	/**
     * Instead of immediately outputting the renderable content, this method will 
     * capture that to a string value that can be assigned to a variable. This allows 
     * you to use the same pseudo-template subclass to output content requested by 
     * either WordPress actions or filters.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     * @return 	 String 			  				Rendered HTML-formatted string.
     */
	public function render_to_var($context = array()){
		ob_start();
		$this->render($context);
          // we have to get rid of newlines, tabs, etc because WP can create convert 
          // some things to `<br>` automatically...and we don't want that!
		$value = preg_replace("/\r+|\n+|\t+/", "", ob_get_contents());
		ob_end_clean();
		return $value;
	}
}
