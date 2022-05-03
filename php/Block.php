<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block ) {
		$html       = '';
		$post_types = get_post_types( array( 'public' => true ) );
		$class_name = $attributes['className'];
		ob_start();

		?>
		<div class="<?php echo esc_attr( $class_name ); ?>">
			<h2><?php echo esc_html( __( 'Post Counts', 'site-counts' ) ); ?></h2>
			<ul>
		<?php
		if ( ! empty( $post_types ) ) :
			foreach ( $post_types as $post_type_slug ) :
				$post_type_object = get_post_type_object( $post_type_slug );
				$post_count       = count(
					get_posts(
						array(
							'post_type'      => $post_type_slug,
							'posts_per_page' => -1,
						)
					)
				);

				/* translators: %1$d post count, %2$s label name */
				$count_text = sprintf( __( 'There are %1$d %2$s.', 'site-counts' ), $post_count, $post_type_object->labels->name );
				?>
					<li><?php echo esc_html( $count_text ); ?></li>
				<?php
				endforeach;
				endif;
		?>
			</ul>
			<p>
			<?php
			/* translators: %d post id */
			$count_text = sprintf( __( 'The current post ID is %d.', 'site-counts' ), $_GET['post_id'] );
			echo esc_html( $count_text );
			?>
			</p>

			<?php
			$query = new WP_Query(
				array(
					'post_type'     => array( 'post', 'page' ),
					'post_status'   => 'any',
					'date_query'    => array(
						array(
							'hour'    => 9,
							'compare' => '>=',
						),
						array(
							'hour'    => 17,
							'compare' => '<=',
						),
					),
					'tag'           => 'foo',
					'category_name' => 'baz',
					'post__not_in'  => array( get_the_ID() ),
				)
			);

			if ( $query->have_posts() ) :
				?>
				<h2><?php echo esc_html( __( '5 posts with the tag of foo and the category of baz', 'site-counts' ) ); ?></h2>
				<ul>
				<?php

				foreach ( array_slice( $query->posts, 0, 5 ) as $post ) :
					?>
					<li><?php echo esc_html( $post->post_title ); ?></li>
					<?php
				endforeach;
			endif;
			?>
			</ul>
		</div>
		<?php

		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
