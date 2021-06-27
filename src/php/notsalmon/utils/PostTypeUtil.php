<?php 

namespace notsalmon\utils;
use notsalmon\bases\Facade;
use \WP_Query;

/**
 * This utility class is intended to be the singlular place to check "What is the current 
 * post or page type?" so we can keep and update these "checks" in a single place.
 *
 * Please note, this class makes use of the `Facade` class. Please read the `Facade` 
 * documentation for more information.
 *
 * Facade Example:
 * To call `$instance->__is_post_single()`, instead you can `PostTypeUtil::is_post_single()`. 
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class PostTypeUtil extends Facade {

    // =================================================================================
    // ======= POST (DEFAULT): POST TYPE ===============================================
    // =================================================================================
    /**
     * Gets if the current page is a single Post. If an `id` is provided, returns if the 
     * post id is a valid Post entry.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_post_single()
     * @param    Int               $id          Optional. A valid Post `id`.
     * @return   Boolean                        True if a single Post, false if not.
     */
    protected function __is_post_single($id = null){
        return ($id) 
            ? (get_post_type($id) == "post") ? true : false 
            : (is_singular("post")) ? true : false;
    }

    /**
     * Gets if the current page is a Post archive – but not necessarily a Post category.
     * 
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_post_archive()
     * @return   Boolean                True if a Post archive, false if not.
     */
    protected function __is_post_archive(){
        return (is_archive("post")) ? true : false;
    }

    /**
     * Gets if the current page is a Post category.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_post_category()
     * @return   Boolean                True if a Post category, false if not.
     */
    protected function __is_post_category(){
        return (is_archive("post") && is_category()) ? true : false;
    }

    // =================================================================================
    // ======= BOOK : POST TYPE ========================================================
    // =================================================================================
    /**
     * Gets if the current page is a single Book. If an `id` is provided, returns if the 
     * post id is a valid Book entry.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_book_single()
     * @param    Int               $id          Optional. A valid Book `id`.
     * @return   Boolean                        True if a single Book, false if not.
     */
    protected function __is_book_single($id = null){
        return ($id) 
            ? (get_post_type($id) == "book") ? true : false 
            : (is_singular("book")) ? true : false;
    }

    /**
     * Gets if the current page is a Book archive.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_book_archive()
     * @return   Boolean                True if a Book archive, false if not.
     */
    protected function __is_book_archive(){
        return (is_post_type_archive("book")) ? true : false;
    }

    // =================================================================================
    // ======= LEGACY COURSE : POST TYPE ===============================================
    // =================================================================================
    /**
     * Gets if the current page is a single Legacy Course. If an `id` is provided, 
     * returns if the post id is a valid Legacy Course entry.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_legacy_course_single()
     * @param    Int               $id          Optional. A valid  Legacy Course `id`.
     * @return   Boolean                        True if a single Legacy Course, false if not.
     */
    protected function __is_legacy_course_single($id = null){
        return ($id) 
            ? (get_post_type($id) == "legacy_course") ? true : false 
            : (is_singular("legacy_course")) ? true : false;
    }

    /**
     * Gets if the current page is a Legacy Course archive.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_legacy_course_archive()
     * @return   Boolean                True if a Legacy Course archive, false if not.
     */
    protected function __is_legacy_course_archive(){
        return (is_post_type_archive("legacy_course")) ? true : false;
    }

    // =================================================================================
    // ======= NS VIDEO COURSES : PAGE TYPE ============================================
    // =================================================================================
    /**
     * Gets if the current page is a Video Course page by checking if it is a direct 
     * child page of the main Video Courses parent page, and if it has the ACF field 
     * "videocourse_is_course" set to "true". This will be false for any pages 
     * nested 2-levels deep - such as a /courses/anxiety-cure/free-preview page, or any 
     * offer/ancillary pages where "videocourse_is_course=false".
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_video_course_page()
     * @return   Boolean                True if a Vide Course single page, false if not.
     */
    protected function __is_video_course_page($id = null){
        // get the current Video Courses main parent page id based on the current 
        // environment and TLD - local, dev, com, etc
        $video_courses_parent_id = self::get_video_courses_parent_page_id();

        // If the post id was provided, we're going to query that post and check if 
        // the parent id matches.
        if ($id){
            $post = get_post($id);
            return ($post && ($post->post_parent == $video_courses_parent_id) && get_field("videocourse_is_course", $id)) ? true : false;
        }
        // If the post id was not provided, we're going to query the current global 
        // $post and try and match its parent post id.
        else {
            global $post;
            return (is_page() && ($post->post_parent == $video_courses_parent_id) && get_field("videocourse_is_course", $post->ID)) ? true : false;
        }
    }

    /**
     * Gets the id of the main Video Courses parent page. Please note, because "Video 
     * Courses" are "pages" and not posts or custom type types, we have to query them 
     * differently. We can't use is_post_type($id). Instead, we have to check that the 
     * Video Course is a direct child of the main parent Video Courses page.
     * 
     * To do this, we unfortunately have to hard-code the parent page's id in order to 
     * query against it. And, to complicate the matter, we have multiple environments 
     * where the id might be different – local, dev, live, etc. So, we have to first 
     * determine the environment (by domain and TLD) and find the corresponding 
     * hard-coded id.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::get_video_courses_parent_page_id()
     * @return   int                The id of the main Video Courses parent page per the current environment.
     */
    protected function __get_video_courses_parent_page_id(){
        // hard coded ids per environment – domain/TLD
        $env_courses_ids = array(
            "*" => 50238,
            "localhost" => 50238,
            "dev" => 50238,
            "com" => 63418
        );

        // check what type of domain/TLD. we'll use the match as an array key.
        preg_match('/dev|com|localhost$/', $_SERVER["HTTP_HOST"], $matches);
        
        // if we found a match, return the id per the current environment.
        if ($matches && count($matches) > 0){
            $env = $matches[0];
            return $env_courses_ids[$env];
        }
        // if we didn't find a match for any reason, its safest to just return the live 
        // site's id. worst case is we break a dev environment, but never a live one!
        return $env_courses_ids["*"];
    }

    // =================================================================================
    // ======= MASTERS IN LIFE COURSES : POST TYPE =====================================
    // =================================================================================
    /**
     * Gets if the current page is a single MastersInLife Course. If an `id` is 
     * provided, returns if the post id is a valid MastersInLife Course entry.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_milcourse_single()
     * @param    Int               $id          Optional. A valid MIL Course `id`.
     * @return   Boolean                        True if a single MastersInLife Course, false if not.
     */
    protected function __is_mil_course_single($id = null){
        return ($id)
            ? (get_post_type($id) == "mil_courses") ? true : false 
            : (is_singular("mil_courses")) ? true : false;
    }

    /**
     * Gets if the current page is a MastersInLife Course archive/taxonomy. If an `id` 
     * is provided, returns if the associated term id is a valid MastersInLife Course 
     * archive/taxonomy entry.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_mil_course_category()
     * @param    Int               $id          Optional. A valid MIL Course Category `id`.
     * @return   Boolean                        True if a MastersInLife Course archive, false if not.
     */
    protected function __is_mil_course_category($id = null){
        return ($id)
            ? (term_exists($id, "mil_category")) ? true : false 
            : (is_tax("mil_category")) ? true : false;
    }

    /**
     * Gets if the current page is a MastersInLife Course archive/taxonomy.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_milcourse_archive()
     * @return   Boolean                True if a MastersInLife Course archive, false if not.
     */
    protected function __is_mil_course_archive(){
        return (is_post_type_archive("mil_courses") || self::is_mil_course_category()) ? true : false;
    }

    // =================================================================================
    // ======= POSTER/QUOTE : POST TYPE ================================================
    // =================================================================================
    /**
     * Gets if the current page is a single Poster/Quote. If an `id` is provided, 
     * returns if the post id is a valid Poster entry.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_poster_single()
     * @param    Int               $id          Optional. A valid Poster/Quote `id`.
     * @return   Boolean                        True if a single Poster/Quote, false if not.
     */
    protected function __is_poster_single($id = null){
        return ($id)
            ? (get_post_type($id) == "poster") ? true : false 
            : (is_singular("poster")) ? true : false;
    }

    /**
     * Gets if the current page is a Poster archive/taxonomy. If an `id` is provided, 
     * returns if the associated term id is a valid Poster archive/taxonomy entry.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_mil_course_category()
     * @param    Int               $id          Optional. A valid Poster Tag `id`.
     * @return   Boolean                        True if a MastersInLife Course archive, false if not.
     */
    protected function __is_poster_tag($id = null){
        return ($id)
            ? (term_exists($id, "poster_tag")) ? true : false 
            : (is_tax("poster_tag")) ? true : false;
    }

    /**
     * Gets if the current page is a Poster/Quote archive.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PostTypeUtil::is_poster_archive()
     * @return   Boolean                True if a Poster/Quote archive, false if not.
     */
    protected function __is_poster_archive(){
        return (is_post_type_archive("poster") || self::is_poster_tag()) ? true : false;
    }
}
