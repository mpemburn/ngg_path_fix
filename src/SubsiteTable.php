<?php

namespace Ngg_Path_Fix;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class SubsiteTable extends \WP_List_Table
{
    function get_columns() {
        $columns = array(
            'blog_id' => __( 'Blog ID', 'npf' ),
            'name' => __( 'Name', 'npf' ),
            'theme' => __( 'Theme', 'npf' ),
            'modified' => __( 'Last Update', 'npf' ),
            'pages' => __( 'Pages', 'npf' ),
            'posts' => __( 'Posts', 'npf' ),
            'users' => __( 'Users', 'npf' ),
            'cats' => __( 'Categories', 'npf' )
        );
        return $columns;
    }

    // function __construct() {

    // }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = $this->get_column_info();

        // pagination
        $options = array(
            'per_page' => $this->get_items_per_page('sites_per_page', 5),
            'current_page' => $this->get_pagenum(),
            'orderby' => ( isset( $_GET['orderby'] ) && $this->_is_sortable( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'blog_id',
            'order' => ( isset($_GET['order'] ) && 'desc' === strtolower($_GET['order']) ) ? 'desc' : 'asc'
        );

        $this->set_pagination_args( array(
            'total_items' => uri_network_blogs_count(),
            'per_page' => $options['per_page']
        ) );

        $data = uri_network_general_query( $options );

        $this->items = $data;
    }

    function no_items() {
        _e( 'This network contains no blogs.', 'uri' );
    }

    function _is_sortable( $column_name ) {
        $cols = $this->get_sortable_columns();
        return ( array_key_exists( $column_name, $cols ) );
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'blog_id' => array( 'blog_id', false ),
            'name' => array( 'name', false ),
            'theme' => array( 'theme', false ),
            'modified' => array( 'modified', false ),
            'pages' => array( 'pages', false ),
            'posts' => array( 'posts', false ),
            'users' => array( 'users', false ),
            'cats' => array( 'cats', false )
        );
        return $sortable_columns;
    }

    function column_name( $item ) {
        $text = sprintf('<a href="%s">%s</a>', $item['url'], $item['name'] );
        $actions = array(
            'edit' => sprintf('<a href="/wordpress/wp-admin/network/site-info.php?id=%s">%s</a>', $item['blog_id'], 'Edit' ),
            'settings' => sprintf('<a href="/wordpress/wp-admin/network/site-settings.php?id=%s">%s</a>', $item['blog_id'], 'Settings' ),
        );
        return sprintf('%1$s %2$s', $text, $this->row_actions($actions) );
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            // case 'blog_id':
            // case 'theme':
            // case 'pages':
            // case 'posts':
            // case 'users':
            // case 'cats':
            case 'modified':
                return date( 'Y-m-d', strtotime( $item['modified'] ) );
            default:
                return $item[$column_name];
            // return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }
}