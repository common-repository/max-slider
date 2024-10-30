<?php

namespace MaxSlider\Select2;

defined('ABSPATH') || exit(); // Exit if accessed directly

/**
 *  Elementor extra features
 */
class Max_slider_select2
{

    public function __construct()
    {
        // Max Slider select2
        add_action('elementor/controls/register', array($this, 'register_controls'));
        add_action('wp_ajax_max_slider_select2_search_post', [$this, 'select2_ajax_posts_filter_autocomplete']);
        add_action('wp_ajax_max_slider_select2_get_title', [$this, 'select2_ajax_get_posts_value_titles']);
    }

    public function register_controls($controls_manager)
    {
        $controls_manager->register_control('max-slider-select2', new \MaxSlider\Select2\Controls\Select2());
    }

    public static function get_query_post_list($post_type = 'any', $limit = -1, $search = '', $meta_query = [])
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
                $where .= " AND {$wpdb->posts}.post_type IN ('" . join("', '", array_map('esc_sql', $in_search_post_types)) . "')";
            }
        } elseif (!empty($post_type)) {
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_type = %s", esc_sql($post_type));
        }

        if (!empty($search)) {
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", '%' . esc_sql($search) . '%');
        }

        // Process Meta Query
        if (!empty($meta_query)) {
            $where_meta = [];
            foreach ($meta_query as $mq) {
                $meta_key = esc_sql($mq['key']);
                $meta_value = esc_sql($mq['value']);
                $meta_compare = isset($mq['compare']) ? esc_sql($mq['compare']) : '=';
                $where_meta[] = "{$wpdb->postmeta}.meta_key = '$meta_key' AND {$wpdb->postmeta}.meta_value $meta_compare '$meta_value'";
            }
            if (!empty($where_meta)) {
                $where .= " AND ( " . join(' OR ', $where_meta) . " )";
            }
        }

        $query = "select post_title,ID from $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id where post_status = 'publish' $where $limit";
        $results = $wpdb->get_results($query);
        if (!empty($results)) {
            foreach ($results as $row) {
                $data[$row->ID] = $row->post_title;
            }
        }
        return $data;
    }


    public function select2_ajax_posts_filter_autocomplete()
    {
        $post_type   = 'post';
        $source_name = 'post_type';

        if (!empty($_POST['post_type'])) {
            $post_type = sanitize_text_field($_POST['post_type']);
        }

        if (!empty($_POST['source_name'])) {
            $source_name = sanitize_text_field($_POST['source_name']);
        }

        $search  = !empty($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
        $results = $post_list = [];
        $meta_query = !empty($_POST['meta_query']) ? $_POST['meta_query'] : '';
        switch ($source_name) {
            case 'taxonomy':
                $args = [
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'search'     => $search,
                    'number'     => '5',
                ];

                if ($post_type !== 'all') {
                    $args['taxonomy'] = $post_type;
                }

                $post_list = wp_list_pluck(get_terms($args), 'name', 'term_id');
                break;
            case 'user':
                if (!current_user_can('list_users')) {
                    $post_list = [];
                    break;
                }

                $users = [];

                foreach (get_users(['search' => "*{$search}*"]) as $user) {
                    $user_id           = $user->ID;
                    $user_name         = $user->display_name;
                    $users[$user_id] = $user_name;
                }

                $post_list = $users;
                break;
            default:
                $post_list = $this->get_query_post_list($post_type, 10, $search, $meta_query);
        }

        if (!empty($post_list)) {
            foreach ($post_list as $key => $item) {
                $results[] = ['text' => $item, 'id' => $key];
            }
        }

        wp_send_json(['results' => $results]);
    }

    /**
     * Select2 Ajax Get Posts Value Titles
     * get selected value to show elementor editor panel in select2 ajax search box
     *
     * @access public
     * @return void
     * @since 4.0.0
     */
    public function select2_ajax_get_posts_value_titles()
    {

        if (empty($_POST['id'])) {
            wp_send_json_error([]);
        }

        if (empty(array_filter($_POST['id']))) {
            wp_send_json_error([]);
        }
        $ids         = array_map('intval', $_POST['id']);
        $source_name = !empty($_POST['source_name']) ? sanitize_text_field($_POST['source_name']) : '';

        switch ($source_name) {
            case 'taxonomy':
                $args = [
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'include'    => implode(',', $ids),
                ];

                if ($_POST['post_type'] !== 'all') {
                    $args['taxonomy'] = sanitize_text_field($_POST['post_type']);
                }

                $response = wp_list_pluck(get_terms($args), 'name', 'term_id');
                break;
            case 'user':
                $users = [];

                foreach (get_users(['include' => $ids]) as $user) {
                    $user_id           = $user->ID;
                    $user_name         = $user->display_name;
                    $users[$user_id] = $user_name;
                }

                $response = $users;
                break;
            default:
                $post_info = get_posts([
                    'post_type' => sanitize_text_field($_POST['post_type']),
                    'include'   => implode(',', $ids)
                ]);
                $response  = wp_list_pluck($post_info, 'post_title', 'ID');
        }

        if (!empty($response)) {
            wp_send_json_success(['results' => $response]);
        } else {
            wp_send_json_error([]);
        }
    }

}

new Max_slider_select2();