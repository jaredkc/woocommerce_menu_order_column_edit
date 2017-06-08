<?php
/**
 * Plugin Name: WooCommerce Menu Order Admin Column and Edit
 * Plugin URI:
 * Description: View and quickly update WooCommerce product menu orders in the admin.
 * Version:     1.0
 * Author:      Jared Cornwall
 * Author URI:  http://jaredcornwall.com
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 */

add_action( 'admin_init', array( WC_Menu_Order_Column_Edit::get_instance(), 'init_plugin' ), 20 );
class WC_Menu_Order_Column_Edit {
	/**
	 * Stores the class instance.
	 *
	 * @var WC_Menu_Order_Column_Edit
	 */
	private static $instance = null;

	/**
	 * Returns the instance of this class.
	 *
	 * It's a singleton class.
	 *
	 * @return WC_Menu_Order_Column_Edit The instance
	 */
	public static function get_instance() {

		if ( ! self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Initialises the plugin.
	 */
	public function init_plugin() {
		$this->init_hooks();
	}

	/**
	 * Initialises the WP actions.
	 *  - admin_print_scripts
	 */
	private function init_hooks() {
		if ( ! current_user_can('administrator') ) {
			return;
		}

		add_filter( 'manage_product_posts_columns', array( $this, 'jc_product_order_column') );
		add_action( 'manage_product_posts_custom_column', array( $this, 'jc_product_order_value') );
		add_filter( 'manage_edit-product_sortable_columns', array( $this, 'jc_product_order_sort' ) );

		add_action( 'admin_head', array( $this, 'jc_product_order_css') );
		add_action( 'admin_enqueue_scripts', array( $this, 'jc_product_order_js' ) );

		add_action( 'wp_ajax_jc_update_menu_order', array( $this, 'jc_update_menu_order') );
		add_action( 'wp_ajax_nopriv_jc_update_menu_order', array( $this, 'jc_update_menu_order') );

	}

	/**
	 * Add order column to admin listing screen for products
	 */
	public function jc_product_order_column($columns) {
		$new_columns = array(
			'menu_order' => __('Menu order', 'woocommerce'),
		);
		return array_merge($columns, $new_columns);
	}

	/**
	 * Menu order column data
	 */
	public function jc_product_order_value($name) {
		global $post;

		switch ($name) {
			case 'menu_order':
			echo '<div class="jc-menu-order-set"><input data-product="' . $post->ID . '" class="jc-menu-order" type="number" value="' . $post->menu_order . '"><a href="#" class="jc-menu-order-update">Update</a></div>';
			break;
		default:
			break;
		}
	}

	/**
	 * Make menu order sortable
	 */
	public function jc_product_order_sort( $columns ) {
		$columns['menu_order'] = 'menu_order';
		return $columns;
	}

	/**
	 * Ajax update menu order
	 */
	function jc_update_menu_order() {
		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_POST) ) {
			$data = array(
				'ID'           => $_POST['product_id'],
				'menu_order'   => $_POST['menu_order'],
			);
			wp_update_post( $data );

			// If you're debugging, it might be useful to see what was sent in the $_REQUEST
			// print_r($_REQUEST);
		}
		// Always die in functions echoing ajax content
		die();
	}

	/**
	 * Styles
	 */
	function jc_product_order_css() {
		global $post_type;
		if( 'product' !== $post_type ) {
			return;
		}

		echo '<style>
			.jc-menu-order-set input.jc-menu-order {
				border: 0;
				background: transparent;
				box-shadow: none;
				cursor: pointer;
				display: inline-block;
				width: 5em;
				max-width: 100%;
			}
			.jc-menu-order-set.jc-active input.jc-menu-order {
				border: 1px solid #ddd;
				background: #fff;
				box-shadow: inset 0 1px 2px rgba( 0, 0, 0, 0.07 );
			}
			.jc-menu-order-update {
				display: none;
				padding-left: 5px;
				padding-right: 5px;
			}
			.jc-menu-order-set.jc-active .jc-menu-order-update {
				display: inline-block;
			}
			// Do not use WC sort products, does not play nicely with desired menu_order
			ul.subsubsub li.byorder {
				display: none;
			}
		</style>';
	}

	/**
	 * Scripts
	 */
	function jc_product_order_js() {
		global $post_type;
		if( 'product' !== $post_type ) {
			return;
		}

		wp_enqueue_script( 'jc-menu-order-js', plugins_url( 'scripts.js', __FILE__ ) );
		wp_localize_script( 'jc-menu-order-js', 'jc_update_menu_order', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	}

}
