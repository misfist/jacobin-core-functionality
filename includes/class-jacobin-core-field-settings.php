<?php
/**
 * Jacobin Core Field Settings
 *
 * @package    Jacobin_Core
 * @subpackage Jacobin_Core\Includes
 * @since      0.1.0
 * @license    GPL-2.0+
 */

/**
 * Register and Set-up Fields
 *
 *
 * @since 0.1.0
 */
class Jacobin_Field_Settings {

    /**
     * The single instance of Jacobin_Field_Settings.
     * @var     object
     * @access  private
     * @since   1.0.0
     */
    private static $_instance = null;

    /**
     * Initialize all the things
     *
     * @since 0.1.0
     */
    function __construct() {
        /**
         * Remove Metaboxes
         *
         * @since 0.1.0
         *
         * @link https://codex.wordpress.org/Function_Reference/remove_meta_box
         */
        add_filter( 'acf/fields/flexible_content/no_value_message', array( $this, 'modify_acf_flexible_content_message' ) );

        add_filter( 'acf/fields/relationship/query/name=translator', array( $this, 'relationship_options_filter' ), 10, 3 );
        add_filter( 'acf/fields/relationship/query/name=interviewer', array( $this, 'relationship_options_filter' ), 10, 3 );
        add_filter( 'acf/fields/relationship/query/name=name', array( $this, 'relationship_options_filter' ), 10, 3 );
        add_filter( 'acf/fields/relationship/query/name=cover_artist', array( $this, 'relationship_options_filter' ), 10, 3 );

        add_filter( 'acf/fields/relationship/query/name=article_issue_relationship', array( $this, 'relationship_options_filter' ), 10, 3 );
        add_filter( 'acf/fields/relationship/query/name=related_articles', array( $this, 'relationship_options_filter' ), 10, 3 );
        add_filter( 'acf/fields/relationship/query/name=featured_article', array( $this, 'relationship_options_filter' ), 10, 3 );
        add_filter( 'acf/fields/relationship/query/name=home_content', array( $this, 'relationship_options_filter' ), 10, 3 );
        add_filter( 'acf/fields/relationship/query/name=editors_pick', array( $this, 'relationship_options_filter' ), 10, 3 );

        /**
         * Filter Default WP media markup
         *
         * @since 0.3.12
         */
        add_filter( 'img_caption_shortcode_width', '__return_false' );
        add_filter( 'wp_calculate_image_srcset', '__return_false' );

        add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );

        /**
         * Format text fields
         * @since 0.4.5
         */
        add_filter( 'acf/format_value/type=text', array( $this, 'format_text_field' ), 10, 3 );

        /**
         * Filter to include ACF fields for revisions
         * @since 0.5.1
         */
        if( function_exists( 'get_fields' ) ) {
          add_filter( 'rest_prepare_revision', array( $this, 'rest_prepare_revision' ), 10, 2 );
        }

     }

    /**
     * Register the custom fields
     *
     * @since 0.1.0
     */
    function register() {}

    /**
     * Format Text Fields
     * Apply `the_title` format filters to text fields
     *
     * @since 0.4.5
     *
     * @link https://www.advancedcustomfields.com/resources/acf-format_value/
     *
     * @param string $value
     * @param int $post_id
     * @param  $field
     *
     * @return string $value
     */
    public function format_text_field( $value, $post_id, $field ) {
      $value = apply_filters( 'the_title', $value );
      return $value;
    }

    /**
     * Add Feature Support
     *
     * @since 0.3.15
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/
     *
     * @return void
     */
    public function add_theme_support() {
      add_theme_support( 'post-thumbnails' );
      add_theme_support( 'custom-logo' );
      add_theme_support( 'html5', array( 'gallery', 'caption' ) );
    }

    /**
     * Filter out unpublished posts
     * Relationship fields will only show posts where `post_status = publish`
     *
     * @since 0.2.8
     *
     * @access public
     *
     * @param  array $options
     * @param  string $field
     * @param  obj $the_post
     * @return array $options
     */
    public function relationship_options_filter( $options, $field, $the_post ) {

    	$options['post_status'] = array( 'publish' );

    	return $options;
    }

    /**
     * Change Flexible Content Text
     *
     * @param string $no_value_message
     * @return string $no_value_message
     */
    public function modify_acf_flexible_content_message( $no_value_message ) {
        $no_value_message = __( 'Click the "%s" button below to add a section', 'jacobin-core' );

        return $no_value_message;
    }

    /**
     * Include ACF Fields
     * Add ACF fields to revision post data
     *
     * @since 0.5.1
     *
     * @param  obj $response [description]
     * @param  obj $post     [description]
     * @return obj $data
     */
    function rest_prepare_revision( $response, $post ) {
      $data = $response->get_data();
      $data['acf'] = get_fields( $post->ID );

      return rest_ensure_response( $data );
    }


}

new Jacobin_Field_Settings();
