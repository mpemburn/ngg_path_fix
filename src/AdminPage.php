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

    public static function clearGeneralOptions($options)
    {
        $options['per_page'] = (isset($options['per_page']) && (int)$options['per_page'] > 0) ? $options['per_page'] : 10;
        $options['current_page'] = (isset($options['current_page']) && (int)$options['current_page'] > 1) ? $options['current_page'] : 1;

        return $options;
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
