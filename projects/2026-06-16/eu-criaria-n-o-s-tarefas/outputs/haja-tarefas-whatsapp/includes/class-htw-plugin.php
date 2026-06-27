<?php

if (!defined('ABSPATH')) {
    exit;
}

class HTW_Plugin
{
    private static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init()
    {
        $repository = new HTW_Task_Repository();
        $calendar = new HTW_Google_Calendar();
        $parser = new HTW_Command_Parser();

        (new HTW_Admin($repository))->init();
        (new HTW_REST_Controller($repository, $calendar, $parser))->init();
    }
}
