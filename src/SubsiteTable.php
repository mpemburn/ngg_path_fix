<?php

namespace Ngg_Path_Fix;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class SubsiteTable extends \WP_List_Table
{
    public function get_columns(): array
    {
        return [
            'blog_id' => __('Blog ID', 'npf'),
            'name' => __('Name', 'npf'),
            'siteurl' => __('URL', 'npf'),
            'modified' => __('Last Update', 'npf'),
            'galleries' => __('Galleries', 'npf'),
        ];
    }

    public function prepare_items(): void
    {
        $this->_column_headers = $this->get_column_info();

        // pagination
        $options = array(
            'per_page' => $this->get_items_per_page('sites_per_page', 5),
            'current_page' => $this->get_pagenum(),
            'orderby' => (isset($_GET['orderby']) && $this->_is_sortable($_GET['orderby'])) ? $_GET['orderby'] : 'blog_id',
            'order' => (isset($_GET['order']) && 'desc' === strtolower($_GET['order'])) ? 'desc' : 'asc'
        );

        $this->set_pagination_args(array(
            'total_items' => uri_network_blogs_count(),
            'per_page' => $options['per_page']
        ));

        $data = (new SubsiteData())->getSubsites($options);

        $this->items = $data;
    }

    public function no_items(): void
    {
        _e('This network contains no matching blogs.', 'uri');
    }

    public function _is_sortable($column_name): bool
    {
        $cols = $this->get_sortable_columns();

        return (array_key_exists($column_name, $cols));
    }

    public function get_sortable_columns(): array
    {
        $sortable_columns = [
            'blog_id' => ['blog_id', false],
            'name' => ['name', false],
            'siteurl' => ['siteurl', false],
            'modified' => ['modified', false],
            'galleries' => ['galleries', false],
        ];

        return $sortable_columns;
    }

    public function column_name($item): string
    {
        $text = sprintf('<a href="%s">%s</a>', '/wp-admin/network/admin.php?page=fix-paths&blog_id=' . $item['blog_id'], $item['name']);

        return sprintf('%1$s', $text);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            // case 'blog_id':
            // case 'theme':
            // case 'pages':
            // case 'posts':
            // case 'users':
            // case 'cats':
            case 'modified':
                return date('Y-m-d', strtotime($item['modified']));
            default:
                return $item[$column_name];
            // return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }
}