<?php 

namespace notsalmon\utils;
use notsalmon\bases\Facade;

/**
 * This utility class is simply intended to provide a single place to retrieve urls 
 * that point to our site on the external `teachable.com` platform. If anything changes 
 * in the future (for example: `http` to `https`), it should be easy enough to update 
 * the functionality and output here, instead of in multiple different files.
 *
 * Please note, this class makes use of the `Facade` class. Please read the `Facade` 
 * documentation for more information.
 *
 * Facade Example:
 * To call `$instance->__get_url(...)`, instead you can `TeachableUtil::get_url(...)`. 
 *
 * TODO: Technically, this class should be "informed" by some sort of global option 
 * defined inside the CMS itself. But, for now, its unnecessary as its subject to change.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class TeachableUtil extends Facade {

    /**
     * Defines the base url to our site on the `teachable.com` platform. Please note, 
     * this variable is intended to be accessed only through the available API methods.
     *
     * @since    1.0.0
     * @access   private
     * @example  TeachableUtil::get_url();
     * @var      String           $base_url    The base url to the teachable.com site.
     */
    private $base_url = "http://mastersinlife.com/";

    /**
     * Defines the displayble name for our site on the `teachable.com` platform. Please 
     * note, this variable is intended to be accessed only through the available API 
     * methods.
     *
     * @since    1.0.0
     * @access   private
     * @example  TeachableUtil::get_display_name();
     * @var      String           $base_url    The base url to the teachable.com site.
     */
    private $display_name = "MastersInLife.com";


    // =================================================================================
    // ======= GETTERS : DISPLAY NAME ==================================================
    // =================================================================================
    /**
     * Returns the displayable name for our site on the `teachable.com` platform. This 
     * is provided for consistency and simplicity. If our name ever changes, or we want 
     * to change how it is display on the site (ie...all lowercase letters), then this 
     * should make it much easier to update any hard-coded values through the site.
     *
     * @since    1.0.0
     * @access   protected
     * @example  TeachableUtil::get_display_name();
     * @return   String                         The full absolute path to a MastersInLife.com page.
     */
    protected function __get_display_name(){
        return $this->display_name;
    }

    // =================================================================================
    // ======= GETTERS : URLS ==========================================================
    // =================================================================================
    /**
     * Returns an full absolute url to MastersInLife.com and/or any subpages. Optionally, 
     * you can generate a Google Analytics tracking query (using 
     * `TeachableUtil::get_tracking_query(...)`) that will be appended to the full url.
     *
     * @since    1.0.0
     * @access   protected
     * @example  TeachableUtil::get_url("p/secrets-of-happy-couples");
     * @param    String            $uri         Optional. A uri to a subdirectory or file.
     * @param    String            $query       Optional. A valid query string.
     * @return   String                         The full absolute path to a MastersInLife.com page.
     */
    protected function __get_url($uri = "", $query = null){
        return $this->base_url . 
                ltrim($uri, "/") . 
                (($query) ? ("?" . $query) : "");
    }

    /**
     * Returns a full absolute url to the MastersInLife.com course listing page. 
     * Optionally, you can generate a Google Analytics tracking query (using 
     * `TeachableUtil::get_tracking_query(...)`) that will be appended to the full url.
     *
     * @since    1.0.0
     * @access   protected
     * @example  TeachableUtil::get_courses_listing_url();
     * @param    String            $uri         Optional. A uri to a subdirectory or file. (Shouldn't be needed)
     * @param    String            $query       Optional. A valid query string.
     * @return   String                         The full absolute path to the MastersInLife.com course listing page.
     */
    protected function __get_courses_listing_url($uri = "", $query = null){
        return $this->__get_url("courses/" . ltrim($uri, "/"), $query);
    }

    /**
     * Returns a full absolute url to the MastersInLife.com course listing page filtered 
     * by category. Optionally, you can generate a Google Analytics tracking query (using 
     * `TeachableUtil::get_tracking_query(...)`) that will be appended to the full url.
     *
     * @since    1.0.0
     * @access   protected
     * @example  TeachableUtil::get_course_category_url("love");
     * @param    String            $uri         Optional. A course category slug.
     * @param    String            $query       Optional. A valid query string.
     * @return   String                         The full absolute path to the MastersInLife.com course listing page filtered by category.
     */
    protected function __get_course_category_url($uri = "", $query = null){
        return $this->__get_courses_listing_url("category/" . ltrim($uri, "/"), $query);
    }

    /**
     * Returns a full absolute url to a MastersInLife.com general page. Please note, "page" 
     * urls are applicable to any genral page (ie...about, praise, etc) as well as actual 
     * course sales pages. Optionally, you can generate a Google Analytics tracking query 
     * (using `TeachableUtil::get_tracking_query(...)`) that will be appended to the full url.
     *
     * @since    1.0.0
     * @access   protected
     * @example  TeachableUtil::get_page_url("praise");
     * @param    String            $uri         Optional. A valid page slug.
     * @param    String            $query       Optional. A valid query string.
     * @return   String                         The full absolute path to a general MastersInLife.com page.
     */
    protected function __get_page_url($uri = "", $query = null){
        return $this->__get_url("p/" . ltrim($uri, "/"), $query);
    }

     /**
     * Returns a full absolute url to a single MastersInLife.com course page. Optionally, you 
     * can generate a Google Analytics tracking query (using `TeachableUtil::get_tracking_query(...)`) 
     * that will be appended to the full url.
     *
     * @since    1.0.0
     * @access   protected
     * @example  TeachableUtil::get_single_course_url("stop-craving-sugar-and-junk-food");
     * @param    String            $uri         Optional. A valid page slug.
     * @param    String            $query       Optional. A valid query string.
     * @return   String                         The full absolute path a single MastersInLife.com course page.
     */
    protected function __get_single_course_url($uri = "", $query = null){
        return $this->__get_page_url($uri, $query);
    }

    // =================================================================================
    // ======= GETTERS : QUERY STRING ==================================================
    // =================================================================================
    /**
     * Returns a Google Analytics tracking query string. This is intended to inform the 
     * Google Analytics property at MastersInLife.com on what traffic is being referred 
     * by this website. All parameters are optional.
     *
     * @since    1.0.0
     * @access   protected
     * @example  TeachableUtil::get_tracking_query($source="notsalmon", $medium="website", $campaign="notsalmon", $content="legacy-access/best-seller-recording")
     * @see      https://ga-dev-tools.appspot.com/campaign-url-builder/
     * @param    String            $source      Optional. The referrer. (e.g. google, newsletter)
     * @param    String            $medium      Optional. Marketing medium. (e.g. cpc, banner, email)
     * @param    String            $campaign    Optional. Product, promo code, or slogan (e.g. spring_sale)
     * @param    String            $content     Optional. Use to differentiate ads.
     * @return   String                         A valid query string. (utm_source=...&utm_medium=...&...)
     */
    protected function __get_tracking_query($source = "notsalmon", $medium = "website", $campaign="notsalmon", $content = null){
        $params = array();
        if ($source) $params[] = "utm_source=" . $source;
        if ($medium) $params[] = "utm_medium=" . $medium;
        if ($campaign) $params[] = "utm_campaign=" . $campaign;
        if ($content) $params[] = "utm_content=" . $content;
        return (count($params) > 0) ? implode("&", $params) : "";
    }
}
