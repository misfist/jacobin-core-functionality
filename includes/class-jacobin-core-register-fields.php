<?php
/**
 * Jacobin Core Register Fields with REST API
 *
 * @package    Jacobin_Core
 * @subpackage Jacobin_Core\Includes
 * @since      0.1.0
 * @license    GPL-2.0+
 */

/**
 * Register Fields with REST API
 *
 * This file registers fields with REST API.
 *
 * @since 0.1.0
 */
class Jacobin_Rest_API_Fields {

    /**
     * Initialize all the things
     *
     * @since 0.1.0
     */
    function __construct () {
        /**
         * Filters to have fields returned in `custom_fields` instead of `acf`.
         */
        add_filter( 'acf/rest_api/post/get_fields', array( $this, 'set_custom_field_base' ) );
        add_filter( 'acf/rest_api/issue/get_fields', array( $this, 'set_custom_field_base' ) );
        add_filter( 'acf/rest_api/term/get_fields', array( $this, 'set_custom_field_base' ) );
        add_filter( 'acf/rest_api/timeline/get_fields', array( $this, 'set_custom_field_base' ) );
        add_filter( 'acf/rest_api/chart/get_fields', array( $this, 'set_custom_field_base' ) );

        /**
         * Modify Responses
         */
        add_filter( 'rest_prepare_post', array( $this, 'modify_taxonomy_response' ), 10, 3 );

        /**
         * Register Fields
         */
        add_action( 'rest_api_init', array( $this, 'register_fields' ) );

    }

