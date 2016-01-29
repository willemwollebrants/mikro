<?php

namespace Studiow\Mikro;

class Config
{

    private $settings = [];

    public function __construct($settings = [])
    {
        $this->set($settings);
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            $this->settings[$key] = $value;
        }
    }

    public function get($key, $defaultValue = null)
    {
        
    }

}
