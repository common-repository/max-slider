<?php

namespace MaxSlider\Select2\Controls;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use \Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use \Elementor\Utils;
use Elementor\Plugin;

class Helper
{

    public static function fix_old_query($settings)
    {
        $update_query = false;

        foreach ($settings as $key => $value) {
            if (strpos($key, 'eaeposts_') !== false) {
                $settings[str_replace('eaeposts_', '', $key)] = $value;
                $update_query = true;
            }
        }

        if ($update_query) {
            global $wpdb;

            $post_id = get_the_ID();
            $data = get_post_meta($post_id, '_elementor_data', true);
            $data = str_replace('eaeposts_', '', $data);
            $wpdb->update(
                $wpdb->postmeta,
                [
                    'meta_value' => $data,
                ],
                [
                    'post_id' => $post_id,
                    'meta_key' => '_elementor_data',
                ]
            );
        }

        return $settings;
    }

    public static function get_query_args($settings = [], $post_type = 'post')
    {
	    $settings = wp_parse_args( $settings, [
		    'post_type'      => $post_type,
		    'posts_ids'      => [],
		    'orderby'        => 'date',
		    'order'          => 'desc',
		    'posts_per_page' => 3,
		    'offset'         => 0,
		    'post__not_in'   => [],
	    ] );

	    $args = [
		    'orderby'             => $settings['orderby'],
		    'order'               => $settings['order'],
		    'ignore_sticky_posts' => 1,
		    'post_status'         => 'publish',
		    'posts_per_page'      => $settings['posts_per_page'],
		    'offset'              => $settings['offset'],
	    ];

	    if ( 'by_id' === $settings['post_type'] ) {
		    $args['post_type'] = 'any';
		    $args['post__in']  = empty( $settings['posts_ids'] ) ? [ 0 ] : $settings['posts_ids'];
	    } else {
		    $args['post_type'] = $settings['post_type'];
		    $args['tax_query'] = [];

		    $taxonomies = get_object_taxonomies( $settings['post_type'], 'objects' );

		    foreach ( $taxonomies as $object ) {
			    $setting_key = $object->name . '_ids';

			    if ( ! empty( $settings[ $setting_key ] ) ) {
				    $args['tax_query'][] = [
					    'taxonomy' => $object->name,
					    'field'    => 'term_id',
					    'terms'    => $settings[ $setting_key ],
				    ];
			    }
		    }

		    if ( ! empty( $args['tax_query'] ) ) {
			    $args['tax_query']['relation'] = 'AND';
		    }
	    }

	    if ( $args['orderby'] === 'most_viewed' ) {
		    $args['orderby']  = 'meta_value_num';
		    $args['meta_key'] = '_max_slider_post_view_count';
	    }

	    if ( ! empty( $settings['authors'] ) ) {
		    $args['author__in'] = $settings['authors'];
	    }

	    if ( ! empty( $settings['post__not_in'] ) ) {
		    $args['post__not_in'] = $settings['post__not_in'];
	    }

        if( 'product' === $post_type && function_exists('whols_lite') ){
            $args['meta_query'] = array_filter( apply_filters( 'woocommerce_product_query_meta_query', $args['meta_query'], new \WC_Query() ) );
        }

        if($settings['pagination'] == 'yes') {
            unset($args['offset']);
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args['paged'] = $paged;
        }

        return $args;
    }

    /**
     * Get All POst Types
     * @return array
     */
    public static function get_post_types()
    {
        $post_types = get_post_types(['public' => true, 'show_in_nav_menus' => true], 'objects');
        $post_types = wp_list_pluck($post_types, 'label', 'name');

        return array_diff_key($post_types, ['elementor_library', 'attachment']);
    }

    /**
     * Get all types of post.
     *
     * @param  string  $post_type
     *
     * @return array
     */
    public static function get_post_list($post_type = 'any')
    {
        return self::get_query_post_list($post_type);
    }

    /**
     * POst Orderby Options
     *
     * @return array
     */
    public static function get_post_orderby_options()
    {
	    $orderby = array(
		    'ID'            => __( 'Post ID', 'max-slider' ),
		    'author'        => __( 'Post Author', 'max-slider' ),
		    'title'         => __( 'Title', 'max-slider' ),
		    'date'          => __( 'Date', 'max-slider' ),
		    'modified'      => __( 'Last Modified Date', 'max-slider' ),
		    'parent'        => __( 'Parent Id', 'max-slider' ),
		    'rand'          => __( 'Random', 'max-slider' ),
		    'comment_count' => __( 'Comment Count', 'max-slider' ),
		    'most_viewed'   => __( 'Most Viewed', 'max-slider' ),
		    'menu_order'    => __( 'Menu Order', 'max-slider' )
	    );

        return $orderby;
    }