    /**
     * Register the custom fields
     *
     * @since 0.1.0
     */
    function register_fields () {
        if ( function_exists( 'register_rest_field' ) ) {

            register_rest_field( 'post',
                'subhead',
                array(
                    'get_callback'    => array( $this, 'get_field' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'post',
                'authors',
                array(
                    'get_callback'    => array( $this, 'get_authors' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'issue',
                'authors',
                array(
                    'get_callback'    => array( $this, 'get_authors' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'issue',
                'cover_artist',
                array(
                    'get_callback'    => array( $this, 'get_guest_author' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'post',
                'translator',
                array(
                    'get_callback'    => array( $this, 'get_guest_author' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'post',
                'interviewer',
                array(
                    'get_callback'    => array( $this, 'get_interviewer' ),
                    'update_callback' => null,
                    'schema'          => null
                )
            );

            register_rest_field( 'post',
                'featured_image_secondary',
                array(
                    'get_callback'    => array( $this, 'get_featured_image_secondary' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'post',
                'related_articles',
                array(
                    'get_callback'    => array( $this, 'get_related_articles' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'issue',
                'articles',
                array(
                    'get_callback'    => array( $this, 'get_issue_articles' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'department',
                'term_meta',
                array(
                    'get_callback'    => array( $this, 'get_term_meta' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

            register_rest_field( 'category',
                'term_meta',
                array(
                    'get_callback'    => array( $this, 'get_term_meta' ),
                    'update_callback' => null,
                    'schema'          => null,
                )
            );

        }
    }

    /**
     * Modify Response Data Returned for Taxonomies
     * By default the REST API returns only the taxonomy ID in the post response.
     * We want to get more information in the response
     *
     * @since 0.1.12
     *
     * @param {array} $data
     * @param {obj} $post
     * @param {array} $request
     *
     * @return {array} $data
     */
    function modify_taxonomy_response ( $data, $post, $request ) {
        $_data = $data->data;

        $taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

        foreach( $taxonomies as $taxonomy => $details ) {
            $label = strtolower( str_replace( ' ', '_', $details->labels->name ) );

            if( isset( $_data[$label] ) ) {
                $args = array(
                    'orderby'   => 'parent'
                );

                $terms = wp_get_post_terms( $post->ID, $taxonomy, $args );

                foreach( $terms as $term ) {
                    $term_detail = get_term( $term->term_id, $taxonomy, ARRAY_A );
                    $term_meta = get_term_meta( $term->term_id );

                    $_data[$label] = array_merge( $term_detail, $term_meta );

                }
            }
        }

        $data->data = $_data;
        return $data;
    }

    /**
     * Get post meta
     *
     * @since 0.1.0
     *
     * @param object $object
     * @param string $field_name
     * @param string $request
     * @return string meta
     *
     */
    function get_field ( $object, $field_name, $request ) {
        return get_post_meta( $object[ 'id' ], $field_name, true );
    }

    /**
     * Get term meta
     *
     * @since 0.1.0
     *
     * @param object $object
     * @param string $field_name
     * @param string $request
     * @return array meta
     *
     */
    function get_term_meta( $object, $field_name, $request ) {
        return get_term_meta( $object[ 'id' ] );
    }

    /**
     * Get secondary featured image
     *
     * @since 0.1.0
     *
     * @uses  get_post_meta()
     * @uses  get_post()
     * @uses  get_post_meta()
     *
     * @param {object} $object
     * @param {string} $field_name
     * @param {string} $request
     * @return {array} $authors
     */
    public function get_featured_image_secondary ( $object, $field_name, $request ) {

        $post_id = $object['id'];
        $image_meta = get_post_meta( $post_id, $field_name, true );

        if( !empty( $image_meta ) ) {

            $image_id = (int) $image_meta;
            $post_data = get_post( $image_id );

            $featured_image_secondary = array(
                'id'            => $post_data->ID,
                'title'         => array(
                    'rendered'  => $post_data->post_title
                ),
                'alt_text'      => get_post_meta( $image_id  , '_wp_attachment_image_alt', true ),
                'description'   => $post_data->post_content,
                'caption'       => $post_data->post_excerpt,
                'link'          => wp_get_attachment_url( $image_id ),
                'author'        => (int) $post_data->post_author,
                'media_details' => wp_get_attachment_metadata( $image_id ),
            );

            return $featured_image_secondary;
        }

        return false;

    }

    /**
     * Get issue articles
     *
     * @since 0.1.0
     *
     * @uses  get_post_meta()
     * @uses  get_post()
     * @uses  jacobin_the_excerpt()
     * @uses  get_authors_array()
     *
     * @param {object} $object
     * @param {string} $field_name
     * @param {string} $request
     * @return {array} $articles
     *
     */
    public function get_issue_articles ( $object, $field_name, $request ) {
        $meta = get_post_meta( $object['id'], 'article_issue_relationship', true );
        $articles = [];

        $args = array(
            'post__in' => $meta
        );

        $posts = get_posts( $args );

        if( !empty( $posts ) ) {
            foreach( $posts as $post ) {

                $article = array(
                    'id'        => (int) $post->ID,
                    'title'     => array(
                        'rendered'  => $post->post_title,
                    ),
                    'slug'      => $post->post_name,
                    'content'   => array(
                        'rendered'  => $post->post_content,
                    ),
                    'excerpt'   => array(
                        'rendered'    => jacobin_the_excerpt( $post->ID ),
                    )
                );

                $image_id = ( !empty( get_post_thumbnail_id( $post->ID ) ) ) ? (int) get_post_thumbnail_id( $post->ID ) : null;

                $image_data = get_post( $image_id );
                $image_meta = array(
                    'id'            => $image_id,
                    'title'         => array(
                        'rendered'  => $image_data->post_title
                    ),
                    'alt_text'      => get_post_meta( $image_id  , '_wp_attachment_image_alt', true ),
                    'description'   => $image_data->post_content,
                    'caption'       => $image_data->post_excerpt,
                    'link'          => wp_get_attachment_url( $image_id ),
                    'media_details' => wp_get_attachment_metadata( $image_id ),
                );

                $article['featured_image'] = $image_meta;

                $coauthors =  $this->get_authors_array( $post->ID );
                $article['authors'] = $coauthors;

                array_push( $articles, $article );

            }

        }
        return $articles;
    }

    /**
     * Get related articles
     *
     * @since 0.1.13
     *
     * @uses  get_post_meta()
     * @uses  get_post()
     * @uses  jacobin_the_excerpt()
     * @uses  get_authors_array()
     *
     * @param {object} $object
     * @param {string} $field_name
     * @param {string} $request
     * @return {array} $articles
     *
     */
    public function get_related_articles ( $object, $field_name, $request ) {
        $meta = get_post_meta( $object['id'], 'related_articles', true );
        $articles = [];

        $args = array(
            'post__in' => $meta
        );

        $posts = get_posts( $args );

        if( !empty( $posts ) ) {
            foreach( $posts as $post ) {

                $article = array(
                    'id'        => (int) $post->ID,
                    'title'     => array(
                        'rendered'  => $post->post_title,
                    ),
                    'slug'      => $post->post_name,
                    'content'   => array(
                        'rendered'  => $post->post_content,
                    ),
                    'excerpt'   => array(
                        'rendered'    => jacobin_the_excerpt( $post->ID ),
                    ),
                );

                $coauthors =  $this->get_authors_array( $post->ID );
                $article['authors'] = $coauthors;

                $image_id = ( !empty( get_post_thumbnail_id( $post->ID ) ) ) ? (int) get_post_thumbnail_id( $post->ID ) : null;

                $image_data = get_post( $image_id );
                $image_meta = array(
                    'id'            => $image_id,
                    'title'         => array(
                        'rendered'  => $image_data->post_title
                    ),
                    'alt_text'      => get_post_meta( $image_id  , '_wp_attachment_image_alt', true ),
                    'description'   => $image_data->post_content,
                    'caption'       => $image_data->post_excerpt,
                    'link'          => wp_get_attachment_url( $image_id ),
                    'media_details' => wp_get_attachment_metadata( $image_id ),
                );

                $article['featured_image'] = $image_meta;

                $article['departments'] = wp_get_post_terms( $post->ID, 'department', array( 'orderby'   => 'parent' ) );

                array_push( $articles, $article );

            }

        }
        return $articles;
    }

    /**
     * Get coauthors
     *
     * @since 0.1.0
     *
     * @param {object} $object
     * @param {string} $field_name
     * @param {string} $request
     * @return {array} $authors
     *
     */
    public function get_authors ( $object, $field_name, $request ) {

        return $this->get_authors_array ( $object['id'] );
    }

    /**
     * Create array of authors
     *
     * @since 0.1.0
     *
     * @param object $object->ID
     * @return array $authors
     *
     */
    public function get_authors_array( $object_id ) {

        global $coauthors_plus;

        if ( function_exists( 'get_coauthors' ) ) {
            $coauthors = get_coauthors ( $object_id );
            $authors = [];

            foreach( $coauthors as $coauthor ) {

                $user_id = $coauthor->ID;

                $author = [];

                if( array_key_exists( 'data', $coauthor ) && 'wpuser' == $coauthor->data->type ) {
                    $user_meta = get_userdata( $user_id );

                    $author = array(
                        'id'            => (int) $user_id,
                        'name'          => $user_meta->display_name,
                        'first_name'    => $user_meta->first_name,
                        'last_name'     => $user_meta->last_name,
                        'description'   => $user_meta->description,
                        'link'          => get_author_posts_url( $user_id )
                    );
                }
                elseif( 'guest-author' == $coauthor->type ) {
                    $author = array(
                        'id'            => (int) $user_id,
                        'name'          => $coauthor->display_name,
                        'first_name'    => $coauthor->first_name,
                        'last_name'     => $coauthor->last_name,
                        'description'   => $coauthor->description,
                        'link'          => ( !empty( $coauthor->user_login ) ) ? get_author_posts_url( $user_id ) . $coauthor->user_login . '/' : null,
                    );
                }

                array_push( $authors, $author );

            }
            return $authors;
        }

        return false;
    }

    /**
     * Get guest author
     *
     * @since 0.1.7
     *
     * @param   {object} $object
     * @param   {string} $field_name
     * @param   {string} $request
     * @return  {array} $guest_author details
     *
     */
    public function get_guest_author( $object, $field_name, $request ) {

        $guest_author_id = get_post_meta( $object['id'], $field_name, true );

        if( empty( $guest_author_id ) ) {
            return false;
        }

        return $this->get_guest_author_meta( $guest_author_id );

    }

    /**
     * Get interviewer
     * @param  {obj} $object
     * @param  {string} $field_name
     * @param  {array} $request
     * @return {array} get_guest_author_meta || false
     */
    public function get_interviewer( $object, $field_name, $request ) {

        $interviewer_array = get_post_meta( $object['id'], 'interviewer', true );

        if( !empty( $interviewer_array ) ) {

            $interviewers = [];

            foreach( $interviewer_array as $interviewer ) {
                array_push( $interviewers, $this->get_guest_author_meta( (int) $interviewer ) );
            }

            return $interviewers;

        }

        return false;

    }

    /**
     * Get Guest Author Meta
     * Guest author is a custom post type created by the Co-authors Plus plugin
     * @since 0.1.7
     *
     * @link https://github.com/Automattic/Co-Authors-Plus
     *
     * @param {int} $user_id
     * @return {array} $user_details
     */
    public function get_guest_author_meta( $user_id ) {

        $user_id = is_array( $user_id ) ? $user_id[0] : $user_id;
        $user_id = (int) $user_id;

        $user_details = array(
            'id'            => $user_id,
            'name'          => get_post_meta( $user_id, 'cap-display_name', true ),
            'first_name'    => get_post_meta( $user_id, 'cap-first_name', true ),
            'last_name'     => get_post_meta( $user_id, 'cap-last_name', true ),
            'description'   => get_post_meta( $user_id, 'cap-description', true ),
            'website'       => esc_url( get_post_meta( $user_id, 'cap-website', true ) ),
            'link'          => ( get_post_meta( $user_id, 'cap-user_login', true ) ) ? esc_url( get_author_posts_url( $user_id ) . get_post_meta( $user_id, 'cap-user_login', true ) . '/' ) : false,
        );

        if( !empty( $user_details ) ) {
            return $user_details;
        }

        return false;

    }

    /**
     * Change Base Label of Custom Fields
     *
     * Advanced Custom Fields fields are displayed within `acf`.
     *
     * @link https://github.com/airesvsg/acf-to-rest-api/issues/41#issuecomment-222460783
     *
     * @param array $data
     * @return modified array $data
     *
     * @since 0.1.0
     */
    public function set_custom_field_base ( $data ) {
        if ( method_exists( $data, 'get_data' ) ) {
            $data = $data->get_data();
        } else {
            $data = (array) $data;
        }

        if ( isset( $data['acf'] ) ) {
            $data['custom_fields'] = $data['acf'];
            unset( $data['acf'] );
        }
        return $data;
    }

    /**
     * Change response field to `custom_fields`.
     *
     * @since 0.1.0
     *
     * @param $response
     * @param $object
     * @return modified $response
     *
     * @link http://v2.wp-api.org/extending/custom-content-types/
     */
    public function rest_prepare_term ( $response, $object ) {
        if ( $object instanceof WP_Term && function_exists( 'get_fields' ) ) {
            if ( isset( $data['acf'] ) ) {
                $data['custom_fields'] = $data['acf'];
                unset( $data['acf'] );
            }
            $response->data['custom_fields'] = get_fields( $object->taxonomy . '_' . $object->term_id );
        }

        return $response;
    }

}

new Jacobin_Rest_API_Fields();
