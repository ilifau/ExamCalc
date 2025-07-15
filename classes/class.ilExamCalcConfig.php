<?php

class ilExamCalcConfig
{
    protected $settings;

    public function __construct($namespace)
    {
        $this->settings = new ilSetting($namespace);
    }

    public function get($key, $default = "")
    {
        return $this->settings->get($key, $default);
    }

    public function set($key, $value)
    {
        $this->settings->set($key, $value);
    }
}
