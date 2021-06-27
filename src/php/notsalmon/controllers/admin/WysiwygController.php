<?php

namespace notsalmon\controllers\admin;
use notsalmon\bases\Controller;

/**
 * This class applies additional function and behaviors to the Admin WYSIWYG editor 
 * such as – adding custom formats to the toolbar, adding a custom stylesheet, etc.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class WysiwygController extends Controller {

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("after_setup_theme", array($this, "action_add_editor_stylesheet"), 20);
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		add_filter("mce_buttons", array($this, "filter_wysiwyg_buttons_1"));
		add_filter("tiny_mce_before_init", array($this, "filter_wysiwyg_add_formats"));
	}

	// ======= STYLESHEET ==============================================================
	/**
     * Adds/Loads the custom `editor-style.css` file that applies the the WYSIWYG editor.
     *
     * @since    1.0.0
     * @access   public
     */
	public function action_add_editor_stylesheet(){
		add_editor_style();
	}

	// ======= FORMAT BUTTONS ==========================================================
	/**
     * Adds the Format button to the WYSIWYG toolbar (hidden by default)
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $buttons 		First-row list of buttons.
     * @return   Array 								The updated array of buttons
     */
	public function filter_wysiwyg_buttons_1($buttons){
		array_unshift($buttons, "styleselect");
		return $buttons;
	}

	/**
     * Defines and adds additional "styles" to the Format dropdown menu that was turned 
     * on in `ns_wysiwyg_buttons_1`.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $mceinit 		An array with TinyMCE config.
     * @return   Array 								The updated array of TinyMCE config.
     */
	public function filter_wysiwyg_add_formats($mceinit){
		$style_formats = array(
			array(
				"title" => "Aqua Blue",
				"selector" => "h1,h2,h3,h4,p,strong,a,span",
				"classes" => "wysiwyg-color--aqua-blue",
				"wrapper" => false
			),
			array(
				"title" => "Mint Green",
				"selector" => "h1,h2,h3,h4,p,strong,a,span",
				"classes" => "wysiwyg-color--mint-green",
				"wrapper" => false
			),
			array(
				"title" => "Orange",
				"selector" => "h1,h2,h3,h4,p,strong,a,span",
				"classes" => "wysiwyg-color--orange",
				"wrapper" => false
			)
		);
		$mceinit["style_formats"] = json_encode($style_formats);
		return $mceinit; 
	}
}
