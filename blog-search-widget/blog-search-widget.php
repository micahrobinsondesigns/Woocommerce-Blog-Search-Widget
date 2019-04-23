<?php
/**
 * @wordpress-plugin
 * @category Widgets
 * @extends \WP_Widget
 * Plugin Name:       Blog Search Widget
 * Description:       Creates a Blog Search that only returns blog posts.
 * Version:           1.2
 * Author:            Micah Robinson
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       blog-search-wdiget
 WC tested up to: 3.6
 */

/** Die if accessed directly
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		// ONLY RUN IF WOOCOMMERCE IS ACTIVE....
	include_once( WP_PLUGIN_DIR . '/' . 'woocommerce/includes/abstracts/abstract-wc-widget.php' );
		class WC_Widget_Blog_Search extends WC_Widget {
			/**
			 * Constructor.
			 */

			public function __construct() {
				$this->widget_cssclass    = 'woocommerce widget_blog_search';
				$this->widget_description = __( 'A search form for your blog.', 'woocommerce' );
				$this->widget_id          = 'woocommerce_blog_search';
				$this->widget_name        = __( 'Blog Search', 'woocommerce' );
				$this->settings           = array(
					'title'  => array(
						'type'  => 'text',
						'std'   => '',
						'label' => __( 'Title', 'woocommerce' ),
					),
				);
				parent::__construct();
			}

		/**
		 * Render the widget
		 *
		 * @since 1.0.0
		 * @see WP_Widget::widget()
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {

			// get the widget configuration
	    $title = $instance['title'];
	    $placeholder = $instance['placeholder'];

			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . wp_kses_post( $title ) . $args['after_title'];
			}

			// Create Blog Search Form
				//get_blog_search_form();
				//error_log('searching');
				?>
				<form role="search" method="get" class="woocommerce-blog-search search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="screen-reader-text" for="woocommerce-blog-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"><?php esc_html_e( 'Search for:', 'woocommerce' ); ?></label>
					<input type="search" id="woocommerce-blog-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>" class="search-field" placeholder="<?php if ( $placeholder ) { echo wp_kses_post( $placeholder ); } else { echo esc_attr__( 'Search blog posts&hellip;', 'woocommerce' ); }
	        ?>" value="<?php echo get_search_query(); ?>" name="s" />
					<button type="submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'woocommerce' ); ?>" class="search-submit"><svg class="icon icon-search" aria-hidden="true" role="img"> <use href="#icon-search" xlink:href="#icon-search"></use> </svg><span class="screen-reader-text">Search</span></button>
					<input type="hidden" name="post_type" value="post" />
				</form>
				<?php

			echo $args['after_widget'];
		}

		/**
		 * Update the widget title & selected product
		 *
		 * @since 1.0.0
		 * @see WP_Widget::update()
		 * @param array $new_instance new widget settings
		 * @param array $old_instance old widget settings
		 * @return array updated widget settings
		 */
		public function update( $new_instance, $old_instance ) {
	    $instance['title'] = strip_tags( $new_instance['title'] );
	    $instance['placeholder'] = strip_tags( $new_instance['placeholder'] );
			return $instance;
		}

		/**
		 * Render the admin form for the widget
		 *
		 * @since 1.0.0
		 * @see WP_Widget::form()
		 * @param array $instance the widget settings
		 * @return string|void
		 */
		public function form( $instance ) {
			?>
	    <p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'blog-search-widget' ) ?>:</label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( isset( $instance['title'] ) ? $instance['title'] : '' ); ?>" />
			</p>
	    <p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>"><?php _e( 'Search Placeholder', 'blog-search-widget' ) ?>:</label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'placeholder' ) ); ?>" value="<?php echo esc_attr( isset( $instance['placeholder'] ) ? $instance['placeholder'] : '' ); ?>" />
			</p>
			<?php
		}

		/**
		 * Display blog search form.
		 *
		 * Will first attempt to locate the blog-searchform.php file in either the child or
		 * the parent, then load it. If it doesn't exist, then the default search form.
		 * will be displayed.
		 *
		 * The default searchform uses html5.
		 *
		 * @param bool $echo (default: true).
		 * @return string
		 */
		public function get_blog_search_form( $echo = true ) {
			global $blog_search_form_index;
			ob_start();
			if ( empty( $blog_search_form_index ) ) {
				$blog_search_form_index = 0;
			}
			do_action( 'pre_get_blog_search_form' );
			wc_get_template( 'blog-searchform.php', array(
				'index' => $blog_search_form_index++,
			) );
			$form = apply_filters( 'get_blog_search_form', ob_get_clean() );
			if ( $echo ) {
				echo $form; // WPCS: XSS ok.
			} else {
				return $form;
			}
		}

		public function modify_blog_search_query( $query ) {
		  // Make sure this isn't the admin or is the main query
			if( is_admin() || ! $query->is_main_query() ) {
				return;
			}

			// Make sure this isn't the WooCommerce product search form
			if( isset($_GET['post_type']) && ($_GET['post_type'] == 'product') ) {
				return;
			}

			if( $query->is_search() ) {
				$in_search_post_types = get_post_types( array( 'exclude_from_search' => false ) );

				// The post types you're removing
				$post_types_to_remove = array( 'product', 'page' );

				foreach( $post_types_to_remove as $post_type_to_remove ) {
					if( is_array( $in_search_post_types ) && in_array( $post_type_to_remove, $in_search_post_types ) ) {
						unset( $in_search_post_types[ $post_type_to_remove ] );
						$query->set( 'post_type', $in_search_post_types );
					}
				}
			}
		}
	} // end \WC_Widget_Blog_Search class
	/**
	 * Registers the new widget to add it to the available widgets
	 *
	 * @since 1.0.0
	 */
	function wc_active_blog_search_register_widget() {
		register_widget( 'WC_Widget_Blog_Search' );
	}
	add_action( 'widgets_init', 'wc_active_blog_search_register_widget' );
	}
