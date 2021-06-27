<?php
	/**
	 * yarpp Custom Template: Thumbnails
	 *
	 * This is a custom template for yarpp Related Posts that render at the bottom of an entry's content.
	 * Please note, we are INTENTIONALLY making this render the same as Thrive's Related Posts component 
	 * because we want to use them interchangably when Thrive is not supported – for example, in Custom 
	 * Post Types.
	 *
	 * Please note, if no yarpp Related Posts are found on the current post, we are going to query for 
	 * random posts of the same post type.
	 *
	 * @see /themes/pressive/partials/bottom-related-posts.php
	 * @todo 
	 *
	 *		Maybe move this to a `Renderables` class?
	 * 
	 * 		Thrive also supports toggling images with the following line of code. Please note, this 
	 * 		actually renders a completely different type of Related Posts component instead of just 
	 * 		stripping away the images.
	 * 
	 *      `<div class="rltp<?php echo $options['related_posts_images'] == 1 ? 'i' : ''; ?> clearfix">`
	 */
	
	// does yarpp have related posts for this post?
	$ns_yarpp_is_related = (have_posts()) ? true : false;
	// should we change the title based whether this is yarpp results or random posts? 
	$ns_yarpp_title = ($ns_yarpp_is_related) ? "NotSalmon Recommends" : "You May Also Like";
	// sets the default image if no featured image is found.
	$ns_yarpp_thumbnail_default = get_template_directory_uri() . "/images/default_featured.jpg";
	// sets the current post type. (used only when querying random posts)
	$ns_yarpp_post_type = get_post_type();
	// placeholder for queried posts
	$ns_yarpp_posts = array();

	$ns_yarpp_limit = yarpp_get_option("limit");
	// we want to limit "related posters" to a max of 2.
	$ns_type_poster_limit = 2;
	$ns_post_counter = 0;
	
	// if yarpp returned Related Posts - loop and populate our array...
	if ($ns_yarpp_is_related){
		while (have_posts()) : the_post();
			// increment post counter 
			$ns_post_counter++;

			$ns_yarpp_post_id = get_the_ID();
			$ns_yarpp_post_type = get_post_type($ns_yarpp_post_id);
			$ns_yarpp_thumbnail_transform = ($ns_yarpp_post_type == "poster") ? "ns-poster-image" : "tt_related_posts";

			// if this is a poster and we already have 2, don't add anymore!
			if (($ns_yarpp_post_type == "poster") && ($ns_post_counter > $ns_type_poster_limit)) continue;

			$ns_yarpp_posts[] = array(
				"type" => $ns_yarpp_post_type,
				"title" => get_the_title(),
				"permalink" => get_the_permalink(),
				"thumbnail" => (has_post_thumbnail($ns_yarpp_post_id)) ? thrive_get_post_featured_image_src($ns_yarpp_post_id, $ns_yarpp_thumbnail_transform) : $ns_yarpp_thumbnail_default
			);
		endwhile;
	}
	// if no yarpp results were returned...
	else {	
		// query random posts of same type EXCLUDING the current post. use Thrive's theme option to limit query.
		global $post;
		// thrive_get_theme_options("related_posts_number")
		$ns_yarpp_query = new WP_Query(array(
				"post_type" => $ns_yarpp_post_type,
				"posts_per_page" => ($ns_yarpp_post_type == "poster") ? $ns_type_poster_limit : $ns_yarpp_limit,
				"orderby" => "rand",
				"order" => "ASC",
				"post__not_in" => array($post->ID)
			)
		);

		// if query returned results, loop and populate our array...
		if ($ns_yarpp_query->have_posts()){
			while ($ns_yarpp_query->have_posts()) {
				$ns_yarpp_post = $ns_yarpp_query->the_post();
				$ns_yarpp_post_id = get_the_ID();
				$ns_yarpp_post_type = get_post_type($ns_yarpp_post_id);
				$ns_yarpp_thumbnail_transform = ($ns_yarpp_post_type == "poster") ? "ns-poster-image" : "tt_related_posts";
				$ns_yarpp_posts[] = array(
					"type" => $ns_yarpp_post_type,
					"title" => get_the_title(),
					"permalink" => get_the_permalink(),
					"thumbnail" => (has_post_thumbnail($ns_yarpp_post_id)) ? thrive_get_post_featured_image_src($ns_yarpp_post_id, $ns_yarpp_thumbnail_transform) : $ns_yarpp_thumbnail_default
				);
			}
		}
	}
	// finally, if our array contains at least one post, render it!
?>

<?php if (count($ns_yarpp_posts) > 0): ?>

	<div class="rltpi clearfix">
		<div class="awr">
			<h5><?php echo $ns_yarpp_title; ?></h5>
			<?php foreach ($ns_yarpp_posts as $entry): ?>
				<a class="rlt left<?php echo " ns-post-type--" . $entry["type"]; ?>" href="<?php echo $entry["permalink"]; ?>" rel="bookmark">
					<div class="rlt-i">
						<div class="rlti" style="background-image: url('<?php echo $entry["thumbnail"]; ?>');"></div>
					</div>
					<p><?php echo $entry["title"]; ?></p>
				</a>
			<?php endforeach; ?>
		</div>
	</div>	
<?php endif; ?>

