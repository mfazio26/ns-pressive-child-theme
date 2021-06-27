<?php

namespace notsalmon\controllers\pub;
use notsalmon\bases\Controller;
use notsalmon\utils\PostTypeUtil;
use notsalmon\renderables\pub\Posts_CategoryIntroBox;

/**
 * This class defines functionality specific to and available on Single Post and Post 
 * Archives pages – which is the "default" Post Type in WordPress (ie...our essay)
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class PostsController extends Controller {

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("tha_content_top", array($this, "action_category_render_introbox"));
		add_filter("dsq_before_comments", array($this, "action_disqus_anchor"), 9999);
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		// PubExchanged: Removed 06.01.19
		// add_filter("the_content", array($this, "filter_single_render_pubexchange"), 1300);
		
		add_filter("the_content", array($this, "filter_post_comments_cta"), 1200);
		
	}

	// ======= CATEGORY : DESCRIPTION ==================================================
	/**
	 * Renders the Category Image/Description at the top of a Post Category page listing.
	 * (ie...notsalmon.com/category/love)
	 */
	public function action_category_render_introbox(){
		if (PostTypeUtil::is_post_category()){
			$renderer = new Posts_CategoryIntroBox();
			$renderer->render();
		}
	}

	// ======= SINGLE : COMMENTS =======================================================
	/**
	 * Renders a hidden anchor directly above the comments section of a single post. The 
	 * intention is that we can use this anchor to jump/scroll to the comment sections 
	 * via a hyperlink.
	 */
	public function action_disqus_anchor(){
		if (PostTypeUtil::is_post_single()){
			echo "<a id=\"disqus_comments\" class=\"ns-post-comments-anchor\"><span>Comments area<span></a>";
		}
	}

	// ======= SINGLE : COMMENTS CTA ===================================================
	/**
	 * Renders a CTA immediately after the article content. This CTA is intended to 
	 * provide a place to add a "Join the conversation in the comments area below!" 
	 * message with a hyperlink that will jump immediately to the comments section. There 
	 * is also an ACF options page added to the Posts section of the Admin so this 
	 * message can be enabled/disabled and customized.
	 *
	 * Please note, the design is ultimately determined by the markup in the options 
	 * page's message field...which can be HTML.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
     */
	public function filter_post_comments_cta($content){
		if (PostTypeUtil::is_post_single()){
			$enabled = get_field("articles_comments_cta_enabled", "option");
			$msg = get_field("articles_comments_cta_message", "option");

			if ($enabled && $msg){
				$el = "<aside class=\"ns-post-comments-cta\">" . $msg . "</aside>";
				return $content . $el;
			}
		}
		return $content;
	}

	// ======= SINGLE : PUBEXCHANGE WIDGET =============================================
	/**
	 * Renders the PubExchange (third-party service) below the "Related Posts" on a post 
	 * page. Please note, the async script that goes with this is added right before 
	 * the closing `</body>` tag, and that is implemented in Thrive Theme's Dashboard > 
	 * Theme Options > Scripts settings.
	 *
	 * The design of the widget appears to be handled on the PubExchange side.
	 * 
     *
     * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
     */
	public function filter_single_render_pubexchange($content){
		if (PostTypeUtil::is_post_single()){
			$div = "<div class=\"pubexchange_module\" id=\"pubexchange_below_content\" data-pubexchange-module-id=\"2872\"></div>";
			return $content . $div;
		}
		return $content;
	}
}
