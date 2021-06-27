<?php

namespace notsalmon\controllers;
use notsalmon\bases\Controller;
use notsalmon\utils\ThriveThemeUtil;
use notsalmon\utils\PostTypeUtil;

/**
 * This class is intended to contain anything that is "generic" (and public) within our 
 * child theme that is not specific to anything in particular – meaning, the functionality 
 * in this file can be applied to multiple post/page types. If all of the other public 
 * classes' intended purposes are too specific for any potential addition, odds are that 
 * new functionality belongs in this class.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Public_CommonController extends Controller {

	/**
     * Flag that keeps track of if we have enqueued our combined Google Fonts request yet. 
     * We only want to add it once.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean
     */
	private $_google_fonts_rendered = false;

	/**
     * Load and instantiate all dependencies. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function load_dependencies(){
		$posts = new pub\PostsController();
		$posters = new pub\PostersController();
		$books = new pub\BooksController();
		$courses = new pub\CoursesController();
		$milcourses = new pub\MilCoursesController();
		$videocourses = new pub\VideoCoursesController();
		$shortcodes = new pub\ShortcodesController();
	}

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("wp_enqueue_scripts", array($this, "action_enqueue_scripts"), 9999);
		add_action("wp_enqueue_scripts", array($this, "action_enqueue_stylesheets"));
		add_action("wp_loaded", array($this, "action_wp_loaded"));

		// remove theme/plugin google fonts from queue
		add_action("wp_print_styles", array($this, "action_wp_print_styles"), 9999);
		add_action("wp_print_footer_scripts", array($this, "action_wp_print_footer_styles"), -50);
		// yoast: removes opengraph publish dates for single posts
		// add_action("template_redirect", array($this, "action_wp_template_redirect_remove_yoast_publish_date"), 1000);
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		add_filter("excerpt_length", array($this, "filter_excerpt_length"), 9999);
		add_filter("get_the_excerpt", array($this, "filter_get_the_excerpt"), 9999);
		add_filter("the_password_form", array($this, "filter_password_form"));
		add_filter("protected_title_format", array($this, "filter_protected_title_format"));
		// add_filter("the_content", array($this, "filter_single_post_focus_area_bottom"));

		// optins - hardcoded shortcode id
		add_filter("the_content", array($this, "filter_optin_below_post"), 10);
		add_filter("the_content", array($this, "filter_optin_below_page"), 10);
		add_filter("the_content", array($this, "filter_optin_below_custompost"), 20);

		// bugfix: Thrive Post Grids on homepage
		add_filter("the_content", array($this, "filter_inline_style_background_image_noquotes"), 9999);

		// removes yoast opengraph publish dates for non-single pages (if they exist)
		//add_filter("wpseo_opengraph_show_publish_date", "__return_false", 20);
		// add_filter("the_content", array($this, "filter_content_remove_empty_p"), 99999);
	}

	// ======= LOADED =================================================================
	/**
	 * Opportunity to alter plugins after all plugins and themes are loaded. For example, 
	 * we can try and move around the YARRP output order.
	 *
	 * Please note, currently, Thrive Themes does not implement all of its action/filter 
	 * hooks. So, we don't have a good way (yet) to move YARPP 
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function action_wp_loaded(){
		
		// global $yarpp;
		// remove_filter('the_content',        array($yarpp, 'the_content'), 1200);
		// add_filter('the_content',        	array($yarpp, 'the_content'), 9999999999999);
	}

	// ======= SCRIPTS =================================================================
	/**
	 * Thrive Themes Bugfix: A Thrive Ultimatum script loads too soon in the footer, and 
	 * before WordPress has a chance to add its own version of jQuery. So, when the 
	 * Thrive script looks for `jQuery`, it is undefined. 
	 *
	 * Our solution here is to deregister the `jquery` handle, and then re-register it 
	 * in the head. This will (a) make sure we don't get multiple copies of jquery in the 
	 * output, and (b) ensure jQuery is loaded before Thrive actually needs it which 
	 * should eliminate the console js output error.
	 *
	 * Thrive Themes Bugfix: There are inline footer scripts that are explicitly looking 
	 * for `ThriveGlobal.$j` (a reference to jQuery.noConflict()) - but it is defined with 
	 * the individual external Thrive Theme/Plugin scripts. When we optimize and 
	 * concatenate/combine external javascript files using Litespeed Cache Plugin, it adds 
	 * the combined file (that defines ThriveGlobal.$j) BELOW the inline scripts that use 
	 * it...which throws an "undefined" error.
	 * 
	 * The solution is to define `ThriveGlobal.$j` as soon as our "jquery" file is output 
	 * aby attaching it as an inline script.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function action_enqueue_scripts(){
		// define ThriveGlobal.js
		$thrive_global_js = "var ThriveGlobal = ThriveGlobal || { \$j : jQuery.noConflict() };";
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? "" : ".min";

		// remove queued version of jquery
		wp_deregister_script("jquery");

		// add our own and force it into the <head>
		wp_enqueue_script("jquery", "//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js", array(), false, false);

		// attach an inline script to the jquery script tag output!
		wp_add_inline_script("jquery", $thrive_global_js);
	}

	// ======= STYESHEETS ==============================================================
	/**
	 * Ensures our child theme styles are loaded after the parent theme's styles – making 
	 * it a little easier to override them where applicable. Otherwise, our stylesheet 
	 * can load before the parent theme.
	 *
	 * Please note, we are auto-incrementing the `?ver=` with a timestamp that represents 
	 * the last time the file was modified. This acts as a cachebuster every time we 
	 * upload a new css file.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function action_enqueue_stylesheets(){
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? "" : ".min";
		$css_uri = get_stylesheet_directory_uri() . "/style" . $suffix . ".css";
		$css_path = get_stylesheet_directory() . "/style" . $suffix . ".css";
		
		wp_enqueue_style("pressive-style", $css_uri, array("thrive-main-style"), filemtime($css_path));
		// wp_enqueue_style("pressive-style", $css_uri, array("thrive-main-style"), "1.0.10");
	}

	// ======= PRINT STYLESHEETS ======================================================
	/**
	 * Thrive's Pressive Theme, Thrive Architecture and some plugins add individual and 
	 * unnecessary Google Fonts requests. And, in some case, they output duplicates of 
	 * font requests. The intention of this set of requests is to remove all unnecessary 
	 * Google Fonts requests and replace it with only a single combined request that 
	 * contains only the stuff we actually need.
	 *
	 * This method removes all the Google Font requests every time WordPress prints the 
	 * requests. This typically only happens in the `<head>`. However, we'll check to 
	 * make sure we only add our combined request once.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function action_wp_print_styles(){
		if (!is_admin()){
			// remove all google font references in the queue
			$this->_remove_google_fonts_references_from_queue();

			// If we haven't yet added our combined Google Font, add it!
			if (!$this->_google_fonts_rendered) {
				wp_enqueue_style("pressive-child-google-fonts", "//fonts.googleapis.com/css?family=Raleway:400,700,400italic,700italic|Roboto:400,700,400italic,700italic&subset=latin");
			}
		}
	}

	/**
	 * Thrive Architect adds some Google Font requests to the footer. This method removes 
	 * those and doesn't output anything because we've already added our combined request 
	 * in the `<head>`.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function action_wp_print_footer_styles(){
		if (!is_admin()){
			$this->_remove_google_fonts_references_from_queue();	
		}
	}

	/**
	 * Remove all references of Google Fonts from the queue. The way we do this is to 
	 * loop through all the queued stylesheets, and remove the ones with `fonts.google` 
	 * in the source url.
	 *
	 * @since    1.0.0
     * @access   private
	 */
	private function _remove_google_fonts_references_from_queue(){
		global $wp_styles;

		// loop through all queued stylesheets
		foreach ($wp_styles->queue as $style) {
			$link = $wp_styles->registered[$style];

			// if the src url is a google font, remove it from the queue.
			if (strpos($link->src, "fonts.google") !== false) {
				wp_dequeue_style( $link->handle );
			}
		}
	}

	// ======= THRIVE: INLINE STYLE BG IMAGE WITHOUT QUOTES ============================
	/**
	 * Bugfix...kinda. The Thrive Architect Post Grid component (and maybe some others) 
	 * adds the article image as a background image - 'style="background-image: url(myimage.jpg); '. 
	 * However, the image url is added WITHOUT single or double quotes. 
	 * 
	 * When an inline style background image doesn't have single or double quotes, 
	 * Litespeed cache does not recognize this as a background image, and therefore,  
	 * cannot know to replace the jpg|png|gif with an optimized WebP version.
	 *
	 * This filter will find those background images and and add single quotes - making 
	 * it visible to Litespeed cache for optimization.
	 * 
	 * @since    1.0.0
     * @access   public
     * @see 	 homepage - notsalmon.com
	 */
	public function filter_inline_style_background_image_noquotes($content){
		$pattern = '/background-image:\s{1}url\(\s*(\w.+\w)\s*\)/i';
		$str = preg_replace($pattern, "background-image: url('$1')", $content);
		return ($str) ? $str : $content;
	}

	// ======= YOAST: OPEN GRAPH DATES =================================================
	/**
	 * Removes the OpenGraph dates (og:published_date, modified, etc) that Yoast SEO 
	 * plugin adds to the head on single posts. The reason for this is, this is the only 
	 * place that WordPress outputs a "publish date" and it appears that Google Search 
	 * is able to read this date in order to place a date in from of SERP results...which 
	 * we don't want because it makes articles appear "old". This isn't a great idea as 
	 * it can look like "masking" to Google.
	 *
	 * The way this is accomplished is by tapping into the Yoast OpenGraph object and 
	 * removing the registered action that handles outputting the dates. Please note, 
	 * the `priority=19` has to exactly match the Yoast action priority or else this 
	 * won't work as expected...or at all.
	 * 
	 * @since    1.0.0
     * @access   public
	 */
	public function action_wp_template_redirect_remove_yoast_publish_date(){
		// check if the Yoast object exists in the globals array...
		if (array_key_exists("wpseo_og", $GLOBALS)){
			$obj = $GLOBALS["wpseo_og"];
			
			// if it exists, check to see if the action is already registered and remove 
			// it. Please note, the `priority=19` has to be EXACT or else it won't remove 
			// the action.
			if (has_action("wpseo_opengraph", array($obj, "publish_date"))) {
				remove_action("wpseo_opengraph", array($obj, "publish_date"), 19);
			}
		}
	}

	// ======= EXCERPTS ================================================================
	/**
     * Sets the allowed length (in words) of excerpts. Please note the `priority` has to 
     * be crazy-high for this to override what the parent theme is doing.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Int 				  $words 		The numberof words. Default 55.
     * @return   Int                				The updated allowed number of words.
     */
	public function filter_excerpt_length($words){
		return 40;
	}

	/**
     * Removes the excerpt from blog listing and category pages.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 String 			$content 		The currently rendered content string.
     * @return   String                				The updated content string.
     */
	public function filter_get_the_excerpt($content){
		if (is_home() || PostTypeUtil::is_post_category()){
			return "";
		} 
		return $content;
	}

	// ======= CONTENT =================================================================
	/**
	 * Remove empty paragraph tags created by wpautop() 
	 * 
	 * There are better ways to create empty space than adding empty paragraph tags. 
	 * (ie...&nbsp;) However, we want to keep an eye on this to ensure it doesn't cause 
	 * any unintended layout changes, and most importantly, that the RegExps that are run 
	 * are not noticably slowing anything down.
	 *
	 * IMPORTANT: CURRENTLY DISABLED because of a Thrive Themes issue where `<p>` tags 
	 * are wrapped in `<p>` tags – causing this to behave irratically.
	 *
	 * @since    1.0.0
     * @access   public
     * @author   Ryan Hamilton
	 * @link     https://gist.github.com/Fantikerz/5557617
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_content_remove_empty_p($content){
		$value = force_balance_tags($content);
	    $patterns = array(
	    	'/#<p>\s*+(<br\s*\/*>)?\s*<\/p>#i/',
	    	'/~\s?<p>(\s|&nbsp;)+<\/p>\s?~/'
	    );
	    return preg_replace($patterns, "", $value);
	}

	// ======= FILTER : OPT-IN BELOW CONTENT ==========================================
	/**
	 * The following filters render a hard-coded Thrive Leads Opt-In form (general 
	 * subscribe) below various content types – posts, pages and NS custom posts. We 
	 * used to use Thrive's "Focus Areas" functionality to handle this, but we decided 
	 * to move where the opt-in renders higher up the page (on posts), and using the 
	 * "Focus Areas" functionality verbatim no longer works for our purposes. 
	 * 
	 * However, we are still using the Thrive focus area custom meta settings (available 
	 * in posts/pages) to determine if we should allow the opt-in to be rendered at all 
	 * on a specific piece of content. This allows us the add this new opt-in method 
	 * while maintaining EXACTLY where the old method used to render. For example, the 
	 * bottom focus area opt-ins are disbled on the mastersinlife and sales pages.
	 *
	 * Last, the reason for the 3 separate methods is that we need to render each post 
	 * type's opt-in in a slightly different priority level. This is the least amount 
	 * of duplicated code (mostly shared) we could come up with to accomplish this.
	 *
	 * This method renders the opt-in on single "post" types only.
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_optin_below_post($content){
		return $this->_render_common_optin_below_content($content, "post");
	}

	/**
	 * This method renders the opt-in on "page" types only. See the documentation to 
	 * the method above for more information.
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_optin_below_page($content){
		return $this->_render_common_optin_below_content($content, "page");
	}

	/**
	 * This method renders the opt-in on NS custom post types only. See the documentation 
	 * to the method above for more information.
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_optin_below_custompost($content){
		return $this->_render_common_optin_below_content($content, "custompost");
	}

	/**
	 * This is a private helper method to reduce the amount of duplicated code used to 
	 * render the above 3 "opt-in below content" methods who's priority level differs 
	 * based on post type. In order to do that, we need to match the actual post type 
	 * against the "allowed post type" to make sure we don't render the same opt-in 
	 * form multiple times on the same page/post.
	 *
	 * Its important to note that "posts" and "pages" are using the Thrive custom 
	 * bottom Focus Area settings (available in the admin) to determine if we should 
	 * render the opt-in on specific posts/pages. However, Thrive Themes does not support 
	 * these settings on custom post types (nor make them available), so instead, NS custom 
	 * post types (books, quotes, etc) will render the opt-in automatically.
	 *
	 * @since    1.0.0
     * @access   private
	 * @see 	 themes/pressive/functions.php line 201
     * @param 	 String 			  $content 				The currently rendered content string.
     * @param 	 String 			  $allowd_post_type 	The initiator method type - "post", "page", or "customtype".
     * @return   String                						The updated content string.
	 */
	private function _render_common_optin_below_content($content, $allowed_post_type){
		// this is the Thive Leads shortcode we'll render if applicable
		// use this on localhost: [thrive_leads id='49903']
		$optin = "[thrive_leads id='57892']";
		
		// is this the homepage or 404 page? We don't want to render the optin on these pages.
		if (is_home() || is_front_page() || is_404()) return $content;

		// get the post object so we can check the custom Thrive field that enable/disable the "focus areas".
		global $post;
		$custom_fields = get_post_custom($post->ID);

		// is this post a "post" or "page" – AND, is the initiator of this function call the allowed "post" or a "page"?
		// if so, we want to check the Thrive custom fields to see if the bottom focus area is enabled or disabled.
		if ((($allowed_post_type == "post") && PostTypeUtil::is_post_single()) || (($allowed_post_type == "page") && ($post->post_type == "page"))) {
			// if the custom field exists, and its set to "default" (other than "hide", etc), we want to go ahead and render the optin.
			if (isset($custom_fields["_thrive_meta_post_focus_area_bottom"][0]) && ($custom_fields["_thrive_meta_post_focus_area_bottom"][0] === "default")){
				return $content . do_shortcode($optin);
			}
			return $content;
		}
		// if this isn't a post/page with Thrive custom focus area fields, we need to check if this is an NS custom 
		// post type AND if the current initiator of this function call is the allowed "custompost". If so, go ahead 
		// and just render the shortcode. again, there are no custom fields to check so we're always rendering the 
		// shortcode when applicable.
		else {
			if (($allowed_post_type == "custompost") && (PostTypeUtil::is_poster_single() || PostTypeUtil::is_book_single() || PostTypeUtil::is_single_legacy_course())){
				return $content . do_shortcode($optin);
			}
		}

		// if we got this far, just return the content.
		return $content;
	}

	// ======= FILTER : POST BELOW CONTENT FOCUS AREA =================================
	/**
	 * Please see the above `_render_common_optin_below_content` methods that replace 
	 * this functionality. 
	 * 
	 * Update: We are disabling this filter because we now want the bottom focus area 
	 * to render the general subscribe trigger box, but ONLY on post pages. So, this 
	 * functionality (for the time being) is obsolete.
	 *
	 * Previous Documentation:
	 * Thrive Focus Areas does not support Custom Post Types, so we have to override the 
	 * default behavior by duplicating a bunch of Thrive code...regrettably. This method
	 * simply checks if we are in one of our NS Custom Post Types and attempts to render 
	 * the bottom Focus Area, if one exists.
	 *
	 * @since    1.0.0
     * @access   public
	 * @see 	 themes/pressive/functions.php line 201
	 * @see      themes/pressive/functions.php line 316
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_single_post_focus_area_bottom($content){
		// if this is one of our custom post types that should be considered to be "just like a post"...
		if (PostTypeUtil::is_poster_single() || PostTypeUtil::is_book_single() || PostTypeUtil::is_single_legacy_course()) {
			// note: this check returns true, but not sure its always accurate to "posts"...
			if (thrive_check_bottom_focus_area()){
				// render focus area template to buffer
				ob_start();
				ThriveThemeUtil::render_top_focus_area("bottom"); // see this method for more information
				$focus_area = ob_get_contents();
				ob_end_clean();
		        // add the focus area to the content
		        return $content . $focus_area;
			}
		}
		return $content;
	}

	// ======= PASSWORD PROTECTED ======================================================
	/**
	 * Strips the "Protected:" part of the title that is added automatically to 
	 * password-protected pages and posts. In most of our scenarios, we are adding our 
	 * own identifiers directly in the post title, such as – "Access Now:".
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $format 		The password-protected title format.
     * @return   String                				The updated password-protected title format.
	 */
	public function filter_protected_title_format($format){
		return "%s";
	}

	/**
	 * Replaces the standard message shown on locked password-protected pages. The 
	 * intention here is – we are supporting legacy access to some course materials, but 
	 * we are no longer supporting user logins (emails, passwords). So, we need are 
	 * simply hiding this content behind generic passwords. In time, depending on the 
	 * Google Analytics, we may choose to phase out these pages entirely.
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $output 		The password form HTML output.
     * @return   String                				The updated password form HTML output.
	 */
	public function filter_password_form($output){
		$message = "<strong>This content is password protected.</strong><br> " . 
					"To view it please enter your password below. " . 
					"If you do not have a password, you may need to request one. " . 
					"<a href=\"/account/\">Learn more and gain access now!</a>";

		return str_replace(
			"This content is password protected. To view it please enter your password below:",
			$message,
			$output
		);
	}
}
