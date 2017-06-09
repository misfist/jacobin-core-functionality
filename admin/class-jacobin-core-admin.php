<?php
/**
 * Jacobin Core Field Settings
 *
 * @package    Jacobin_Core
 * @subpackage Jacobin_Core\Admin
 * @since       0.1.4
 * @license    GPL-2.0+
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link 		https://codex.wordpress.org/Settings_API
 *
 * @package    Jacobin_Core
 * @subpackage Jacobin_Core\Admin
 * @author     Pea <pea@misfist.com>
 */
class Jacobin_Core_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.4
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.4
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The Setting Name
	 * Used for page name and setting name
	 *
	 * @since    0.1.4
	 * @access   private
	 * @var      string    $setting_name    The setting that will be registered.
	 */
	private $setting_name = 'featured_content';

	private $option_id;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.4
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->option_id = 'toplevel_page_featured-content';

		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_menu', array( $this, 'remove_meta_boxes' ) );

		//add_action( 'acf/input/admin_head', array( $this, 'modify_interview_question_field_height' ) );

		/**
		 * Add JS to admin head for ACF
		 */
		add_action( 'acf/input/admin_footer', array( $this, 'admin_footer' ) );
		add_action( 'acf/input/admin_head', array( $this, 'admin_head' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Modify custom post args
		add_filter( 'issue_register_args', array( $this, 'modify_issue_args' ), 'issue' );
		add_filter( 'timeline_register_args', array( $this, 'modify_timeline_args' ), 'timeline' );
		add_filter( 'chart_register_args', array( $this, 'modify_chart_args' ), 'chart' );

	}


	/**
	 * Add an Options Page using ACF
	 *
	 * @since 0.1.14
	 *
	 * @uses acf_add_options_page()
	 */
	public function add_options_page() {
		if( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page( array(
				'page_title' 	=> __( 'Featured Content', 'jacobin-core' ),
				'menu_title'	=> __( 'Featured Content', 'jacobin-core' ),
				'menu_slug' 	=> 'featured-content',
				'capability'	=> 'edit_posts',
				'icon_url' 		=> 'dashicons-star-filled',
				'position' 		=> 50,
				'redirect'		=> false
			) );
		}
	}

	/**
	 * Get Settings
	 * Get the name of the settings
	 *
	 * @since    0.1.2
	 */
	public function get_setting_name() {
		return $this->setting_name;
	}

	/**
	 * Descrease height of interview question editor box
	 *
	 * @return null
	 */
	public function admin_head() {

			if( 'post' == get_post_type() ) : ?>
					<style>
					.small .acf-editor-wrap iframe,
					.small .acf-editor-wrap .wp-editor-area {
						height: 150px !important;
						min-height: 150px;
					}
				</style>

				<?php endif; ?>
	<?php
	}

	/**
	 * Add Scripts and Styles to ACF Admin Head
	 *
	 * @since 0.2.7
	 *
	 * @link https://www.advancedcustomfields.com/resources/acfinputadmin_head/
	 *
	 * @return void
	 */
	public function admin_footer() {
		$current_screen = get_current_screen();
		?>

		 <?php if( $this->option_id == $current_screen->id ) : ?>

			 <script type="text/javascript">
			 (function($) {

			 })(jQuery);
			 </script>

			 <style type="text/css">
			 #home-sections .acf-relationship .list {
            height: 510px !important;
        }
			 </style>
		 <?php endif; ?>

		 <?php if( 'post' == $current_screen->base ) : ?>

		 <style type="text/css">
			#wp-content-editor-tools {
					background-color: transparent;
					padding-top: 0;
			}
		</style>

		 <?php endif; ?>

	 <?php
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.2
	 */
	public function enqueue_styles() {
		$current_screen = get_current_screen();
		/**
		 * This function is provided for demonstration purposes only.
		 */
		if( $this->option_id == $current_screen->id || 'post' == $current_screen->base ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/jacobin-core-admin.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.2
	 */
	public function enqueue_scripts() {
		$current_screen = get_current_screen();
		/**
		 * This function is provided for demonstration purposes only.
		 */
		if( $this->option_id == $current_screen->id || 'post' == $current_screen->base ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jacobin-core-admin.js', array( 'jquery' ), $this->version, true );
		}
	}

	/**
	 * Sanitize Input
	 *
	 * @since    0.1.2
	 *
	 * @param string $string
	 * @return sanitized string $string
	 */
	public function sanitize_string( $string ) {
		return sanitize_text_field( $string );
	}

	/**
	 * Modify Issue CPT Args
	 * @access  public
	 * @since   0.1.0
	 * @return  $args array
	 */
	public function modify_issue_args( $args ) {
	    $args['menu_icon'] = 'dashicons-book';
	    return $args;
	}

	/**
	 * Modify Timeline CPT Args
	 * @access  public
	 * @since    0.1.2
	 * @return  $args array
	 */
	public function modify_timeline_args( $args ) {
	    $args['menu_icon'] = 'dashicons-list-view';
	    return $args;
	}

	/**
	 * Modify Chart CPT Args
	 * @access  public
	 * @since    0.1.2
	 * @return  $args array
	 */
	public function modify_chart_args( $args ) {
	    $args['menu_icon'] = 'dashicons-chart-line';
	    return $args;
	}

	/**
	 * Remove standard WordPress metaboxes for custom taxonomies.
	 *
	 * @since  0.1.0
	 * @since 0.3.10
	 */
	function remove_meta_boxes() {
			remove_meta_box( 'seriesdiv', 'post', 'side' );
			remove_meta_box( 'formatdiv', 'post', 'side' );
			remove_meta_box( 'formatdiv', 'issue', 'side' );
			remove_meta_box( 'authordiv', 'issue', 'side' );

			/**
			 * @since 0.3.9
			 */
			remove_meta_box( 'departmentdiv', 'post', 'side' );
			remove_meta_box( 'categorydiv', 'post', 'side' );
			remove_meta_box( 'tagsdiv-post_tag', 'post', 'side' );
			remove_meta_box( 'locationdiv', 'post', 'side' );
	}

}
