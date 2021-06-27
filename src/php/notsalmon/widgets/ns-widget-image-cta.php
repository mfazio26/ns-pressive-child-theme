<?php

/**
 * This class creates a custom widget that is intended for the specific use of allowing an image to 
 * be used to either (A) trigger a ThriveBox popup form, or (B) navigate to an internal or external 
 * url. When used in combination with Thrive Themes Clever Widgets, we can pick and choose which 
 * Image CTAs appear on which pages/posts/etc. 
 * 
 * The intention here is to make a more "useable" and "easier to understand" way of performing this 
 * type of action. WordPress's standard `Text` widget isn't capable of parsing shortcodes (which is 
 * how we open a ThriveBox), other plugins don't allow us to "wrap" the output in a shortcode, and 
 * our own NS Shortcode Widget means we are pasting too much raw HTML (and shortcodes) to make it 
 * easily readable. This seems to be the best way to accomplish this type of feature.
 *
 * IMPORTANT:
 * Please note that we are only adding a `title` field directly via this plugin. All other fields 
 * that appear within this Widget are added via the Advanced Custom Fields UI. This drastically 
 * simplifies the process of creating, validating, etc etc. Soooo much easier.
 *
 * @since      1.0.0
 * @package    notsalmon/widgets
 * @author     Mike Fazio <mike@mfazio.com>
 */
class NS_Widget_Image_Cta extends WP_Widget {

	/**
	 * Sets up a new Image CTA widget instance.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function __construct() {
		// configure and instantiate the widget options
		$widget_options = array(
			"classname" => "ns_widget_image_cta",
			"description" => __("Allows an image to open a ThriveBox or navigate to a URL.", "notsalmon")
		);
		parent::__construct("ns_widget_image_cta", __("NS Image CTA", "notsamon"), $widget_options);

		// We need to do this to match how Thrive does it. This is to ensure that the Thrive Clever 
		// Widgets functionality works properly. Any time a post is added/removed/edited, we need the 
		// Thrive Widget Display Options to update accordingly.
		add_action("save_post", array(&$this, "flush_widget_cache"));
		add_action("deleted_post", array(&$this, "flush_widget_cache"));
		add_action("switch_theme", array(&$this, "flush_widget_cache"));
	}

	/**
	 * Outputs the content for the current Image CTA widget instance. Please note, because of the 
	 * very specific purpose of this widget, we do not want the parent theme or plugins to be able 
	 * to alter our output. For this reason, we are not including the "before/after" arguments.
	 *
	 * Please note, to not overcomplicate the rendering logic, we are splitting in into two private 
	 * methods that each handle their own use-case – intended to trigger a ThriveBox, and intended 
	 * to navigate to an internal/external url.
	 *
	 * @since    1.0.0
     * @access   public
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Image CTA widget instance.
	 */
	public function widget($args, $instance) {
		// required to retrieve Advanced Custom Fields from widgets
		$widget_id = "widget_" . $this->id;

		// get the image field and type
		$image = get_field("image", $widget_id);
		$type = get_field("cta_type", $widget_id);

		// if there is a valid image, render it
		$img_rendered = ($image) ? wp_get_attachment_image($image, "ns-poster-image", false, array("class" => "ns-sidebar-widget-image-cta__image")) : null;

		// if we have a valid image tag, otherwise, there's no point in rendering anything!
		if ($img_rendered){
			// which type should we validate and render?
			if ($type == "thrivebox-trigger") $this->_render_widget_thrivebox_trigger($widget_id, $img_rendered);
			else $this->_render_widget_url($widget_id, $img_rendered);
		}
		
		// note: This is what we would do if we wanted the parent theme or plugins to be allowed 
		// to alter our output. However, because of the very specific nature of this widget, we 
		// do not want that. The following line is here just for reference purposes.
		
		// echo $args["before_widget"] . $args["before_title"] . $title . $args["after_title"] . $our_output . $args["after_widget"];
	}

