<?php 

namespace notsalmon\utils;
use notsalmon\bases\Facade;
use notsalmon\utils\PostTypeUtil;

/**
 * This class is the only place that we should be adding experimental or unsupported 
 * Thrive Themes functionality. The intention is – if in the future Thrive Themes 
 * makes a more robust API accessible, adds more filters/hooks, etc – this is the only 
 * file we have to edit to remove our "risky" code.
 *
 * Setters / Getters
 * This class provides access to Thrive Theme `$options` variable that are set per-page 
 * at runtime by finding the global variable that is set within the Thrive Theme 
 * template files. Its obviously less-than-ideal that we have to resort to accessing 
 * and editing this variable directly, and even less ideal that they used something as 
 * generic as `$options`, but this is the best way (currently) to override the dictated 
 * display on a per-page basis. Hopefully, this necessity will become obsolete with 
 * future Thrive Themes releases and updates.
 *
 * Please note, this class makes use of the `Facade` class. Please read the `Facade` 
 * documentation for more information.
 *
 * Facade Example:
 * To call `$instance->__set_option()`, instead you can `ThriveThemeUtil::set_option()`. 
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class ThriveThemeUtil extends Facade {
	
	// =================================================================================
    // ======= SETTERS / GETTERS =======================================================
    // =================================================================================
	/**
     * Gets the current global Thrive Themes `$options` variable that is defined at the 
     * top of each template (if it exists). Please note, this is an instance method and 
     * must be called differently that our standard `Facade` interface. In most scenarios, 
     * you are probably better off just using the `ThriveThemeUtil::set_option()` method.
     * 
     * @since    1.0.0
     * @access   public
     * @example  
     * 			 $util = ThriveThemeUtil::get_instance();
     * 			 $opts = &util->get_options();
     * 			 $opts["featured_image_style"] = "wide";
     * 			 
     * @param  	 String 			  $key 			The key in the `$options` variable to set.
     * @param    [Mixed] 			  $value 		The value to set.
     */
	public function &get_options(){
		global $options;
		return (isset($options)) ? $options: null;
	}

	/**
     * Sets a value directly on the current Thrive Themes global `$options` variable 
     * that is defined at the top of each template (if it exists).
     * 
     * @since    1.0.0
     * @access   protected
     * @example  ThriveThemeUtil::set_option("featured_image_style", "wide");
     * @param  	 String 			  $key 			The key in the `$options` variable to set.
     * @param    [Mixed] 			  $value 		The value to set.
     */
	protected function __set_option($key, $value){
		global $options;

		if (isset($options, $key, $value)){
			$options = array_merge($options, array($key => $value));
		}
	}

     /**
     * Checks to see if a Thrive "Focus Area" exists that should be shown "between blog 
     * posts". The reason we have to do this is because Thrive only allows you to put a 
     * focus area "between" when you are using a full-width layout and ONLY on the main 
     * blog page...not a category archive. So, we are circumventing this by proxying 
     * the `partials/content-grid.php` file and having that file do the checking instead.
     * 
     * @since    1.0.0
     * @see themes/pressive/functions.php line 179
     * @see themes/pressive-child/src/php/partials/content-grid.php
     * @access   protected
     * @example  ThriveThemeUtil::check_blog_between_focus_area("featured_image_style", "wide");
     * @param    Int                      $postion     The index of the current archive post.
     * @return   Boolean                               True if this post should be followed by a "between focus area". False if not.
     */
     protected function __check_blog_between_focus_area($position){
          // note: this is what is stopping it from rendering on category pages!
          // if ( ! is_home() ) {
          //   return false;
          // }

          $query       = new \WP_Query( "post_type=focus_area&meta_key=_thrive_meta_focus_display_location&meta_value=between_posts&order=ASC" );
          $focus_areas = $query->get_posts();

          foreach ( $focus_areas as $temp_focus ) {
               $post_custom_atr = get_post_custom( $temp_focus->ID );
               if ( isset( $post_custom_atr['_thrive_meta_focus_display_between_posts'] ) && isset( $post_custom_atr['_thrive_meta_focus_display_between_posts'][0] ) && $post_custom_atr['_thrive_meta_focus_display_between_posts'][0] == $position ) {
                    return true;
               }
          }
          return false;
     }

     /**
     * Renders the top/bottom Thrive Focus Area. Please note, this is almost an exact copy 
     * of the Thrive function `thrive_render_top_focus_area()` except that we have added and/or 
     * allowed our set of additional Custom Post Types about half-way down the function. Thrive's 
     * `_thrive_check_top_focus_area_post()` works as expected so THIS method was the only thing 
     * that was stopping us from just being able to use the default behavior. If, in the future, 
     * Thrive adds support for Custom Post Types, we will probably start seeing "duplicate focus 
     * areas" being rendered – in which case, we should remove this function and any calls to 
     * it. (ie...PublicCommonController > filter_single_post_focus_area_bottom)
     * 
     * @since    1.0.0
     * @see themes/pressive/functions.php line 316
     * @access   protected
     * @example  ThriveThemeUtil::render_top_focus_area("bottom");
     * @param    String                      $postion       The focus area id ("top", "bottom", "between_posts")
     * @param    String                      $place         What page/post to check. ("blog", "archive")
     */
     protected function __render_top_focus_area($position = "top", $place = null){
          global $post;
          $current_post        = $post;
          $page_focus          = null;
          $post_focus          = null;
          $current_focus       = null;
          $current_focus_attrs = null;

          $custom_fields = get_post_custom( $post->ID );

          if ( $position == "top" ) {
               if ( isset( $custom_fields['_thrive_meta_post_focus_area_top'][0] ) && is_numeric( $custom_fields['_thrive_meta_post_focus_area_top'][0] ) && get_post( $custom_fields['_thrive_meta_post_focus_area_top'][0] ) && $post->post_type == "post" ) {
                    $post_focus = get_post( $custom_fields['_thrive_meta_post_focus_area_top'][0] );
               }

               if ( isset( $custom_fields['_thrive_meta_post_focus_area_top'] ) && is_numeric( $custom_fields['_thrive_meta_post_focus_area_top'][0] ) && get_post( $custom_fields['_thrive_meta_post_focus_area_top'][0] ) && $post->post_type == "page" ) {
                    $page_focus = get_post( $custom_fields['_thrive_meta_post_focus_area_top'][0] );
               }
          } else {
               if ( isset( $custom_fields['_thrive_meta_post_focus_area_bottom'][0] ) && is_numeric( $custom_fields['_thrive_meta_post_focus_area_bottom'][0] ) && get_post( $custom_fields['_thrive_meta_post_focus_area_bottom'][0] ) && $post->post_type == "post" ) {
                    $post_focus = get_post( $custom_fields['_thrive_meta_post_focus_area_bottom'][0] );
               }

               if ( isset( $custom_fields['_thrive_meta_post_focus_area_bottom'] ) && is_numeric( $custom_fields['_thrive_meta_post_focus_area_bottom'][0] ) && get_post( $custom_fields['_thrive_meta_post_focus_area_bottom'][0] ) && $post->post_type == "page" ) {
                    $page_focus = get_post( $custom_fields['_thrive_meta_post_focus_area_bottom'][0] );
               }
          }

          if ( ! $post_focus ) {
               $post_categories = wp_get_post_categories( $post->ID );
               $query1          = new \WP_Query( "post_type=focus_area&meta_key=_thrive_meta_focus_display_post_type&meta_value=post&order=ASC&posts_per_page=-1" );
               foreach ( $query1->get_posts() as $p ) {
                    $post_custom_atr = get_post_custom( $p->ID );
                    $focus_cats      = json_decode( $post_custom_atr['_thrive_meta_focus_display_categories'][0] );
                    if ( ! is_array( $focus_cats ) ) {
                         $focus_cats = array();
                    }

                    if ( isset( $post_custom_atr['_thrive_meta_focus_display_location'] ) && isset( $post_custom_atr['_thrive_meta_focus_display_location'][0] ) && $post_custom_atr['_thrive_meta_focus_display_location'][0] == $position && ( $post_custom_atr['_thrive_meta_focus_display_is_default'][0] == 1 || count( array_intersect( $post_categories, $focus_cats ) ) > 0 ) ) {
                         $post_focus = $p;
                    }
               }
          }
          if ( ! $page_focus ) {
               $post_categories = wp_get_post_categories( $post->ID );
               //get the focus area for the posts and for the pages, if any is set
               $query2 = new \WP_Query( "post_type=focus_area&meta_key=_thrive_meta_focus_display_post_type&meta_value=page&order=ASC&posts_per_page=-1" );
               foreach ( $query2->get_posts() as $p ) {
                    $post_custom_atr = get_post_custom( $p->ID );
                    if ( isset( $post_custom_atr['_thrive_meta_focus_display_categories'] ) && $post_custom_atr['_thrive_meta_focus_display_categories'][0] ) {
                         $focus_cats = json_decode( $post_custom_atr['_thrive_meta_focus_display_categories'][0] );
                    } else {
                         $focus_cats = array();
                    }
                    if ( ! is_array( $focus_cats ) ) {
                         $focus_cats = array();
                    }
                    if ( isset( $post_custom_atr['_thrive_meta_focus_display_location'] ) && isset( $post_custom_atr['_thrive_meta_focus_display_location'][0] ) && $post_custom_atr['_thrive_meta_focus_display_location'][0] == $position ) {
                         $page_focus = $p;
                    }
               }
          }

          if ( $current_post->post_type == "post" ) {
               if ( $post_focus ) {
                    $current_focus = $post_focus;
               }
          }
          /**
           *  IMPORTANT: This is our check that allows us to have a Focus Area in our custom post types!
           */
          if (PostTypeUtil::is_poster_single() || PostTypeUtil::is_book_single() || PostTypeUtil::is_single_legacy_course()) {
               if ( $post_focus ) {
                    $current_focus = $post_focus;
               }
          }

          if ( $post->post_type == "page" ) {
               if ( $page_focus ) {
                    $current_focus = $page_focus;
               }
          }

          if ( $position == "between_posts" ) {
               $query3      = new \WP_Query( "post_type=focus_area&meta_key=_thrive_meta_focus_display_location&meta_value=between_posts&order=ASC&posts_per_page=-1" );
               $focus_areas = $query3->get_posts();

               foreach ( $focus_areas as $temp_focus ) {
                    $post_custom_atr = get_post_custom( $temp_focus->ID );
                    if ( isset( $post_custom_atr['_thrive_meta_focus_display_between_posts'] ) && isset( $post_custom_atr['_thrive_meta_focus_display_between_posts'][0] ) && $post_custom_atr['_thrive_meta_focus_display_between_posts'][0] == $place ) {
                         $current_focus = $temp_focus;
                    }
               }
          }

          if ( $place == "blog" || $place == "archive" ) {

               if ( $place == "blog" ) {
                    $query4 = new \WP_Query( "post_type=focus_area&meta_key=_thrive_meta_focus_page_blog&meta_value=blog&order=ASC&posts_per_page=-1" );
               } elseif ( $place == "archive" ) {
                    $query4 = new \WP_Query( "post_type=focus_area&meta_key=_thrive_meta_focus_page_archive&meta_value=archive&order=ASC&posts_per_page=-1" );
               }

               $focus_areas = $query4->get_posts();

               foreach ( $focus_areas as $focus_area ) {
                    $post_custom_atr = get_post_custom( $focus_area->ID );

                    if ( isset( $post_custom_atr['_thrive_meta_focus_display_location'] )
                         && isset( $post_custom_atr['_thrive_meta_focus_display_location'][0] )
                         && $post_custom_atr['_thrive_meta_focus_display_location'][0] == $position
                    ) {
                         $current_focus = $focus_area;
                    }
               }
          }

          if ( ! $current_focus ) {
               return;
          }
          $current_attrs = get_post_custom( $current_focus->ID );

          if ( ! $current_attrs || ! isset( $current_attrs['_thrive_meta_focus_template'] ) || ! isset( $current_attrs['_thrive_meta_focus_template'][0] ) ) {
               return;
          }

          if ( isset( $current_attrs['_thrive_meta_focus_optin'] ) && isset( $current_attrs['_thrive_meta_focus_optin'][0] ) ) {
               $optin_id = (int) $current_attrs['_thrive_meta_focus_optin'][0];

               //form action
               $optinFormAction = get_post_meta( $optin_id, '_thrive_meta_optin_form_action', true );

               //form method
               $optinFormMethod = get_post_meta( $optin_id, '_thrive_meta_optin_form_method', true );
               $optinFormMethod = strtolower( $optinFormMethod );
               $optinFormMethod = $optinFormMethod === 'post' || $optinFormMethod === 'get' ? $optinFormMethod : 'post';

               //form hidden inputs
               $optinHiddenInputs = get_post_meta( $optin_id, '_thrive_meta_optin_hidden_inputs', true );

               //form fields
               $optinFieldsJson  = get_post_meta( $optin_id, '_thrive_meta_optin_fields_array', true );
               $optinFieldsArray = json_decode( $optinFieldsJson, true );

               //form not visible inputs
               $optinNotVisibleInputs = get_post_meta( $optin_id, '_thrive_meta_optin_not_visible_inputs', true );
          } else {
               $optinFieldsArray  = array();
               $optinFormAction   = "";
               $optinHiddenInputs = "";
          }
          $value_focus_template = strtolower( $current_attrs['_thrive_meta_focus_template'][0] );
          //TODO - refactorize this
          //if ($value_focus_template == "template3" || $value_focus_template == "template6") {
          if ( $value_focus_template == "template5" || $value_focus_template == "template6" ) {
               $value_focus_template = "template2";
          }

          $template_path = get_template_directory() . "/focusareas/" . $value_focus_template . ".php";
          if ( $position != "top" ) {
               //echo $template_path; die;
          }
          require $template_path;
     }
}
