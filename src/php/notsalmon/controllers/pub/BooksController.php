<?php

namespace notsalmon\controllers\pub;
use notsalmon\bases\Controller;
use notsalmon\utils\PostTypeUtil;
use notsalmon\utils\ThriveThemeUtil;
use notsalmon\renderables\pub\Books_RelatedBox;

/**
 * This class defines functionality specific to and available on Single Book and Book 
 * Archives pages.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class BooksController extends Controller {

	/**
     * Registers actions. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_actions(){
		add_action("pre_get_posts", array($this, "action_archive_posts_per_page"));
		// add_action("tha_html_before", array($this, "action_single_remove_author_box"));
	}

	/**
     * Registers filters. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_filters(){
		add_filter("wp_get_attachment_image_src", array($this, "filter_archive_thumbnail_size"), 9999, 4);
		add_filter("the_content", array($this, "filter_single_content_after"), 0);
	}

	// ======= ARCHIVE : POSTS PER PAGE ================================================
    /**
     * Sets the number of book posts per page on an archive page. Please note, we are 
     * using the built-in `$query` test methods instead of our `PostTypeUtil` helper. We 
     * could use either, but this seems like a better use.
     *
     * @since    1.0.0
     * @access   public
     * @param    WPQuery  $query         The current WP query object.
     */
	public function action_archive_posts_per_page($query){
		if (!is_admin() && $query->is_post_type_archive("book") && $query->is_main_query()){
			$query->set("posts_per_page", 20);
		}
	}

	// ======= ARCHIVE : THUMBNAIL SIZE ================================================
    /**
     * Changes which thumbnail/image size to use for book archive items. By default, 
     * Thrive Themes wants to use the "tt_grid_layout" size for all archive items, but 
     * we want our book cover size on the book archive page(s).
     *
     * @since    1.0.0
     * @access   public
     * @param    array  $image         Either array with src, width & height, icon src, or false.
     * @param    int 	$attachment_id Image attachment ID.
     * @param    string $size          Size of image. Image size or array of width and height values (in that order). Default 'thumbnail'.
     * @param    bool   $icon          Whether the image should be treated as an icon. Default false.
     * @return   array                 Returns an array (url, width, height, is_intermediate), or false, if no image is available.
     */
	public function filter_archive_thumbnail_size($image, $attachment_id, $size, $icon){
		// if this is a book archive and a grid layout item, then we know we need to 
		// change which image Thrive wants to use as the thumbnail
		if (PostTypeUtil::is_book_archive() && ($size == "tt_grid_layout")) {
			// get the current post
			global $post;

			// if the current archive post is set and it is a book (which it always should be), 
			// then its ok to change the image to our book cover size!
			if (isset($post) && PostTypeUtil::is_book_single($post->ID)) {
				return image_downsize( $attachment_id, "ns-book-related" );
			}
		}

		// otherwise, just return the un-filtered image array.
		return $image;		
	}
	
	// ======= SINGLE : CONTENT ========================================================
	/**
     * Renders our related books/courses and available book retailers below the content.
     *
     * @since    1.0.0
     * @access   public
     * @param    String 			  $content   	The currently rendered content string.
     * @return 	 String 							The updated content string.
     */
	public function filter_single_content_after($content){
		if (PostTypeUtil::is_book_single()){
			// related books and courses
			$related_renderer = new Books_RelatedBox();
			$related = $related_renderer->render_to_var(array("title" => "You may also like..."));
			// let the cta shortcode do the hard work instead of configuring this manually!
			$retailers = do_shortcode("[ns_book_cta style=\"standard\" show_image=\"auto\"]");
			return $content . $retailers . $related;
		}
		return $content;
	}
	
	// ======= SINGLE : AUTHOR BOX =====================================================
	/**
	 * Currently disabled.
     * Removes the Author Box at the bottom of the content. We accomplish this by 
     * setting a Thrive Theme Option at runtime.
     *
     * @since    1.0.0
     * @access   public
     * @see themes/pressive/single.php on line 173.
     */
	public function action_single_remove_author_box(){
		if (PostTypeUtil::is_book_single()){
			ThriveThemeUtil::set_option("bottom_about_author", false);
		}
	}
}