    /**
     * Get all Authors
     *
     * @return array
     */
	public static function get_authors_list() {
		$args = [
			'capability'          => [ 'edit_posts' ],
			'has_published_posts' => true,
			'fields'              => [
				'ID',
				'display_name',
			],
		];

		// Capability queries were only introduced in WP 5.9.
		if ( version_compare( $GLOBALS['wp_version'], '5.9-alpha', '<' ) ) {
			$args['who'] = 'authors';
			unset( $args['capability'] );
		}

		$users = get_users( $args );

		if ( ! empty( $users ) ) {
			return wp_list_pluck( $users, 'display_name', 'ID' );
		}

		return [];
	}

    public static function get_dynamic_args(array $settings, array $args)
    {
	    if ( $settings['post_type'] === 'source_dynamic' && ( is_archive() || is_search() ) ) {
            $data = get_queried_object();

            if (isset($data->post_type)) {
                $args['post_type'] = $data->post_type;
                $args['tax_query'] = [];
            } else {
                global $wp_query;
                $args['post_type'] = $wp_query->query_vars['post_type'];
                if(!empty($wp_query->query_vars['s'])){
                    $args['s'] = $wp_query->query_vars['s'];
                    $args['offset'] = 0;
                }
            }

            if ( isset( $data->taxonomy ) ) {
                $args[ 'tax_query' ][] = [
                    'taxonomy' => $data->taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $data->term_id,
                ];
            }

            if ( isset($data->taxonomy) ) {
                $args[ 'tax_query' ][] = [
                    'taxonomy' => $data->taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $data->term_id,
                ];
            }

            if (get_query_var('author') > 0) {
                $args['author__in'] = get_query_var('author');
            }

            if (get_query_var('s')!='') {
                $args['s'] = get_query_var('s');
            }

            if (get_query_var('year') || get_query_var('monthnum') || get_query_var('day')) {
                $args['date_query'] = [
                    'year' => get_query_var('year'),
                    'month' => get_query_var('monthnum'),
                    'day' => get_query_var('day'),
                ];
            }

            if (!empty($args['tax_query'])) {
                $args['tax_query']['relation'] = 'AND';
            }

            $args[ 'meta_query' ] = [ 'relation' => 'AND' ];
            $show_stock_out_products = isset( $settings['max_slider_product_out_of_stock_show'] ) ? $settings['max_slider_product_out_of_stock_show'] : 'yes';

            if ( get_option( 'woocommerce_hide_out_of_stock_items' ) == 'yes' || 'yes' !== $show_stock_out_products  ) {
                $args[ 'meta_query' ][] = [
                    'key'   => '_stock_status',
                    'value' => 'instock'
                ];
            }
            if( 'product' === $args['post_type'] && function_exists('whols_lite') ){
                $args['meta_query'] = array_filter( apply_filters( 'woocommerce_product_query_meta_query', $args['meta_query'], new \WC_Query() ) );
            }
        }

        return $args;
    }

    public static function get_query_post_list($post_type = 'any', $limit = -1, $search = '')
    {
        global $wpdb;
        $where = '';
        $data = [];

        if (-1 == $limit) {
            $limit = '';
        } elseif (0 == $limit) {
            $limit = "limit 0,1";
        } else {
            $limit = $wpdb->prepare(" limit 0,%d", esc_sql($limit));
        }

        if ('any' === $post_type) {
            $in_search_post_types = get_post_types(['exclude_from_search' => false]);
            if (empty($in_search_post_types)) {
                $where .= ' AND 1=0 ';
            } else {
                $where .= " AND {$wpdb->posts}.post_type IN ('" . join("', '",
                    array_map('esc_sql', $in_search_post_types)) . "')";
            }
        } elseif (!empty($post_type)) {
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_type = %s", esc_sql($post_type));
        }

        if (!empty($search)) {
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", '%' . esc_sql($search) . '%');
        }

        $query = "select post_title,ID  from $wpdb->posts where post_status = 'publish' $where $limit";
        $results = $wpdb->get_results($query);
        if (!empty($results)) {
            foreach ($results as $row) {
                $data[$row->ID] = $row->post_title;
            }
        }
        return $data;
    }
}
