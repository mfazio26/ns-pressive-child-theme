<?php

namespace notsalmon\bases;

/**
 * This abstract class serves a single purpose. It allows a subclass's instance methods 
 * that are prepended with two underscores ("__") to be accessed via a static method 
 * equivalent. For example...
 *
 * To call `$instance->__is_post_single()` instead you can `PostTypeUtil::is_post_single()`.
 *
 * The intention is, we don't want to have to instantiate and store a new class instance 
 * in every file where the class instance is used. So, instead, the `Facade` will create 
 * a new instance (when one doesn't already exist) and cache it. We then lookup and 
 * retrieve the subclass instance on subsequent static method calls – which ensures that 
 * exactly and only one instance of each subclass type exists at a time.
 *
 * Please note, this is an `Abstract Class` because it should never be instantiated directly. 
 * It should ALWAYS have a subclass.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
abstract class Facade {

	/**
     * Gets if the current page is a single Post.
     *
     * @since    1.0.0
     * @access   private
     * @var      Array                $instances    A cache of all `Facade` subclass instances.
     */
	private static $instances = array();

	/**
     * When a static method is called on a subclass and that method DOES NOT exist, we 
     * are able to process it here. First, we retrieve the cached instance of the subclass 
     * that was called. We then prepend two underscores to the static method name that was 
     * called. If the subclass has an instance method name that matches (with underscores), 
     * then we are going to call that subclass instance's method and pass along any 
     * static method arguments that are available.
     *
     * @since    1.0.0
     * @access   public
     * @param    String        		  $name             The subclass's static method name that was called.
     * @param    Array                    $arguments        An array of agruments that was passed to the subclass's static method call.
     */
	public static function __callStatic($name, $arguments) {
		$item = self::get_cached_item(get_called_class());
		if ($item && method_exists($item["class_name"], ("__" . $name))) {
			return call_user_func_array(
				array($item["instance"], ("__" . $name)),
				$arguments
			);
		}
	}

     /**
     * In some scenarios, methods may need to be called on the subclass instance itself, and 
     * not proxied via the `__callStatic()` method. This method simply retrieves the cached 
     * instance of the subclass that was called. For example, when attempting to return a 
     * variable reference you can't do `&ThriveThemeUtil::get_options()["key"] = value`. 
     * Instead, you have to...
     *      $instance = ThriveThemeUtil::get_instance();
     *      $arr = &$instance->get_options();
     *      $arr["featured_image_style"] = "wide";
     *
     * @since    1.0.0
     * @access   public
     * @return   Class                                      The corresponding subclass instance item from the `$instances` cache.
     */
     public static function get_instance() {
          if ($item = self::get_cached_item(get_called_class())){
               return $item["instance"];
          }
          return null;
     }

	/**
     * Gets the corresponding subclass instance item from the `$instances` cache. If an 
     * item doesn't exist yet, one will automatically be created and cached. This method 
     * ensures that exactly and only one instance ever exists per subclass type.
     *
     * @since    1.0.0
     * @access   private
     * @param    String        		  $subclass_name    The full namespaced subclass name that was called.
     * @return   Array                					The new or existing subclass item in the cache.
     */
	private static function get_cached_item($subclass_name){
		if ($subclass_name){
               // if the subclass name key doesn't already exist, create a new cache entry.
			if (!array_key_exists($subclass_name, self::$instances)){
				self::$instances[$subclass_name] = array(
					"class_name" => $subclass_name,
					"instance" => (new \ReflectionClass($subclass_name))->newInstance()
				);
			}
			return self::$instances[$subclass_name];
		}
		return null;
	}
}
