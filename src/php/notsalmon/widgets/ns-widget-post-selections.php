<?php

/**
 * This class creates a custom widget that allows an author to select/choose posts to be included 
 * in a sidebar listing. The intention is – Thrive Themes has "related posts" widget that renders 
 * a listing, but you have no control over which specific posts gets rendered. So, this custom 
 * widget is intended to look and render exactly like that widget, but provide a way for an author 
 * to specifically select the posts they want to include and in which order. For this reason, this 
 * which renders identically to Thrive Themes `widget-related.php` widget.
 *
 * IMPORTANT:
 * Please note that we are only adding a `title` field directly via this plugin. All other fields 
 * that appear within this Widget are added via the Advanced Custom Fields UI in a group named 
 * "Post Selections Widget". This drastically simplifies the process of creating, validating, etc 
 * etc. Soooo much easier.
 *
 * @since      1.0.0
 * @package    notsalmon/widgets
 * @author     Mike Fazio <mike@mfazio.com>
 */
class NS_Widget_Post_Selections extends WP_Widget {

	/**
	 * Sets up a new Post Selections widget instance.
	 *
	 * @since    1.0.0
     * @access   public
	 */
	public function __construct() {
		// configure and instantiate the widget options
		$widget_options = array(
			"classname" => "ns_widget_postselect",
			"description" => __("Add a list of posts.", "notsalmon")
		);
		parent::__construct("ns_widget_postselect", __("NS Post Selections", "notsamon"), $widget_options);

		// We need to do this to match how Thrive does it. This is to ensure that the Thrive Clever 
		// Widgets functionality works properly. Any time a post is added/removed/edited, we need the 
		// Thrive Widget Display Options to update accordingly.
		add_action("save_post", array(&$this, "flush_widget_cache"));
		add_action("deleted_post", array(&$this, "flush_widget_cache"));
		add_action("switch_theme", array(&$this, "flush_widget_cache"));
	}

	/**
	 * Outputs the content for the current Post Selections widget instance. Please note, because of 
	 * the very specific purpose of this widget, we do not want the parent theme or plugins to be able 
	 * to alter our output. For this reason, we are not including the "before/after" arguments.
	 *
	 * Please note, this widget is intended to render identically to the Thrive Themes "widget-related.php" 
	 * so that we can update styles all at once and retain continuity. If something changes with the 
	 * Thrive output markup, we probably need to update this as well...which kinda stinks.
	 *
	 * @since    1.0.0
     * @access   public
     * @see 	 themes/pressive/inc/widgets/widget-related.php
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Post Selections widget instance.
	 */
	public function widget($args, $instance) {
		
		// required to retrieve Advanced Custom Fields from widgets
		$widget_id = "widget_" . $this->id;

		// widget settings/fields
		$show_thumbnails = get_field("show_thumbnails", $widget_id);
		$show_date = get_field("show_date", $widget_id);
		// post objects are returned as an array of post ids!
		$post_objects = get_field("post_list", $widget_id);

		// Thrive specific and used in the rendering
		$txt_class = ( $show_thumbnails ) ? "" : " noImageTab";

		// if no posts were included, don't output anything!
		if (!$post_objects) return;


		// Begin rendering...
		?>

			<section class="rw widget_thrive_related ns-sidebar-widget-postselect">
				<div class="scn">
					<div class="awr">
						<div class="twr">
							<?php if ( $instance["title"] ): ?>
								<p class="upp ttl"><?php echo $instance["title"] ?></p>
							<?php endif; ?>
						</div>

						<?php foreach($post_objects as $post_id): ?>
							<?php 
								// get post data
								$post_title = get_the_title($post_id);
								$post_permalink = get_permalink($post_id);
								$post_date = get_the_date(get_option("date_format"), $post_id);
								// get the Thrive Themes post icon OR, get the Thrive Themes default icon
								$post_image = thrive_get_post_featured_image_src($post_id, "tt_post_icon");
								$post_image = ($post_image) ? $post_image : get_template_directory_uri() . "/images/tabs_default.png";
							?>
							<div class="pps clearfix">
								<div class="left tim">
									<?php if ($show_thumbnails): ?>
										<div class="wti" style="background-image: url('<?php echo $post_image; ?>');"></div>
									<?php endif; ?>
								</div>
								<div class="left txt<?php echo $txt_class; ?>">
									<a href="<?php echo $post_permalink; ?>"><?php echo $post_title; ?></a>
								</div>
								<?php if ($show_date): ?>
									<span class="post-date">
										<?php echo $post_date; ?>
                                	</span>
                                <?php endif; ?>
							</div>
						<?php endforeach; ?>

						<div class="clear"></div>
					</div>
				</div>
			</section>
		<?php
		
		// note: This is what we would do if we wanted the parent theme or plugins to be allowed 
		// to alter our output. However, because of the very specific nature of this widget, we 
		// do not want that. The following line is here just for reference purposes.
		
		// echo $args["before_widget"] . $args["before_title"] . $title . $args["after_title"] . $our_output . $args["after_widget"];
	}

	/**
	 * Handles updating settings for the current Post Selectiosn widget instance. Please note, we are 
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
		if (isset($all_options["ns_widget_postselect"])){
			delete_option("ns_widget_postselect");
		}
		return $instance;
	}

	/**
	 * Clears the cache associated with our Post Selections widget. Please note, this code is derived 
	 * directly from the Thrive Themes `widget-related.php`. We want to make sure our widget 
	 * matches theirs as closely as possible for compatibility. So, although I'm not entirely sure 
	 * why or if this is necessary, we are going to include it anyways.
	 *
	 * @since    1.0.0
     * @access   public
     * @see 	 themes/pressive/inc/widgets/widget-related.php
	 */
	public function flush_widget_cache() {
		wp_cache_delete("ns_widget_postselect", "widget");
	}

	/**
	 * Outputs the Post Selections widget settings form. Please note, we are only adding a `title`. All 
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