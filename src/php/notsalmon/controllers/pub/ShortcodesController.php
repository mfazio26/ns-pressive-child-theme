<?php

namespace notsalmon\controllers\pub;
use notsalmon\bases\Controller;
use notsalmon\utils\PostTypeUtil;
use notsalmon\renderables\pub\Books_CtaBox;
use notsalmon\renderables\pub\Books_SidebarCoverTitle;
use notsalmon\renderables\pub\Books_SingleInlineCta;
use notsalmon\renderables\pub\Courses_SidebarCoverTitle;
use notsalmon\renderables\pub\Courses_SingleInlineCta;
use notsalmon\renderables\pub\Books_MilCourses_ListingBox;
use notsalmon\renderables\pub\NS_LegacyNotice;
use notsalmon\renderables\pub\Quotes_Embedbox;
use notsalmon\renderables\pub\Posts_AuthorsPick;
use notsalmon\renderables\pub\Optin_2StepCtaTrigger;
use notsalmon\renderables\pub\NS_Colorbox;
use notsalmon\renderables\pub\VideoCourses_ListingBox;

/**
 * This class defines all of our theme-specific shortcodes. Please note, if it is 
 * a shortcode that renders something simple, its ok to define it directly in this 
 * class. However, if it is relatively complex, it might be better to move the logic 
 * into a `Renderable` item or some other class to keep this file relatively clean and 
 * easy to understand.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class ShortcodesController extends Controller {

	/**
     * Registers shortcodes. Overriden and called automatically via `Controller`.
     *
     * @since    1.0.0
     * @access   protected
     */
	protected function register_shortcodes(){
          // these shortcodes can be used by authors in content.
		add_shortcode("ns_last_updated", array($this, "last_updated"));
		add_shortcode("ns_noop", array($this, "noop"));
		add_shortcode("ns_book_cta", array($this, "book_cta"));
          add_shortcode("ns_books_milcourses_listing", array($this, "books_milcourses_listing"));
          add_shortcode("ns_label_simple", array($this, "label_simple"));
          add_shortcode("ns_authorspick", array($this, "posts_authors_pick"));
          add_shortcode("ns_legacy_notice", array($this, "legacy_notice"));
          add_shortcode("ns_2step_optin_trigger", array($this, "optin_2step_trigger"));
          add_shortcode("ns_books_single_inline_cta", array($this, "books_single_inline_cta"));
          add_shortcode("ns_colorbox", array($this, "colorbox_content"));
          add_shortcode("ns_videocourses_listing", array($this, "videocourses_listing"));
          add_shortcode("ns_courses_single_inline_cta", array($this, "videocourses_single_inline_cta"));

          add_shortcode("ns_category_cta", array($this, "category_cta"));

          // these shortcodes are provided for very specific purposes and should not be 
          // used by authors in content. (ie...automatically used in sidebars only)
          add_shortcode("ns_book_cover", array($this, "book_cover"));
          add_shortcode("ns_legacy_course_cover", array($this, "legacy_course_cover"));
          add_shortcode("ns_quote_embedbox", array($this, "quote_embedbox"));

          // third-party: these shortcodes contain potentially unsupported functionality, 
          // and we should keep an eye on these.
          add_shortcode("ns_monarch_share_inline", array($this, "monarch_share_inline"));

          // ThriveCart checkout page only. Requires ACF fields that define the per-product 
          // embed codes.
          add_shortcode("ns_thrivecart_checkout", array($this, "thrivecart_dynamic_checkout"));
	}


     // ======= THRIVECART CHECKOUT =====================================================
     /**
     * Render the embeddable ThriveCart Checkout. This shortcode should only ever by used 
     * on the Shop > Checkout page. In order for this to function properly, there is a 
     * specific ACF repeater field of product info that must be present on this page. The 
     * ACF repeater field holds a unique slug and the embed code required to render the 
     * specific product's checkout.
     *
     * The way this works is – in ThriveCart you add a custom checkout url with a unique 
     * query parameter product slug. Example: /shop/checkout/?tcpid=anxiety-cure. When 
     * the checkout page loads it checks to find a matching slug in the ACF repeater. If 
     * it finds a match, it will then use the embed code field for that product to render 
     * the specific product's checkout.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function thrivecart_dynamic_checkout($atts, $content = null){
          // Generate the context
          $context = shortcode_atts(
               array(),
               $atts,
               "ns_thrivecart_checkout"
          );

          // Holds a contextual error message (if applicable).
          $error_msg = null;

          // Does the ?tcpid= parameter exist and is it populate?
          if (array_key_exists("tcpid", $_GET)){
               // Sanitize the GET parameter value.
               $tcpid = filter_input(INPUT_GET, "tcpid", FILTER_SANITIZE_STRING);
               
               // If it exists and is clean and valid...
               if ($tcpid){
                    // Sanitize it as a "key" and convert to lowercase for comparison.
                    $tcpid = sanitize_key(strtolower($tcpid));

                    // Loop through all the products in the ACF repeater and search for 
                    // a row with the same product slug...
                    if ( have_rows("thrivecart_checkout_products") ){
                         while ( have_rows("thrivecart_checkout_products") ) : the_row();
                              $product_slug = get_sub_field("thrivecart_checkout_product_slug");
                              if ($product_slug){
                                   // Trim and convert to lowercase for comparison.
                                   $product_slug = strtolower(trim($product_slug));

                                   // Compare. If the query parameter matches this product 
                                   // slug, we found a match. Return the corresponding 
                                   // embed code field!
                                   if ($product_slug == $tcpid){
                                        $product_embed = get_sub_field("thrivecart_checkout_product_embed_code");
                                        if ($product_embed){
                                             return $product_embed;
                                        }
                                        // We found a matching product, but the embed code 
                                        // is missing!
                                        else {
                                             $error_msg = "Oops. Something went wrong with preparing your cart. Please try again later.";
                                        }
                                   }
                              }
                         endwhile;

                         // If we made it to the end of the loop, then we didn't find 
                         // a matching product.
                         $error_msg = "Oops. We couldn't find the product you are looking for. It either doesn't exist or it might have been removed. ";
                         $error_msg .= "Please check the url and try again.";
                    }
                    // The ACF field doesn't exist, which means this shortcode is 
                    // embedded on the wrong page. Don't render anything!
                    else {
                         $error_msg = null;
                    }
               }
               // The query parameter ?tcpid either wasn't available or the slug didn't 
               // pass the validation test.
               else {
                    $error_msg = "No product specified. Please check the url and try again.";
               }
          }
          // The ?tcpid= query parameter doesn't exist in the url.
          else {
               $error_msg = "No product specified. Please check the url and try again.";
          }

          // If a contextual error message was defined, render it. If not, don't render 
          // anything. For example, if this shortcode appears on the wrong page, we don't 
          // want to render anything at all.
          return ($error_msg) ? "<p style=\"text-align: center; max-width: 960px; margin-left: auto; margin-right: auto;\">" . $error_msg . "</p>" : "";
     }


	// ======= LAST UPDATED DATE =======================================================
	/**
     * Adds the Post/Page's last modified date as "<p>Last Updated: [Date]</p>" format. 
     * This is primary used on ancillary pages such as disclaimers, terms and conditions, 
     * privacy policy, etc, so we don't ever have to do this manually. 
     *
     * @since    1.0.0
     * @access   public
     * @param 	  Array 			  $atts 		An associative array of attributes, or an empty string if no attributes are given
     * @param    String 			  $content 	The enclosed content (if the shortcode is used in its enclosing form)
     * @return 							HTML output string
     */
	public function last_updated($atts, $content = null){
		extract(shortcode_atts(array(
					"format" => "F j, Y \a\t g:i a", 
					"before" => "<strong>Last updated:</strong> ", 
					"after" => ""
				), 
				$atts
			)
		);
		return "<p>" . the_modified_date($format, $before, $after, 0) . "</p>";
	}

     // ======= LABEL SIMPLE ============================================================
     /**
     * Renders a simple label/tag. Most themes include something like this simple element.
     *
     * @since    1.0.0
     * @access   public
     * @see      `courses/write-your-way-best-seller`
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function label_simple($atts, $content = null){
          $context = shortcode_atts(
               array(
                    "style" => "aqua-blue",                                             // right now, only supports "aqua-blue"
               ),
               $atts,
               "ns_label_simple"
          );
          // if content is defined, return a simple label (no need for a `Renderable` class)
          return ($content) ? ("<div class=\"ns-label-simple ns-label-simple--" . $context["style"] . "\"><span>" . $content . "</span></div>") : "";
     }

	// ======= NOOP ====================================================================
	/**
     * Allows us to add a shortcode that literally outputs nothing. The intention here 
     * is – we can add notes to authors directly inside WYSIWYG editors without effecting 
     * the actual output. For example, we can add...
     * `[ns_noop note="PLEASE DO NOT ADD ANYTHING TO THIS FIELD!"]`
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
	public function noop($atts, $content = null){
		return "";
	}

     // ======= VIDEO COURSES : SIMPLE LISTING BOX ======================================
     /**
     * Renders a simple 3-column Video Courses listing box. Currently, this is only used 
     * on the "/courses" page, and renders ALL the available courses. If, in the future, 
     * we want to use this any place else, we should consider making this shortcode more 
     * robust and customizable.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function videocourses_listing($atts, $content = null){
          // generate the context
          $context = shortcode_atts(
               array(
                    "title" => "",                          // heading title. (string)
                    "article_htag" => "h2",                 // tag to apply to the article item heading (string)
                    "show_price" => 0,                      // 0 to hide price, 1 to show price (int)
                    "cta" => "Get started now!"             // cta button message (string)
               ),
               $atts,
               "ns_videocourses_listing"
          );

          $renderer = new VideoCourses_ListingBox();
          return $renderer->render_to_var($context);
     }

     // ======= VIDEO COURSES : SINGLE INLINE CONTENT CTA ===============================
     /**
     * Renders a single Video Course callout with a title, featured image (in book cover 
     * format), and video course title label. This is intended to be used inside a "post" 
     * to callout a "featured" or "related" video course that navigates straight to that 
     * video course's sales page. You can add a custom heading and choose how to align/justify 
     * the callout with the content - left, right, center. 
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function videocourses_single_inline_cta($atts, $content = null){
          $context = shortcode_atts(
               array(
                    "id" => (PostTypeUtil::is_video_course_page()) ? get_the_ID() : null,      // If video course page, it will default to the current video course id. (int)
                    "title" => "Related Video Course",                                        // heading title (string)
                    "align" => "center",                                                       // in-content alignment (string - "left", "right", or "center"),
               ),
               $atts,
               "ns_courses_single_inline_cta"
          );

          $renderer = new Courses_SingleInlineCTA();
          return $renderer->render_to_var($context);
     }




     // ======= ARTICLE : AUTHORS PICK BOX ==============================================
     /**
     * Renders the Author's Pick box which includes a title ("Karen’s Pick") and an article 
     * title that links to a given article. Please note, this shortcode is intended to be 
     * used automatically inside Article posts by using - `[ns_authorspick]`. This will 
     * automatically use the Advanced Custom Fields inside an article to render the pick. 
     * You can also use this shortcode on any other page, but you will need to specify the 
     * article id manually as a shortcode attribute.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function posts_authors_pick($atts, $content = null){
          // default values
          $post_id = -1;
          $title = "Karen’s Pick";

          // If the current post type is an "article", we need to check the Advanced Custom 
          // Fields to override the defaults. Please note, if you provide `id` or `title` 
          // directly on the shortcode, it will override these fields...which means you can 
          // show multiple different author's pick boxes per page.
          if (PostTypeUtil::is_post_single()) {
               $ids_field = get_field("authorspick_post");
               $post_id = (is_array($ids_field) && (count($ids_field) > 0)) ? $ids_field[0] : -1;

               $title_field = get_field("authorspick_title");
               $title = ($title_field) ? $title_field : $title;
          }

          // generate the context
          $context = shortcode_atts(
               array(
                    "id" => $post_id,         // article id to promote. (int)
                    "title" => $title,        // box title/heading. (ie...Karen’s Pick) (string)
                    "show_image" => 1         // show the featured image or not (boolean - 0 or 1)
               ),
               $atts,
               "ns_authorspick"
          );

          $renderer = new Posts_AuthorsPick();
          return $renderer->render_to_var($context);
     }

     // ======= NS GLOBAL : CONTENT COLOR BOX ===========================================
     /**
     * Renders content inside a theme-able colored box. Authors have a choice of using 
     * pre-defined "themes" (1-4) or determining their own text and background color 
     * scheme by providing the correct variables. 
     * 
     * Please note, by default, themes will take precedence over color variables. So, if 
     * a valid theme number is provided, custom color variables will be ignored. If 
     * custom color values are provided and invalid for any reason, a default color scheme 
     * will be applied (theme 1).
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function colorbox_content($atts, $content = null) {
          // generate the context
          $context = shortcode_atts(
               array(
                    "color" => null,                                  // text hex color (string - ex: "#FF0000")
                    "bgcolor" => null,                                // background hex color (string - ex: "#FF0000")
                    "theme" => -1                                     // preset theme number (int - 1 through 4)
               ),
               $atts,
               "ns_colorbox"
          );

          // add the content to the context
          $context["content"] = $content;

          // render it!
          $renderer = new NS_Colorbox();
          return $renderer->render_to_var($context);
     }

     // ======= OPTIN TRIGGER : 2 STEP CTAS =============================================
     /**
     * Renders a customizable CTA box that will trigger a specific Thrive Leads Lightbox 
     * by id. By default, this shortcode will render the "General Subscribe/Happy Inbox" 
     * lightbox, but it can be configured to open any valid Thrive 2step Lightbox post type.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function optin_2step_trigger($atts, $content = null){
          // generate the context
          $context = shortcode_atts(
               array(
                    "layout" => 2,                               // layout style - (1=inline, 2=nobox, 3=boxed)
                    "style" => 1,                                // text theme style (1=primary)
                    "optin_id" => 49887,                         // thrive 2step optin id
                    "expanded" => 0                              // vertical margins (0=condensed, 1=expanded)
               ),
               $atts,
               "ns_2step_optin_trigger"
          );

          // add the content to the context
          $context["content"] = $content;

          // render it!
          $renderer = new Optin_2StepCtaTrigger();
          return $renderer->render_to_var($context);
     }

     // ======= POST/QUOTE : CATEGORY INLINE CTA ========================================
     /**
     * Renders an in-content CTA driven by a WYSIWYG custom field attached directly to a 
     * category - that belongs to the current post/quote/etc. 
     *
     * The intention is - if we add specific offer/cta shortcodes inside each article or 
     * quote, we'd have to update each individual post every time our primary offer/product 
     * changes...which is a pain.
     *
     * Instead, (1) we can add a generic shortcode [ns_category_cta] inside an article/quote/etc, 
     * (2) WP will automatically find the categories associated with the current post (if 
     * any exist), and (3) render a CTA that has been attached to that category (if one exists). 
     *
     * This way, if a new product/service becomes available that corresponds to "Happiness", 
     * we can change the CTA on the "Happiness" category and ALL posts/quotes/etc that belong 
     * to that category will update automatically.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function category_cta($atts, $content = null){
          // $context = shortcode_atts(array(
          //           "random" => 0
          //      ), 
          //      $atts
          // );

          // Get the current post id
          global $post;
          $post_id = $post->ID;
          // Get the categories assigned to the current post
          $categories = get_the_category($post_id);
          $total_categories = count($categories);
          
          // If there is at least one category...
          if ($total_categories > 0){
               // If there is more than one category, choose a random index. Otherwise, just grab the first one.
               $index = ($total_categories > 1) ? rand(0, $total_categories-1) : 0;
               // Get the WYSIWYG custom field attached directly to the category.
               $cta = get_field("category_incontent_cta", "category_" . $categories[$index]->term_id);
               // If the field exists, return its content.
               if ($cta) {
                    return $cta;
               }
          }
          // For any reason, we didn't find a corresponding valid category cta.
          return "";
     }

     // ======= BOOK : SINGLE INLINE CONTENT CTA ========================================
     /**
     * Renders a single Book callout with a title, book cover image, and book title label. 
     * This is intended to be used inside a "post" to callout a "featured" or "related" 
     * book that navigates straight to that book's page - or optionally, directly to the 
     * preferred retailer url. You can add a custom heading and choose how to align/justify 
     * the callout with the content - left, right, center. Please note, if the preferred 
     * retailer is not set or available, the url will navigate to the NS book page.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function books_single_inline_cta($atts, $content = null){
          $context = shortcode_atts(
               array(
                    "id" => (PostTypeUtil::is_book_single()) ? get_the_ID() : null,       // if book page, it will default to the current book id. (int)
                    "title" => "Related Book",                                            // heading title (string)
                    "align" => "center",                                                   // in-content alignment (string - "left", "right", or "center"),
                    "preferred_retailer" => "auto"                                          // "auto"=first retailer, "amazon"=preferred retailer name, or "none" to use NS book page. (string)
               ),
               $atts,
               "ns_book_cover"
          );

          // null goes to book page, auto goes to first retailer. if not retailers, fallback to book page.

          $renderer = new Books_SingleInlineCta();
          return $renderer->render_to_var($context);
     }

     // ======= BOOK COVER ==============================================================
     /**
     * Renders a specific Book's Cover and Title. Currently, this is only intended to be 
     * used in the sidebar, and as such only renders a sidebar version. However, it is 
     * possible to provide any Book's `id`, and render the sidebar widget for that Book.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function book_cover($atts, $content = null){
          $context = shortcode_atts(
               array(
                    "id" => (PostTypeUtil::is_book_single()) ? get_the_ID() : null,       // if book page, it will default to the current book id.
                    "style" => "sidebar"                                                  // sidebar (currently only supports sidebar!)
               ),
               $atts,
               "ns_book_cover"
          );

          $renderer = new Books_SidebarCoverTitle();
          return $renderer->render_to_var($context);
     }

     // ======= QUOTE EMBEDBOX ==========================================================
     /**
     * Renders a specific Quote's Embed Image Box. Currently, this is only intended to be 
     * used on a single poster post, and as such the default "id" attribute does not 
     * get added automatically. Instead, this is called via the Posters controller.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function quote_embedbox($atts, $content = null){
          $context = shortcode_atts(
               array(
                    "id" => -1,                                                           // quote post id (int)
                    "heading" => "Embed this image on your site.",                        // embed heading/title (string)
               ),
               $atts,
               "ns_quote_embedbox"
          );

          $renderer = new Quotes_Embedbox();
          return $renderer->render_to_var($context);
     }

	// ======= BOOK RETAILER CALL-TO-ACTION ============================================
	/**
     * Renders a specific Book's call-to-action that will create buttons to all the 
     * available retainers that sell/pre-order this Book. Please note, this component is 
     * configurable and can display in a number of ways and places...
     * 		- Can render in `the_content` or as a sidebar widget.
     * 		- It can render the current Book (if applicable) or by id...allowing you to 
     * 		  these CTAs from any other page.
     * 		- It can be configured to include or exclude the Book's cover image if you 
     * 		  want an expanded or condensed box.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
	public function book_cta($atts, $content = null){
		$context = shortcode_atts(
			array(
				"id" => (PostTypeUtil::is_book_single()) ? get_the_ID() : null,		// if book page, it will default to the current book id.
				"cta" => null,												// will override book's cta field.
				"style" => "standard",										// standard, sidebar
				"show_image" => "auto",                                               // auto, 0, 1
                    "classes" => ""                                                       // space separated list of classes
			), 
			$atts, 
			"ns_book_cta"
		);

		$renderer = new Books_CtaBox();
		return $renderer->render_to_var($context);
	}

     // ======= UI : LEGACY NOTICE ======================================================
     /**
     * Renders a simple Legacy Notice. This is intended to be used as a `.ns-legacy-notice` 
     * that informs a user regarding things like Legacy Courses, where they should go to 
     * request access, and a large callout button to MastersInLife.com.
     *
     * Please note, all parts are optional, but at least one part must be provided for the 
     * callout to render.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function legacy_notice($atts, $content = null){
          $context = shortcode_atts(
               array(
                    "heading" => "Important Notice:",                                               // The callout heading/title
                    "cta" => null,                                                                  // Optional. Button label. Will use `cta_url` if not provided.
                    "cta_url" => null,                                                              // Optional. Destination url. If not provided, button will not be rendered.
                    "cta_target" => "_blank",                                                       // _blank | _self
                    "style" => "standard"                                                           // standard (currently only supports standard!)
               ),
               $atts,
               "ns_legacy_notice"
          );
          $context["content"] = $content;

          $renderer = new NS_LegacyNotice();
          return $renderer->render_to_var($context);
     }

     // ======= BOOK COVER ==============================================================
     /**
     * Renders a specific Legacy Course's Cover and Title. Currently, this is only 
     * intended to be used in the sidebar, and as such only renders a sidebar version. 
     * However, it is possible to provide any Legacy Course's `id`, and render the sidebar 
     * widget for that Legacy Course.
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function legacy_course_cover($atts, $content = null){
          $context = shortcode_atts(
               array(
                    "id" => (PostTypeUtil::is_legacy_course_single()) ? get_the_ID() : null,        // if book page, it will default to the current book id.
                    "style" => "sidebar"                                                            // sidebar (currently only supports sidebar!)
               ),
               $atts,
               "ns_book_cover"
          );

          $renderer = new Courses_SidebarCoverTitle();
          return $renderer->render_to_var($context);
     }
    
     // ======= BOOKS AND MIL COURSES LISTING BOX =======================================
     /**
     * Renders a specified list of Books and MastersInLife Courses as well as an optional 
     * list of MIL Course Categories. 
     *
     * @since    1.0.0
     * @access   public
     * @todo     Do we want this to also have options to render in the sidebar?
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function books_milcourses_listing($atts, $content = null){
          $context = shortcode_atts(
               array(
                    "title" => null,                                                           // Optional. Main section title.
                    "ids" => array(),                                                          // Course/Book ids to include in the listing.
                    "columns" => 2,                                                            // Number of columns. Right now, it can only be 2 or 3. Any other number will be 2.
                    "fullwidth" => 0,                                                          // Expand to full width of container? 1=true, 0=false.
                    "boxed" => 1,                                                              // Should this have a "boxed" background container? 1=true, 0=false/transparent.
                    "milcourse_category_ids" => array(),                                       // Optional. MIL Category ids to include as a footer list.
                    "milcourse_category_caption" => null                                       // Optional. A caption that is prepended to the MIL Categories list.
               ),
               $atts,
               "ns_books_milcourses_listing"
          );
          // add $content to our $context array
          $context["content"] = $content;

          $renderer = new Books_MilCourses_ListingBox();
          return $renderer->render_to_var($context);
     }

     // ======= MONARCH INLINE SHARER ===================================================
     /**
     * Renders an inline/in-content Monarch Share component. Monarch does not provided a 
     * shortcut we can use directly, but its easy enough to create our own!
     *
     * @since    1.0.0
     * @access   public
     * @param    Array               $atts        An associative array of attributes, or an empty string if no attributes are given
     * @param    String              $content     The enclosed content (if the shortcode is used in its enclosing form)
     * @return                                    HTML output string
     */
     public function monarch_share_inline($atts, $content = null){
          // if the global monarch variable is defined...
          if (array_key_exists("et_monarch", $GLOBALS) && isset($GLOBALS["et_monarch"])) {
               $monarch = $GLOBALS["et_monarch"];
               // grab the global options
               $monarch_options = $monarch->monarch_options;
               // call the inline method directly!!!
               return $monarch->generate_inline_icons("et_social_inline_bottom");   
          }
     }
}
