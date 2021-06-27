<?php

namespace notsalmon\bases;

/**
 * This class is simply intended to reduce repetitive code in all the controller 
 * subclasses by initializing itself and automatically calling all of our load/register 
 * helper methods. This also helps with organizing code in the subclass controllers 
 * because you can quickly identify what is a dependency, action, filter, or shortcode.
 *
 * Please note, there are no implementations of the helper methods, so technically, you 
 * can do whatever you want with them if you had a need to do something different. But, 
 * its probably better if you stick to the original intended usage.
 *
 * Please note, you only have to override the methods that you will be using. For example, 
 * if you don't have any "filters", you don't have to bother overriding that method. Easy.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Controller {
	
	/**
     * Called when initially instantiated. If you include a constructor in the subclass 
     * and you still want this to run, you will have to call it manually via the 
     * `parent::__construct()` method.
     *
     * @since    1.0.0
     * @access   public
     */
	public function __construct(){
		$this->init();
	}

	/**
     * Initialize and startup. You can override this method, however, you will have to 
     * call any helper methods manually.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function init() {
		$this->load_dependencies();
		$this->register_actions();
		$this->register_filters();
		$this->register_shortcodes();
	}

	// ======= HELPERS : INTENDED TO BE OVERRIDEN ======================================
	/**
     * Intended to be instantiate and initialize any dependencies. (ie...new HelperClass())
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function load_dependencies() {} 

	/**
     * Intended to register all WP/Theme/Plugin actions. (ie...add_action())
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions() {}

	/**
     * Intended to register all WP/Theme/Plugin filters. (ie...add_filter())
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters() {}

	/**
     * Intended to register our own custom child-theme shortcodes. (ie...add_shortcode())
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_shortcodes() {}

}
