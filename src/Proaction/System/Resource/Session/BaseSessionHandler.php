<?php

namespace Proaction\System\Resource\Session;

class BaseSessionHandler
{
    protected $session;
    protected $name;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    public function add($key, $value = null)
    {
        if (is_array($key) && is_null($value)) {
            foreach ($key as $k => $v) {
                $this->session->add($this->name, $k, $v);
            }
        } else {
            $this->session->add($this->name, $key, $value);
        }
    }

    public function remove($key)
    {
        $this->session->rm($this->name, $key);
    }

    public function get()
    {
        return $this->data;
    }

    public function bust()
    {
        return $this->session->bust($this->name);
    }

    public function kill()
    {
        return $this->session->die();
    }

    public function pluck($key)
    {
        return $this->session->pluck($this->name, $key);
    }

    public function push($key, $value)
    {
        $val = $this->session->pluck($this->name, $key);
        $val[] = $value;
        $this->session->add($this->name, $key, $val);
    }
}
