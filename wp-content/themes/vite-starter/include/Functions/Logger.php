<?php

namespace Theme\Functions;

class Logger
{
    public static function log($var)
    {
        \error_log(var_export($var, true) . "\n", 3, \get_template_directory() . '/log/debug.log');
    }
}
