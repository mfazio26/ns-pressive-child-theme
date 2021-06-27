<?php

namespace notsalmon;
use notsalmon\bases\Controller;

/**
 * This is the main entry point to instantiate our child theme and all its required 
 * components. Please don't get too specific in this file because we want to make it as 
 * clear as possible what its doing. Instead, you should delegate any more specific 
 * functionality to their corresponding "more specific" class counterparts. 
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Theme extends Controller {
	
     /**
     * IMPORTANT: FOR DEVELOPER USE ONLY!!!
     * Throughout this child theme there are some scenarios where different actions are 
     * taken depending on whether the current logged in user is or isn't listed in this 
     * array. For example, users/developers listed in this array will see the full admin 
     * menu sidebar, and authors will see a "decluttered" version where a bunch of 
     * unneccessary (and potentially harmful) menu and submenu items are removed.
     *
     * Please note, this is NOT intended to be used as an implementation of "formal 
     * permissions". For example, although some admin menus/submenu items are not visible 
     * to authors – all of the action urls will still accessible and function as usual. 
     * So, if an author knows the action url to switch themes – there is nothing additional 
     * in place to stop them from doing so.
     *
     * @since    1.0.0
     * @access   public
     * @static 
     * @todo     This probably belongs in some type of Settings Model, but for simplicity 
     *           and accessibility, this seems like the best place to put this for now.
     * @example  in_array($current_user->user_login, Theme::$developers);
     *           $developers = array("karen");
     * @var      Array                $developers                     Array of developer login usernames.
     */
     public static $developers = array("mfazio", "shellylipton");

	/**
     * IMPORTANT: FOR DEVELOPER USE ONLY!!!
     * Sets whether or not to instantiate any Controllers that are specifically intended 
     * to aid a developer in the migration process. These are things that should not EVER 
     * be enabled in a live environment.
     *
     * @since    1.0.0
     * @access   private
     * @var      Boolean              $enable_migration_controllers   True to instantiate migration controllers, false to not.
     */
	private $enable_migration_controllers = false;

     /**
     * A global container where we can store any global child-theme specific variables and 
     * values. The intention here is, there are templates, partials, etc that need access 
     * to global state variables/values – which is difficult to provide without creating a 
     * dedicated singleton somewhere. Instead, during the `init()` method, we are attaching 
     * this as a global object via `global $ns_options`. This works much the same way as 
     * Thrive's global `$options` variable.
     *
     * @since    1.0.0
     * @access   public
     * @var      Array                $options                        Holds global child-theme specific variables and values.
     */
     public $options = array();

     /**
     * Initialize and startup. We are creating a global `$ns_options[]` array for our other 
     * classes, renderables, and partials to store values in. This is simple for convenience 
     * as well as filling a few functionality gaps.
     *
     * @since    1.0.0
     * @access   protected
     * @override
     */
     protected function init() {
          global $ns_options;
          $ns_options = $this->options;

          // call super-class!
          parent::init();
     }

	/**
     * Load and instantiate all dependencies. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function load_dependencies(){
		new controllers\Admin_CommonController();
          new controllers\Public_CommonController();

		// for developer use only! please be careful!
		if ($this->enable_migration_controllers){
			new controllers\System_MigrationController();
		}
	}
}
