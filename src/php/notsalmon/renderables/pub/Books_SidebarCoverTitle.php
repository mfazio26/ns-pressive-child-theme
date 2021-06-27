<?php

namespace notsalmon\renderables\pub;
use notsalmon\bases\Renderable;
use notsalmon\utils\PostTypeUtil;

/**
 * Renderable Template:
 * Applied to Single Book. Renders a Book's Cover and Title. Please note, this currently 
 * only supports rendering a sidebar-style widget.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */
class Books_SidebarCoverTitle extends Renderable {

	/**
     * Overriden method
     * Renders the Book's Cover and Title sidebar widget.
     *
     * Please note, this renders identically to `Courses_SidebarCoverTitle`. The only 
     * reason why they are separate is there may be a need in the future to have them 
     * render differently – and its better to be a little more verbose now, then to have 
     * to rip it apart later.
     *
     * @since    1.0.0
     * @access   public
     * @param 	 Array 				  $context 		Optional. Any variables you may want to pass to the pseudo-template.
     */
	public function render($context = array()){
		// shortcuts to context variables
		$id = $context["id"];
		$style = $context["style"];

		// if the id is defined and the id is a single Book...
		if ($id && PostTypeUtil::is_book_single($id)){
			$title = get_the_title($id);
			$thumbnail = (has_post_thumbnail($id)) ? get_the_post_thumbnail($id, "ns-book-cover", array("class" => "ns-sidebar-widget-cover__image")) : "";

			// ======= BEGIN OUTPUT ====================================================
			?>
			
				<section class="ns-sidebar-widget-cover ns-sidebar-widget-cover--book">
					<div class="ns-sidebar-widget-cover__container">
						<?php echo $thumbnail ?>
						<p class="ns-sidebar-widget-cover__title"><?php echo $title ?></p>
					</div>
				</section>

			<?php 
			// ======= END OUTPUT ======================================================
		}
	}
}
