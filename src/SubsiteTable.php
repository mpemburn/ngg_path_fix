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
            'galleries' => __('Galleries', 'npf'),
            'modified' => __('Last Update', 'npf'),
        ];
    }

    public function prepare_items(): void
    {
        $this->_column_headers = $this->get_column_info();

        // pagination
        $options = [
            'per_page' => $this->get_items_per_page('sites_per_page', 20),
            'current_page' => $this->get_pagenum(),
            'orderby' => (isset($_GET['orderby']) && $this->_is_sortable($_GET['orderby'])) ? $_GET['orderby'] : 'blog_id',
            'order' => (isset($_GET['order']) && 'desc' === strtolower($_GET['order'])) ? 'desc' : 'asc'
        ];

        $data = (new SubsiteData())->getSubsites($options);

        $this->set_pagination_args([
            'total_items' => count($data),
            'per_page' => $options['per_page']
        ]);

        $data = array_slice($data, (($this->get_pagenum() - 1) * $options['per_page']), $options['per_page']);

        $this->items = $data;
    }

    public function no_items(): void
    {
        _e('This network contains no matching blogs.', 'uri');
    }

    public function _is_sortable($columnName): bool
    {
        $cols = $this->get_sortable_columns();

        return (array_key_exists($columnName, $cols));
    }

    public function get_sortable_columns(): array
    {
        return [
            'blog_id' => ['blog_id', true],
            'name' => ['name', false],
            'siteurl' => ['siteurl', false],
            'galleries' => ['galleries', false],
            'modified' => ['modified', false],
        ];
    }

    public function column_default($item, $columnName): string
    {
        switch ($columnName) {
            case 'name':
                $name = $item['name'] ?? 'No Title';
                $path = '/wp-admin/network/admin.php?page=fix-paths&blog_id=' . $item['blog_id'];
                $text = sprintf('<a href="%s">%s</a>', $path, $name);

                $value = sprintf('%1$s', $text);
                break;
            case 'siteurl':
                $value = '<a href="' . $item['siteurl'] . '">' . $item['siteurl'] . '</a>';
                break;
            case 'modified':
                $value = date('Y-m-d', strtotime($item['modified']));
                break;
            default:
                $value = $item[$columnName];
        }

        return $value;
    }
}
