<?php

namespace notsalmon\controllers\pub;
use notsalmon\bases\Controller;
use notsalmon\utils\PostTypeUtil;
use notsalmon\utils\ThriveThemeUtil;
use notsalmon\renderables\pub\Posters_TagListBox;
use notsalmon\renderables\pub\Posters_ArchiveIntro;


/**
 * This class defines functionality specific to and available on Single Poster and 
 * Poster Archive pages.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class PostersController extends Controller {

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("tha_html_before", array($this, "action_single_register_monarch_type"));
		add_action("tha_content_top", array($this, "action_archive_intro"));
		add_action("tha_content_top", array($this, "action_archive_tag_description"), 20);
		add_action("tha_content_top", array($this, "action_archive_render_tag_list"));
		// important: this WP action acts like a filter - and requires a parameter and return 
		// value to function properly. I don't know why that is.
		add_action("the_content", array($this, "action_archive_customize_readmore_btn"));
		// deprecated: 11/24/18
		// add_action("tha_html_before", array($this, "action_single_show_featured_image"));
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		add_filter("get_the_excerpt", array($this, "filter_excerpt"), 10);
		add_filter("the_content", array($this, "filter_single_render_image_thrive_featured"), 1);
		add_filter("the_content", array($this, "filter_single_render_image_caption"), 1);
		add_filter("the_content", array($this, "filter_single_render_download_box"), 9);
		add_filter("the_content", array($this, "filter_single_render_embed_box"), 10);
		add_filter("the_content", array($this, "filter_single_render_tag_list"), 20);
		add_filter("wp_get_attachment_metadata", array($this, "filter_single_featured_image_metadata"), 20, 2);
	}

	// ======= SINGLE : MONARCH-ENABLED FEATURED IMAGE ==================================
	/**
	 * Allows the "poster" custom post type to be used in Monarch's Social Media Sharing 
	 * on-media plugin so share buttons can be displayed over the top of the image. By 
	 * default, Monarch only supports "post" types. But, by adding our type to the list, 
	 * we can hack Monarch into auto-parsing different post types, too.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function action_single_register_monarch_type(){
		if (PostTypeUtil::is_poster_single()){
			// if the Monarch plugin exists and is registered to globals...
			if (array_key_exists("et_monarch", $GLOBALS)) {
				$obj = $GLOBALS["et_monarch"];
				// add the poster post type to its list of allowed types
				$obj->monarch_options["sharing_media_post_types"][] = "poster";
			}
		}
	}

	// ======= SINGLE : FEATURED IMAGE =================================================
	/**
	 * DEPRECATED 11/24/18: 
	 * We're no longer using this method to add a quote as a featured image. But, we're 
	 * leaving it here for reference.
	 * 
	 * Shows the poster's featured image (because its the actual poster). We need to do 
	 * this manually because the display of featured images associated with "posts" is 
	 * currently turned off – per the design and convention of the site. Because a 
	 * Poster's featured image IS the actual poster, we need to turn it back on. We 
	 * accomplish this by setting a Thrive Theme Option at runtime.
	 *
	 * @since    1.0.0
     * @access   public
	 * @see themes/pressive/single.php on line 39-40.
	 */
	public function action_single_show_featured_image(){
		if (PostTypeUtil::is_poster_single()){
			ThriveThemeUtil::set_option("featured_image_style", "wide");
		}
	}

	// ======= ARCHIVE / SINGLE : EXCERPT ==============================================
	/**
	 * Removes the poster excerpt from ever showing - because there isn't really any 
	 * actual text content on these post types.
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_excerpt($content){
		if (PostTypeUtil::is_poster_archive() || PostTypeUtil::is_poster_single()){
			return "";
		}
		return $content;
	}

	// ======= SINGLE : THRIVE FEATURED IMAGE ==========================================
	/**
	 * Adds the corresponding Thrive "featured image" (`.fwit`) at the very top of a 
	 * single quote post. The reason we are doing it this way is – we need the `.fwit` 
	 * to be within the `the_content` filtered content so that Monarch will automatically 
	 * apply its on-media sharing buttons. If we did this the old way where Thrive adds 
	 * its `.fwit` outside `the_content`, the Monarch would not be able to find the image.
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_single_render_image_thrive_featured($content){ 
		if (PostTypeUtil::is_poster_single()){
			// get the Thrive version of the featured image.
			$featured_img = thrive_get_post_featured_image( get_the_ID(), "wide");
			// get the img attributes
			$img_src = $featured_img["image_src"];
			$img_alt = $featured_img["image_alt"];
			$img_title = $featured_img["image_title"];
			// create the image tag
			$img = "<div class=\"fwit\">" .
				        "<img src=\"" . $img_src . "\" alt=\"" . $img_alt . "\" title=\"" . $img_title . "\" />" . 
				   "</div>";

			return $img . $content;
		}
		return $content;
	} 

	// ======= SINGLE : FEATURED IMAGE CAPTION =========================================
	/**
	 * Adds caption (if it exists) below the featured quote poster image. Please note, 
	 * the caption data is attached directly to the image in the Media Uploader/Browser. 
	 * If it doesn't exist, not caption will be output.
	 *
	 * The intention here is – we should always be typing out in "text" what the poster 
	 * says. This helps both with accessibility and SEO – to varying degrees.
	 *
	 * Please note, we have to account for "body" copy being attached to the quote entry. 
	 * However, the body copy will come immediately after the featured image - followed 
	 * by the caption. In order to put the body copy below the caption, we have to split 
	 * the content into an array - based on the closing </div> that wraps the image.
	 *
	 * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
	 */
	public function filter_single_render_image_caption($content){
		if (PostTypeUtil::is_poster_single()){
			// get the caption attached to the featured image (if it exists)
			$caption = get_the_post_thumbnail_caption(get_the_ID());
			
			// if it exists, render the caption
			if ($caption) {
				// split the array at the image's closing div tag.
				$content_arr = explode("</div>", $content, 2);
				$caption = "<p class=\"wp-caption-text wp-caption-text--quotesingle\">“" . $caption . "”</p>";

				// If we have more than 1 segment, it means we probably have an entry 
				// body description that needs to come after the caption.
				if (count($content_arr) > 1){
					return $content_arr[0] . "</div>" . $caption . $content_arr[1];
				}
				// If we only got 1 segment, then there's nothing that comes after the 
				// closing </div>, and its ok to output the caption after the content.
				return $content . $caption;
			}
		}
		return $content;
	}

	// ======= SINGLE : EMBED BOX ======================================================
	/**
     * Renders the Poster's Embed Image box (via shortcode) that allows a user to embed 
     * the image as a link that points back to NS. Please note the `priority` value. We 
     * need to ensure this comes after the Monarch post share bar.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
     */
	public function filter_single_render_embed_box($content){
		$embedbox = "";
		if (PostTypeUtil::is_poster_single()){
			$embedbox = do_shortcode("[ns_quote_embedbox id=\"" . get_the_ID() . "\"]");
		}
		return $content . $embedbox;
	}
	
	// ======= SINGLE : SHARE/DOWNLOAD BOX =============================================
	/**
     * Renders the Poster's Share/Download box describes how a user can download an share 
     * the image, as well as providing links to download directly or open the image in a 
     * new browser window. Please note the `priority` value. We need to ensure this comes 
     * after the content, but immediately before the Monarci post share bar.
     *
     * @todo - This could be moved into a Renderable class to keep this a little cleaner.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
     */
	public function filter_single_render_download_box($content){
		if (PostTypeUtil::is_poster_single()){
			$post_id = get_the_ID();
			// get the image id
			$image_id = get_post_thumbnail_id($post_id);
		 	// get the image url, width, and height. (should be 640x640 based on poster-image preset)
			$image_data = wp_get_attachment_image_src($image_id, "ns-poster-image");
			$image_url = $image_data[0];

			// HTML Template
			$downloadbox = "<aside class=\"ns-poster-downloadbox\">";
				$downloadbox .= "<h3 class=\"ns-poster-downloadbox__heading\">Share the positivity!</h3>";
				$downloadbox .= "<p class=\"ns-poster-downloadbox__description\">Download the high resolution image to share on Instagram. Or save the image as wallpaper for your mobile device. Plus, you can use the embed code (below) to add this image to your website. Please always be sure to include attribution to <em>NotSalmon.com</em>. Thank you!</p>";
				$downloadbox .= "<div class=\"ns-poster-downloadbox__links\">";
					$downloadbox .= "<a href=\"" . $image_url . "\" download>Download high-resolution image</a>";
					$downloadbox .= "<a href=\"" . $image_url . "\" target=\"_blank\" rel=\"noopener\">Open image in new window</a>";
				$downloadbox .= "</div>";
			$downloadbox .= "</aside>";

			return $content . $downloadbox;
		}
		return $content;
	}

	// ======= SINGLE : CONTENT ========================================================
	/**
     * Renders the Posters Tag List component at the end of the content. Please note the 
     * `priority` value. We need to ensure this comes before and after the correct theme 
     * and plugin components.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 String 			  $content 		The currently rendered content string.
     * @return   String                				The updated content string.
     */
	public function filter_single_render_tag_list($content){
		if (PostTypeUtil::is_poster_single()){
			$renderer = new Posters_TagListBox();
			return $content . $renderer->render_to_var(array("is_archive" => false));
		}
		return $content;
	}

	// ======= ARCHIVE : INTRODUCTION ==================================================
	/**
	 * Renders the Quotes Intro on archive pages. Please note, the "general" version of 
	 * this section is populated via an Advanced Custom Fields Options page attached to 
	 * "Quotes > Quotes Settings".
     *
     * @since    1.0.0
     * @access   public
     */
	public function action_archive_intro(){
		if (PostTypeUtil::is_poster_archive()){
			// If this isn't a tag archive, or its just a general archive page, attempt 
			// to render the general intro - if it is enabled.
			$enabled = get_field("quotes_archive_intro_enabled", "option");

			if ($enabled){
				// get the field values
				$headline = get_field("quotes_archive_intro_headline", "option");
				$content = get_field("quotes_archive_intro_content", "option");

				// attempt to render it!
				$renderer = new Posters_ArchiveIntro();
				$renderer->render(array(
					"headline"	=> $headline,
					"content" 	=> $content
				));
			}
		}
	}

	// ======= ARCHIVE : TAG DESCRIPTION ===============================================
	/**
	 * Renders the Tag description copy (below the tag list) on Poster Tag Archive pages.
	 * Please note, this is optional. If a description does not exist on a particular 
	 * tag, nothing will be rendered.
     *
     * @since    1.0.0
     * @access   public
     */
	public function action_archive_tag_description(){
		if (PostTypeUtil::is_poster_archive() && is_tax("poster_tag")){
			// Get the description - if it exists.
			$description = tag_description();

			// If the description exists, render it. Please note, we're rendering this 
			// as a "div" instead of the default "aside" because this directly relates 
			// to the content on the page.
			if ($description && (trim($description != ""))) {
				$renderer = new Posters_ArchiveIntro();
				$renderer->render(array(
					"headline" => strip_tags($description, "<br><a><span><em><i>"),
					"content" => null,
					"tagtype" => "div"
				));
			}
		}
	}

	// ======= ARCHIVE : TAG LIST ======================================================
	/**
     * Renders the Posters Tag List component at the top of the archive page. Please note 
     * that this is the same tag list component that renders on a single page, however, 
     * the template itself is smart enough to know how to adapt itself to the different 
     * scenarios.
     *
     * @since    1.0.0
     * @access   public
     */
	public function action_archive_render_tag_list(){
		if (PostTypeUtil::is_poster_archive()){
			$renderer = new Posters_TagListBox();
			$renderer->render(array("is_archive" => true));
		}
	}

	// ======= ARCHIVE : READ MORE BUTTON ==============================================
	/**
     * Alters the standard listing item's "read more" button to add a custom label. 
     * Please note, because this is setting a Thrive Theme Option, we have to set this 
     * at just the right time (ie...action) or else it won't work.
     *
     * IMPORANT: I don't know why, but the the_content ACTION acts like a filter. It has 
     * a parameter and requires a return value....which is unlike any other WP action!!!
     *
     * @since    1.0.0
     * @access   public
     */
	public function action_archive_customize_readmore_btn($content){
		if (PostTypeUtil::is_poster_archive() || is_tax("poster_tag")){
			ThriveThemeUtil::set_option("other_read_more_text", "Share high-resolution image!");
		}
		return $content;
	}

	// ======= ARCHIVE : FEATURED IMAGE ================================================
	/**
     * This filter addresses and issue where, for some reason, Thrive and WordPress are 
     * rendering our newer Poster featured images at the wrong sizes. This may be due to 
     * a recent update, but its hard to tell. Or, potentially a change in WordPress's 
     * `add_image_size()` soft proportion method.
     *
     * Archive images should be rendered at 300x300, but are rendering at 360x300 – and, 
     * Single images should be rendered at 570x570, but are rendering at 980x570. And, 
     * also for some unknown reason, older posts seem to be uneffected.
     *
     * To solve the problem (temporarily), we're simply re-assigning the image size 
     * transformation that Thrive attempts to apply to each to a size that we know is 
     * "the most" correct. Also, depending on when (what year and website version) the 
     * image was created, its possible that some transforms/sizes do not exist for that 
     * particular image. In those cases, we need to find the largest possible existing 
     * image transform to apply.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array|Boolean 		$content 		Array of meta data for the given attachment, or false if the object does not exist.
     * @param 	 Int 				$post_id 		Attachment ID.
     * @return   Array                				The filtered attachment metadata array.
     */
	public function filter_single_featured_image_metadata($data, $post_id){
		// if single poster page...
		// for some reason, newer posts are displaying featured images at 980x570 instead 
		// of 570x570.
		if (PostTypeUtil::is_poster_single()){
			global $post;

			// to ensure we're resizing the right image, we need to check that the image 
			// metadata id matches the current post's thumbnail id – because there are 
			// many images that get tested on this page...
			if (get_post_thumbnail_id($post) == $post_id){

				// If the data object is populated and has the sizes key, we can look for 
				// the largest available transform...
				if ($data && array_key_exists("sizes", $data)){
					$largest_size_key = null;

					// 768x768
					if (array_key_exists("medium_large", $data["sizes"])){
						$largest_size_key = "medium_large";
					}
					// 640x640
					else {
						if (array_key_exists("ns-poster-image", $data["sizes"])){
							$largest_size_key ="ns-poster-image";
						}
					}

					// If we found a largest size key, temporarily apply it to the Thrive options.
					if ($largest_size_key){
						$data["sizes"]["tt_featured_wide_full"] = $data["sizes"][$largest_size_key];	
					}
				}
			}
			return $data;
		}

		// if archive poster page...
		// for some reason, newer posts are displaying featured images at 360x300 instead 
		// of 300x300.
		if (PostTypeUtil::is_poster_archive()){

			// to ensure we're checking and resizing currently, we need to make sure that 
			// the image metadata id matches current loop post's thumbnail id.
			if (get_post_thumbnail_id(get_the_ID()) == $post_id){

				// If the data object is populated and has the sizes key, we can look for 
				// the largest available transform...
				if ($data && array_key_exists("sizes", $data)){
					// Default size. This has to exist. (300x300)
					$largest_size_key = "medium";

					// 768x768
					if (array_key_exists("medium_large", $data["sizes"])){
						$largest_size_key = "medium_large";
					}
					// 640x640
					else {
						if (array_key_exists("ns-poster-image", $data["sizes"])){
							$largest_size_key ="ns-poster-image";
						}
					}
					// Temporarily apply the largest size key available. 
					$data["sizes"]["tt_grid_layout"] = $data["sizes"][$largest_size_key];
				}
			}
			return $data;
		}
		return $data;
	} 
}
