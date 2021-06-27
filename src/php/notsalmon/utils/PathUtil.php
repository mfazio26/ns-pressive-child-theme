<?php 

namespace notsalmon\utils;
use notsalmon\bases\Facade;

/**
 * This utility class is intended to be the singlular place get directory paths – which 
 * can be a little annoying and verbose if you have to do this the long-hand WP way ever 
 * time. Also, by using this utility, we have the freedom to move our theme structure 
 * around later if we ever needed to. All we would have to do is update this file.
 *
 * Please note, this class makes use of the `Facade` class. Please read the `Facade` 
 * documentation for more information.
 *
 * Facade Example:
 * To call `$instance->__get_ns_directory()`, instead you can `PostTypeUtil::get_ns_directory()`. 
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class PathUtil extends Facade {

    // =================================================================================
    // ======= GETTERS =================================================================
    // =================================================================================
    /**
     * Gets the `themes/pressive-child` directory and optionally appends a uri to a 
     * subdirectory or file.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PathUtil::get_ns_directory("notsalmon/Theme.php");
     * @param    Int               $uri         Optional. A uri to a subdirectory or file.
     * @return   String                         The full server path to the directory or file.
     */
    protected function __get_ns_directory($uri = ""){
        return get_stylesheet_directory() . "/" . ltrim($uri, "/");
    }

    /**
     * Gets the `themes/pressive-child/notsalmon/` directory and optionally appends a uri 
     * to a subdirectory or file.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PathUtil::get_ns_app_directory("Theme.php");
     * @param    Int               $uri         Optional. A uri to a subdirectory or file.
     * @return   String                         The full server path to the directory or file.
     */
    protected function __get_ns_app_directory($uri = ""){
        return self::get_ns_directory("notsalmon/" . ltrim($uri));
    }

    /**
     * Gets the `themes/pressive-child/notsalmon/widgets/` directory and optionally 
     * appends a uri to a subdirectory or file. Please note, even though its optional, 
     * most uses-cases should point to an actual file.
     *
     * @since    1.0.0
     * @access   protected
     * @example  PathUtil::get_ns_widgets_directory("ns-widget-shortcode.php");
     * @param    Int               $uri         Optional. A uri to a subdirectory or file.
     * @return   String                         The full server path to the directory or file.
     */
    protected function __get_ns_widgets_directory($uri = ""){
        return self::get_ns_app_directory("widgets/" . ltrim($uri));
    }

    // =================================================================================
    // ======= LOAD / REQUIRE ==========================================================
    // =================================================================================
    /**
     * Loads a widget class file using `require_once()`. We have to do this before we 
     * use `register_widget()`, so this is just a handy shortcut helper. 
     *
     * @since    1.0.0
     * @access   protected
     * @example  PathUtil::load_ns_widget("ns-widget-shortcode.php");
     * @param    Int               $uri         A uri to a widget class file.
     * @return   String                         The full server path to the directory or file.
     */
    protected function __load_ns_widget($uri){
        require_once(self::get_ns_widgets_directory($uri));
    }
}
