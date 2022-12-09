<?php

namespace Ngg_Path_Fix;

class AdminPage
{
    private static $instance = null;
    protected $listTable;

    private function __construct()
    {
        $this->addActions();
    }

    public static function boot()
    {
        if (!self::$instance) {
            self::$instance = new AdminPage();
        }

        return self::$instance;
    }

    public static function clearGeneralOptions($options): array
    {
        $options['per_page'] = (isset($options['per_page']) && (int)$options['per_page'] > 0) ? $options['per_page'] : 10;
        $options['current_page'] = (isset($options['current_page']) && (int)$options['current_page'] > 1) ? $options['current_page'] : 1;

        return $options;
    }

    protected function addActions(): void
    {
        add_action('network_admin_menu', [$this, 'addMenuPage']);
        add_action('network_admin_menu', [$this, 'addPathFixPage']);
        add_action('network_admin_menu', [$this, 'addListPage']);
        add_action('wp_ajax_nopriv_load_gallery_images', [new FixPaths(), 'loadGalleryImages']);
        add_action('wp_ajax_load_gallery_images', [new FixPaths(), 'loadGalleryImages']);
        add_action('wp_ajax_nopriv_update_gallery_path', [new FixPaths(), 'updateGalleryPath']);
        add_action('wp_ajax_update_gallery_path', [new FixPaths(), 'updateGalleryPath']);
        add_action('admin_head', [$this, 'setColumnWidths']);
    }

    public function addListPage(): void
    {
        $this->listTable = new SubsiteTable();
    }

    public function addMenuPage(): void
    {
        $hook = add_menu_page(
            __('NGG Path Fixer', 'uri'),
            'NGG Path Fixer',
            'switch_themes',
            'ngg-path-fix',
            [$this, 'showListPage'],
            'dashicons-admin-tools',
            90
        );

        add_action('load-' . $hook, [$this, 'addAdminAddOptions']);
    }

    public function addPathFixPage(): void
    {
        add_options_page(
            'Fix Paths',
            '',
            'manage_options',
            'fix-paths',
            [new FixPaths(), 'render']
        );
    }

    public function addAdminAddOptions(): void
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Sites',
            'default' => 10,
            'option' => 'sites_per_page'
        );

        add_screen_option($option, $args);
    }

    public function setColumnWidths(): void
    {
        echo '<style>';
        foreach ([
            'blog_id' => '10%',
            'name' => '20%',
            'modified' => '20%',
            'galleries' => '10%',
         ] as $id => $width) {
            echo "th#{$id} { width: {$width}; }";
        }
        echo '</style>';
    }

    public function showListPage(): void
    {
        echo '<div style="max-width: 90%;">';
        $this->listTable->prepare_items();
        $this->listTable->display();
        echo '</div>';
    }
}