	/**
	 * Outputs the widget that is will trigger the ThriveBox to open.
	 *
	 * @since    1.0.0
     * @access   private
	 * @param string 	 $widget_id     The `widget_[number]` that is required for Advanced Custom Fields.
	 * @param string 	 $img 			The rendered html-formatted image tag (`<img>`)
	 */
	private function _render_widget_thrivebox_trigger($widget_id, $img){
		if ($thrivebox_id = get_field("thrivebox_2step_id", $widget_id)){
			?>

				<section class="ns-sidebar-widget-image-cta">
					<?php echo do_shortcode("[thrive_2step id='" . $thrivebox_id . "']" . $img . "[/thrive_2step]"); ?>
				</section>

			<?php
		}
	}

	/**
	 * Outputs the widget that will navigate to an internal/external url depending on the fields 
	 * that have been provided.
	 *
	 * @since    1.0.0
     * @access   private
	 * @param string 	 $widget_id     The `widget_[number]` that is required for Advanced Custom Fields.
	 * @param string 	 $img 			The rendered html-formatted image tag (`<img>`)
	 */
	private function _render_widget_url($widget_id, $img){
		// is this an internal or external url?
		$is_internal = get_field("url_type", $widget_id);
			
		// if this is an internal url, our `internal_url` field returns a post id, not a permalink, so 
		// we have to normalize...
		$url = ($is_internal) 
			? get_the_permalink(get_field("internal_url", $widget_id)) 
			: get_field("external_url", $widget_id);

		// if its external, we have to open in a new window
		$url_target = ($is_internal) ? "_self" : "_blank";

		// at a minimum, the url must be defined. (the `$img` should always be defined to even call this method)
		if ($url){
			?>

				<section class="ns-sidebar-widget-image-cta">
					<a class="ns-sidebar-widget-image-cta__btn" href="<?php echo $url; ?>" target="<?php echo $url_target; ?>">
						<?php echo $img; ?>
					</a>
				</section>

			<?php
		}
	}

	/**
	 * Handles updating settings for the current Image CTA widget instance. Please note, we are 
	 * only adding a `title`. All other fields are provided via Advanced Custom Fields UI.
	 *
	 * @since    1.0.0
     * @access   public
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance["title"] = sanitize_text_field($new_instance["title"]);
		$this->flush_widget_cache();

		// not entirely sure what this does, but we want to mimic Thrive Themes Widgets as closely 
		// as possible. Please reference one of there widgets for more information.
		$all_options = wp_cache_get("alloptions", "options");
		if (isset($all_options["ns_widget_image_cta"])){
			delete_option("ns_widget_image_cta");
		}
		return $instance;
	}

	/**
	 * Clears the cache associated with our Image CTA widget. Please note, this code is derived 
	 * directly from the Thrive Themes `widget-custom-text.php`. We want to make sure our widget 
	 * matches theirs as closely as possible for compatibility. So, although I'm not entirely sure 
	 * why or if this is necessary, we are going to include it anyways.
	 *
	 * @since    1.0.0
     * @access   public
     * @see 	 themes/pressive/inc/widgets/widget-custom-text.php
	 */
	public function flush_widget_cache() {
		wp_cache_delete("ns_widget_image_cta", "widget");
	}

	/**
	 * Outputs the Image CTA widget settings form. Please note, we are only adding a `title`. All 
	 * other fields are provided via Advanced Custom Fields UI.
	 *
	 * @since    1.0.0
     * @access   public
	 * @param array $instance Current settings.
	 */
	public function form($instance) {
		$instance = wp_parse_args((array) $instance, array("title" => ""));
		$title = sanitize_text_field($instance["title"]);

		// =========== BEGIN FORM OUTPUT ============================================================
		?>
			
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Title:</label> 
				<input id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
				       name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
				       value="<?php echo esc_attr($instance['title']); ?>" 
				       type="text"
				       class="widefat" 
				/>
			</p>

		<?php
		// =========== END FORM OUTPUT ============================================================
	}
}