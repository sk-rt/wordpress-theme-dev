<?php

namespace Theme\CustomFields;

class Loader
{
    protected static $instance;
    protected function __construct()
    {
        require_once __DIR__ . '/fields/post.php';
    }
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
