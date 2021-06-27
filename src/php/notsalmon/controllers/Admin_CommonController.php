<?php

namespace notsalmon\controllers;
use notsalmon\Theme;
use notsalmon\bases\Controller;
use notsalmon\utils\PathUtil;

/**
 * This class is intended to contain anything that is "generic" in the Admin area within our 
 * child theme that is not specific to anything in particular – meaning, the functionality 
 * in this file can and will be applied to every page loaded in the Admin area. If all of 
 * the other Admin classes' intended purposes are too specific for any potential addition, 
 * odds are that new functionality belongs in this class.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Admin_CommonController extends Controller {

	/**
     * Initialize and startup. We also need to do some additional setup - such as registering 
     * Advanced Custom Fields Options pages.
     *
     * @since    1.0.0
     * @access   protected
     * @override
     */
	protected function init() {
		parent::init();

		// additional setup
		$this->register_acf_options_pages();
	}

	/**
     * Load and instantiate all dependencies. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function load_dependencies(){
		// creates our custom images sizes and behaviors
		$imager = new admin\ImageResizeController();
		// adds additional functionality to the admin wysiwyg editor
		$wysiwyg_editor = new admin\WysiwygController();
	}

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("widgets_init", array($this, "action_register_widgets"));
		add_action("admin_menu", array($this, "action_menu_declutter"), 9999);
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		// add_filter("page_attributes_dropdown_pages_args", array($this, "filter_attributes_dropdown_pages_args"), 1, 1);
	}

	// ======= PAGE DROPDOWN ARGS =====================================================
	/**
	 * By default, when selecting a Parent page you are only allowed to select pages 
	 * that are "published". This filter allows us to also select pages that are 
	 * currently set to "drag". This allows us to build up page hierarchies before 
	 * publishing any of the content and push everything live all at once.
	 *
	 * @since    1.0.0
     * @access   public
	 */	
	public function filter_attributes_dropdown_pages_args(){
		$dropdown_args["post_status"] = array("publish", "draft");
		return $dropdown_args;
	}

	// ======= ACF OPTIONS PAGES =======================================================
	/**
	 * Register Advanced Custom Fields Options pages - such as the "Quotes Settings"
	 * (where you can define the intro content) and a "Posts Settings" (where you can 
	 * define the "scroll to comments" label. Please note, you still have to create 
	 * the individual Field Groups and attach them to the pages via the UI.
	 *
	 * @since    1.0.0
     * @access   public
     * @see 	 https://www.advancedcustomfields.com/resources/options-page/
	 */	
	public function register_acf_options_pages(){
		if (function_exists("acf_add_options_sub_page")){
               acf_add_options_sub_page(array(
                    "page_title"        => "Quotes Settings",
                    "menu_title"        => "Quotes Settings",
                    "menu_slug"         => "quotes-settings",
                    "capability"        => "edit_posts",
                    "parent_slug"       => "edit.php?post_type=poster",
                    "position"          => false,
                    "icon_url"          => false
               ));

               acf_add_options_sub_page(array(
                    "page_title"        => "Posts Settings",
                    "menu_title"        => "Posts Settings",
                    "menu_slug"         => "posts-settings",
                    "capability"        => "edit_posts",
                    "parent_slug"       => "edit.php",
                    "position"          => false,
                    "icon_url"          => false
               ));
          }
	}
	
	// ======= WIDGETS =================================================================
	/**
	 * We have created a handful of NS-specific widgets, and we need to register them 
	 * here to make them accessible in the WP backend. For example, we have an 
	 * `NS_Widget_Shortcode` that allows us to render shortcodes directly in the sidebar. 
	 * This is used in combination with Thrive Themes Clever Widgets to place specific 
	 * content on specific pages' sidebar. (ie...A single book's book cover, etc)
	 *
	 * @since    1.0.0
     * @access   public
	 */	
	public function action_register_widgets(){
		// load the widget files before we register them
		PathUtil::load_ns_widget("ns-widget-shortcode.php");
		PathUtil::load_ns_widget("ns-widget-image-cta.php");
		PathUtil::load_ns_widget("ns-widget-post-selections.php");

		// once they've been loaded, go ahead and register them.
		register_widget("NS_Widget_Shortcode");
		register_widget("NS_Widget_Image_Cta");
		register_widget("NS_Widget_Post_Selections");
	}

	// ======= MENUS / SUBMENUS ========================================================
	/**
	 * Declutters the admin menu and submenus for most day-to-day users. A user must be 
	 * in the `$show_all_to_users` array to show the full menu. The intention is to only 
	 * provide useful tools and functionality to day-to-day users by removing anything that...
	 * 		1. Wouldn't be used on a daily basis
	 *   	2. Is deemed unnecessary to conduct normal authoring activities
	 *    	3. Is potentially harmful and should only be used for development purposes. 
	 *    	   (ie...Update URLs)
	 *     	4. Is confusing because it shouldn't be used or won't have the desired effect 
	 *     	   anyways. (ie....Appearance > Customize)
	 *
	 * Please note, most menus and submenus can be removed via wp's `remove_menu_page()` 
	 * and `remove_submenu_page` respectively, and we're protected from any potential 
	 * errors. However, this is not always the case. In some cases we have to use php's 
	 * `unset()` to fully remove a menu/submenu – and in these cases we are responsible 
	 * to do the proper error checking.
	 *
	 * IMPORTANT: Please note that this IS NOT enabling or enforcing any strict 
	 * permissions. All of the menus and submenus removed can still be accessed directly 
	 * via their respective URLs. By not enforcing strict permissions we also can enable 
	 * and disable the decluttering without having to change a user's role or individual 
	 * permissions...which would get really annoying.
	 *
	 * @since    1.0.0
     * @access   public
	 */	
	public function action_menu_declutter(){
		// get current user. if not logged in, no need to continue
		$current_user = wp_get_current_user();
		if ($current_user->ID == 0) return; 

		// if current user is not in the "show all" array, declutter menu and submenu items
		if (!in_array($current_user->user_login, Theme::$developers)) {

			// add menus here that can be removed via wp `remove_menu_page()` method...
			$wp_removeable_menus = array(
				"edit.php?post_type=tcb_lightbox", 			// Thrive Lightboxes (use Thrive Leads instead)
				"edit.php?post_type=thrive_optin",			// Thrive Opt-In (use Thrive Leads instead)
				"edit.php?post_type=acf-field-group",		// ACF Pro
				"cptui_main_menu", 							// Custom Post Types UI
			);

			// add submenus here that can be removed via wp `remove_submenu_page()` method...
			$wp_removeable_submenus = array(
				"themes.php" => array(
					"themes.php"							// Appearance > Themes
				),
				"tools.php" => array(
					"tools.php",							// Tools > Available Tools 
					"import.php", 							// Tools > Import
					"export.php", 							// Tools > Export
					"velvet-blues-update-urls.php"			// Tools > Update URLs (Blue Velvet Update URLs)
				),
				"options-general.php" => array(
					"generate-post-thumbnails"				// Settings > Auto Post Thumbnail (potential to kick off a huge process)
				)
			);

			// add any submenus that have to be removed via `unset` because wp's `remove_submenu_page()` won't work...
			$unset_removeable_submenus = array(
				"themes.php" => array(
					"Customize",							// Appearance > Customize
					"Background"							// Appearance > Background
				),
				"tools.php" => array(
					"Migrate DB"							// Tools > Migrate DB (can do a lot of damage if used incorrectly)
				)
			);

			// Remove stuff...
			
			// wp remove menus
			foreach ($wp_removeable_menus as $wp_menu){
				remove_menu_page($wp_menu);
			}

			// wp remove submenus
			foreach ($wp_removeable_submenus as $wp_menu_key => $wp_menu_children){
				foreach ($wp_menu_children as $wp_submenu) {
					remove_submenu_page($wp_menu_key, $wp_submenu);	
				}
			}

			// remove via unset. note, we need a reference to the global `$submenu` array and we need to do the 
			// proper error checking to ensure we are only ever accessing things that exist.
			global $submenu;
			// loop through all unsettable menus...
			foreach ($unset_removeable_submenus as $unset_menu_key => $unset_menu_children){
				// if unsettable menu is in submenu...
				if (array_key_exists($unset_menu_key, $submenu)){
					// loop through all the specific submenu's items...
					foreach ($submenu[$unset_menu_key] as $submenu_key => $submenu_item){
						// if array[0] key exists in submenu item AND the value is in our unsettable submenu list – remove it!
						if (array_key_exists(0, $submenu_item) && in_array($submenu_item[0], $unset_menu_children)){
							unset($submenu[$unset_menu_key][$submenu_key]);
						}
					}
				}
			}
		}
	}
}
