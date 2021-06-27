<?php

namespace notsalmon\controllers;
use notsalmon\bases\Controller;

/**
 * IMPORTANT: FOR DEVELOPER USE ONLY!!!
 * This class is intended for utilities, helpers, and basically anything that aids in 
 * the migration process.
 *
 * Fr most (if not all) purposes, EVERYTHING in this file can and should be disabled 
 * after the migration process is complete (See `Theme.$enable_migration_controllers`) 
 * or when you no longer need an action/filter to be active. Please be careful!
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class System_MigrationController extends Controller {

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		// add_filter("http_request_host_is_external", array($this, "filter_http_request_host_is_external"));
	}

	// ======= LOCAL ENVIRONMENT TO LOCAL ENVIRONMENT ==================================
	/**
	 *
	 * Download associated media
	 * 
	 * Turn this filter on if you are copying/importing content from one local domain to 
	 * another local domain (ie...`notsalmon.legacy` to `notsalmon.dev`) and you have 
	 * also selected to import the associated media. Reasons is – WordPress checks to see 
	 * if the two domains' IP addresses are different in order to determine if the 
	 * request is from/to an external location. If they are different, it allows you to 
	 * import the media files. However, if they are the same (ie...127.0.0.1), WordPress 
	 * will refuse to import the files because it believes the files are already in the 
	 * destination. So, we basically have to force WordPress to process each request as 
	 * if it is always an external request.
	 *
	 * @since    1.0.0
     * @access   public
     * @return   Boolean                			True to trick WP into thinking its requesting an external source.
	 */	
	public function filter_http_request_host_is_external(){
		return true;
	}
}
