<?php

namespace notsalmon\controllers\admin;
use notsalmon\bases\Controller;

/**
 * This class defines and configures the custom images that will be generated 
 * by WordPress when an image is uploaded. 
 *
 * IMPORTANT: Please see the `$this->filter_image_resize_dimensions()` 
 * documentation. The gist is – Thrive Themes doesn't directly support, consider,
 * or expect Custom Post Types or any type of custom designed pages/components. 
 * Because of this – they enforce their settings on ALL images uploaded – 
 * including the specific image sizes and shapes we need on some of our pages. 
 * To resolve the issue, we have to intervene and stop Thrive Theme's default 
 * behavior where applicable.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class ImageResizeController extends Controller {

	/**
     * Add custom image size configurations.
     *
     * @since    1.0.0
     * @access   public
     * @var      array
     */
	public $custom_sizes = array(
		"ns-category-image" => array("width" => 360, "height" => 360, "crop" => false),
		"ns-poster-image" => array("width" => 640, "height" => 640, "crop" => false),
		"ns-book-cover" => array("width" => 498, "height" => 730, "crop" => false), // 2x
		"ns-book-related" => array("width" => 205, "height" => 300, "crop" => array("center", "center"))
	);

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("after_setup_theme", array($this, "action_add_custom_image_sizes"), 20);
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		add_filter("image_resize_dimensions", "filter_image_resize_dimensions", 20, 6);
	}

	// ======= SETUP ===================================================================
	/**
	 * Adds our custom image sizes so they will be generated on image upload.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function action_add_custom_image_sizes(){
		add_theme_support("post-thumbnails");

		// add custom sizes
		foreach ($this->custom_sizes as $name => $opts){
			add_image_size($name, $opts["width"], $opts["height"], $opts["crop"]);
		}
	}

	// ======= RESIZE OVERRIDE =========================================================
	/**
	 * IMPORTANT: Thrive Themes enforces "Scale/Crop and Scale" on all generated image 
	 * sizes – including ours. In some scenarios (like creating MIL Course images in the  
	 * `ns-book-related` aspect ratio) the "crop" can be ignored and only scaled. We 
	 * don't want that on our custom images...so, we have to override what Thrive Themes 
	 * is doing in these scenarios. When an image is supposed to be cropped per the 
	 * "crop" paramter in `add_image_sizes()` we are going to tell WP to do its normal 
	 * thing. And if `crop=false`, we are going to pass this through and back to the 
	 * original Thrive Themes function so it can do what it normally expects to do.
	 *
	 * PLEASE NOTE: The priority of the filter is important. We have to ensure this 
	 * happens after the Thrive Theme's filter is run, or else they'll just override 
	 * what we wanted to do anyways.
	 *
	 * @since    1.0.0
	 * @see 	 themes/pressive/inc/image-resize.php on line 459.
     * @access   public
     * @param 	 [null|Mixed] 		  $payload 		Whether to preempt output of the resize dimensions
     * @param    Int 				  $orig_w 		Original width in pixels
     * @param    Int 				  $orig_h 	    Original height in pixels
     * @param    Int 				  $dest_w 		New width in pixels
     * @param    Int 				  $dest_h 		New height in pixels
     * @param    [Boolean|Array] 	  $crop 		Whether to crop image to specified widtha nd height or resize. 
     *                                      	    An array can specify positioning of the crop area. Default false.
     * @return   [Boolean|Array] 					False on failure. Returned array matches parameters for imagecopyresampled()
	 */
	public function filter_image_resize_dimensions($payload, $orig_w, $orig_h, $dest_w, $dest_h, $crop){
		if ($crop) return false;
		return thrive_custom_image_resize_dimensions($payload, $orig_w, $orig_h, $dest_w, $dest_h, $crop);
	}
}
