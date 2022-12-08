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

    protected function addActions(): void
    {
        add_action('network_admin_menu', [$this, 'addMenuPage']);
        add_action('network_admin_menu', [$this, 'addPathFixPage']);
        add_action('network_admin_menu', [$this, 'addListPage']);
        add_action('wp_ajax_nopriv_load_gallery_images', [new FixPaths(), 'loadGalleryImages']);
        add_action('wp_ajax_load_gallery_images', [new FixPaths(), 'loadGalleryImages']);
        add_action('wp_ajax_nopriv_update_gallery_path', [new FixPaths(), 'updateGalleryPath']);
        add_action('wp_ajax_update_gallery_path', [new FixPaths(), 'updateGalleryPath']);
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

    public function showListPage(): void
    {
        echo '<div style="max-width: 90%;">';
        $this->listTable->prepare_items();
        $this->listTable->display();
        echo '</div>';
    }
}
