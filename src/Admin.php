<?php

namespace Ngg_Path_Fix;

class Admin
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
            self::$instance = new Admin();
        }

        return self::$instance;
    }

    protected function addActions()
    {
        add_action('network_admin_menu', [$this, 'addListPage']);
        add_action('network_admin_menu', [$this, 'addMenuPage']);
    }

    public function addListPage()
    {
        $this->listTable = new SubsiteTable();
    }

    public function addMenuPage()
    {
        $hook = add_menu_page(
            __('NGG Path Fixer', 'uri'),
            'NGG Path Fixer',
            'switch_themes',
            'ngg-path-fix',
            [$this, 'showListPage'],
            'dashicons-admin-tools',
            88
        );

        add_action('load-' . $hook, [$this, 'addAdminAddOptions']);
    }

    public function addAdminAddOptions()
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Sites',
            'default' => 10,
            'option' => 'sites_per_page'
        );

        add_screen_option($option, $args);
    }

    public function showListPage()
    {
        $this->listTable->prepare_items();
        $this->listTable->display();

    }

}
