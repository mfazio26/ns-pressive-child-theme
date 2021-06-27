<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;

/**
 * Renderable Template:
 * Renders a specific Quote's Embed Image Box that allows users to embed the image on 
 * a third-party website as a link back to NotSalmon.
 *
 * IMPORTANT: Please note the class added directly to the `<img class="ngg_"`. This has 
 * very specific purpose. It ensures that the Monarch Social Media Sharer ignores this 
 * image tag when parsing the page in order to automatically wrap images. Otherwise, 
 * Monarch will wrap the `<img>` in `<div class="et_social_media_wrapper">` even though 
 * this isn't really an image!!!
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Quotes_Embedbox extends Renderable {

	/**
     * Overriden method
     * Renders a specific Quote's Embed Image Box that allows users to embed the image 
     * on a third-party website as a link back to NotSalmon.
     * 
     * Please note, WP has a bug where it is adding extra `</p>` tags at the end of 
     * `<img>` tags and wrapping all `<a>` tags. To work around this behavior/bug, we 
     * are wrapping the content in a `<pre>` tag - which tells WordPress to ignore this 
     * behavior. Its totally fine to use a `<pre>` tag here because the content is 
     * embed code anyways.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		
		// required: get the post id
		$post_id = (array_key_exists("id", $context) && $context["id"]) ? $context["id"] : -1;

		// if the post id is undefined or is not a single quote post, return an empty string!
		if (!PostTypeUtil::is_poster_single($id=$post_id)) return "";

		// embed section heading
		$heading = (array_key_exists("heading", $context) && $context["heading"]) ? $context["heading"] : "Embed this image on your site.";

		// create post url with Goggle Analytics campaign tracking tag.
		$post_url = get_permalink($id=$post_id) . "?utm_campaign=Quote%20Embeds&utm_source=notsalmon&utm_medium=website&utm_content=" . $post_id;

		// site name and url shortcuts
		$site_url = home_url("/", "https");
		$site_name = "NotSalmon.com";
		
		// get the image id
		$image_id = get_post_thumbnail_id($post_id);

		// get the image alt text (if applicable)
		$image_alt = get_post_meta($image_id, "_wp_attachment_image_alt", true);
	 	
	 	// get the image url, width, and height. (should be 640x640 based on poster-image preset)
		$image_data = wp_get_attachment_image_src($image_id, "ns-poster-image");
		$image_url = $image_data[0];
		$image_width = $image_data[1];
		$image_height= $image_data[2];


		/* Extra HTML: Intended to be added to textarea.
		<strong>Please include attribution to <?php echo $site_url; ?> with this graphic.</strong>
		<br/>
		<br/>
		*/

		if ($image_url){
			// ======= BEGIN OUTPUT ====================================================	
			?>
				
				<aside class="ns-poster-embedbox">
					<h3 class="ns-poster-embedbox__heading"><?php echo $heading; ?></h3>
					<p class="ns-poster-embedbox__description">Copy the HTML embed code (below) to add this image to your website. Please ensure you include attribution to <em><?php echo $site_name; ?></em>. Thank you!</p>
					<pre class="ns-poster-embedbox__pre">
						<textarea class="ns-poster-embedbox__textarea">
							<p>
								<a href="<?php echo $post_url; ?>" title="View more quotes on <?php echo $site_name; ?>" aria-label="View more quotes on <?php echo $site_name; ?>">
									<img src="<?php echo $image_url; ?>" <?php echo ($image_alt) ? ("alt=\"" . $image_alt . "\"") : ""; ?> width="<?php echo $image_width; ?>" height="<?php echo $image_width; ?>" border="0" data-et-class="ngg_"/>
								</a>
							</p>
						</textarea>
					</pre>
				</aside>

				
			<?php 
			// ======= BEGIN OUTPUT ====================================================
		}
	}
}
