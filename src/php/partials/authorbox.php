<?php
	
	/**
	 * content-grid.php
	 * Derived from themes/pressive/partials/authorbox.php
	 *
	 * This file is 99.999% the same as the Thrive Themes template. Pretty much the only 
	 * thing that is different is that we are modifying hwo the `$author_name` is displayed 
	 * in the `<h4>` instead of displaying `"About the Author"`.
	 *
	 * Note, we are also optionally removing the clickable author image.
	 */

	$fname         = get_the_author_meta( 'first_name' );
	$lname         = get_the_author_meta( 'last_name' );
	$desc          = get_the_author_meta( 'description' );
	$thrive_social = array_filter( array(
		"fbi"  => get_the_author_meta( 'twitter' ),
		"sh"   => get_the_author_meta( 'facebook' ),
		"gi"   => get_the_author_meta( 'gplus' ),
		"ini"  => get_the_author_meta( 'linkedin' ),
		"xing" => get_the_author_meta( 'xing' )
	) );

	$show_social_profiles = explode( ',', get_the_author_meta( 'show_social_profiles' ) );
	$show_social_profiles = array_filter( $show_social_profiles );
	if ( empty( $show_social_profiles ) ) { // back-compatibility
		$show_social_profiles = array( 'e', 'sh', 'fbi', 'gi' );
	}
	$author_name  = get_the_author_meta( 'display_name' );
	$display_name = empty( $author_name ) ? $fname . " " . $lname : $author_name;
	$author_url   = esc_url( get_author_posts_url( get_the_author_meta( "ID" ) ) );

	// Should we allow the image to clickable and navigate to the author bio?
	$is_author_img_clickable = false;
?>

<div class="aut">
	<?php if ( ! empty( $thrive_social['fbi'] ) || ! empty( $thrive_social['sh'] ) || ! empty( $thrive_social['gi'] ) || ! empty( $thrive_social['lnk'] ) || ! empty( $thrive_social['xing'] ) ): ?>
		<div class="scl">
			<div class="flw"><?php _e( "Follow", 'thrive' ); ?></div>
			<div class="scw">
				<ul>
					<?php foreach ( $thrive_social as $key => $url ): ?>
						<?php if ( in_array( $key, $show_social_profiles ) || empty( $show_social_profiles[0] ) ): ?>
							<?php $url = _thrive_get_social_link( $url, $key ); ?>
							<li><a href="<?php echo $url; ?>" target="_blank" class="<?php echo $key; ?>"></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	<div class="ta">
		<div class="left tai">
			<?php if ($is_author_img_clickable): ?>
				<a href="<?php echo $author_url ?>" class="auti" style="background-image: url('<?php echo _thrive_get_avatar_url( get_avatar( get_the_author_meta( 'user_email' ), 180 ) ); ?>')"></a>
			<?php else: ?>
				<div class="auti" style="background-image: url('<?php echo _thrive_get_avatar_url( get_avatar( get_the_author_meta( 'user_email' ), 180 ) ); ?>')"></div>
			<?php endif; ?>
		</div>
		<div class="left tat">
			<h4><?php _e( $author_name, 'notsalmon' ); ?></h4>

			<p><?php echo $desc; ?></p>
		</div>
		<div class="clear"></div>
	</div>
</div>