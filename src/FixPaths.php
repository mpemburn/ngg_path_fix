<?php
namespace Ngg_Path_Fix;

class FixPaths
{
    private static $instance = null;

    private function __construct()
    {
        $this->registerActions();
    }

    public static function boot()
    {
        if (! self::$instance) {
            self::$instance = new FixPaths();
        }

        return self::$instance;
    }

    protected function registerActions()
    {
    }

}
