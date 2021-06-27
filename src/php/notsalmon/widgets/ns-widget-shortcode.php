<?php

/**
 * This class creates a custom widget that is intended for the specific use of allowing shortcodes 
 * (and only shortcodes) to be placed and rendered inside the widget. When used in combination with 
 * Thrive Themes Clever Widgets, we can do things like render a Book Title and Cover in the sidebar 
 * on ONLY single Book pages. This method of dynamic content should be much easier to understand to 
 * future developers – moreso than providing additional custom functionality/plugins/etc to 
 * accomplish the same thing.
 *
 * @since      1.0.0
 * @package    notsalmon/widgets
 * @author     Mike Fazio <mike@mfazio.com>
 */
class NS_Widget_Shortcode extends WP_Widget {

	/**
	 * Sets up a new Shortcode widget instance.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function __construct() {
		// configure and instantiate the widget options
		$widget_options = array(
			"classname" => "ns_widget_shortcode",
			"description" => __("Allows shortcodes to be used.", "notsalmon")
		);
		parent::__construct("ns_widget_shortcode", __("NS Shortcode", "notsamon"), $widget_options);

		// We need to do this to match how Thrive does it. This is to ensure that the Thrive Clever 
		// Widgets functionality works properly. Any time a post is added/removed/edited, we need the 
		// Thrive Widget Display Options to update accordingly.
		add_action("save_post", array(&$this, "flush_widget_cache"));
		add_action("deleted_post", array(&$this, "flush_widget_cache"));
		add_action("switch_theme", array(&$this, "flush_widget_cache"));
	}

	/**
	 * Outputs the content for the current Shortcode widget instance. Please note, because of the very 
	 * specific purpose of this widget, we do not want the parent theme or plugins to be able to 
	 * alter our output. For this reason, we are not including the "before/after" arguments.
	 *
	 * @since    1.0.0
     * @access   public
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Shortcode widget instance.
	 */
	public function widget($args, $instance) {
		$title = apply_filters(
			"widget_title", 
			(empty($instance["title"])) ? "" : $instance["title"],
			$instance,
			$this->id_base
		);
		$shortcodes = (empty($instance["shortcodes"])) ? "" : $instance["shortcodes"];
		// output
		echo do_shortcode($shortcodes);

		// note: This is what we would do if we wanted the parent theme or plugins to be allowed 
		// to alter our output. However, because of the very specific nature of this widget, we 
		// do not want that. The following line is here just for reference purposes.
		
		// echo $args["before_widget"] . $args["before_title"] . $title . $args["after_title"] . do_shortcode($shortcodes) . $args["after_widget"];
	}

	/**
	 * Handles updating settings for the current Shortcode widget instance.
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
		$instance["shortcodes"] = $new_instance["shortcodes"];
		$this->flush_widget_cache();

		// not entirely sure what this does, but we want to mimic Thrive Themes Widgets as closely 
		// as possible. Please reference one of there widgets for more information.
		$all_options = wp_cache_get("alloptions", "options");
		if (isset($all_options["ns_widget_shortcode"])){
			delete_option("ns_widget_shortcode");
		}
		return $instance;
	}

	/**
	 * Clears the cache associated with our Shortcode widget. Please note, this code is derived 
	 * directly from the Thrive Themes `widget-custom-text.php`. We want to make sure our widget 
	 * matches theirs as closely as possible for compatibility. So, although I'm not entirely sure 
	 * why or if this is necessary, we are going to include it anyways.
	 *
	 * @since    1.0.0
     * @access   public
     * @see 	 themes/pressive/inc/widgets/widget-custom-text.php
	 */
	public function flush_widget_cache() {
		wp_cache_delete("ns_widget_shortcode", "widget");
	}

	/**
	 * Outputs the Shortcode widget settings form.
	 *
	 * @since    1.0.0
     * @access   public
	 * @param array $instance Current settings.
	 */
	public function form($instance) {
		$instance = wp_parse_args((array) $instance, array("title" => "", "shortcodes" => ""));
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

			<p>
				<label for="<?php echo esc_attr($this->get_field_id('shortcodes')); ?>">Shortcodes</label>
	            <textarea id="<?php echo esc_attr($this->get_field_id('shortcodes')); ?>" 
	                      name="<?php echo esc_attr($this->get_field_name('shortcodes') ); ?>" 
	                      class="widefat" rows="16" cols="20" 
	                      ><?php echo esc_textarea($instance["shortcodes"]); ?></textarea>
			</p>

		<?php
		// =========== END FORM OUTPUT ============================================================
	}
}