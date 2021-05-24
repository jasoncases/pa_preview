<?php

namespace Proaction\System\Resource\Session;

/**
 * A container class that acts as a facade for the $_SESSION. Trying to
 * experiment with some different ways of handling the session values,
 * particulary since the larger project has multiple session entities
 * that require modfication.
 *
 * Session::$data is a reference to $_SESSION, and values are added
 * by the SessionHandlers.
 *
 * SessionHandlers represent "sub-session", i.e., 'client', 'system',
 * 'user', etc., each with their own class to handle their data directly
 */
class Session
{
    protected $data;
    protected $id;

    private static $__instance;

    public static function getInstance()
    {
        if (!self::$__instance) {
            self::$__instance = new Session();
        }
        return self::$__instance;
    }

    private function __construct()
    {
        $this->data = &$_SESSION;
        $this->id = session_id();
    }

    /**
     * Empties a sub-session
     *
     * @param string $localSessionName
     * @return void
     */
    public function bust($localSessionName)
    {
        $this->data[$localSessionName] = [];
    }

    /**
     * Add a value to a sub-session
     *
     * @param string $localSessionName
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function add($localSessionName, $key, $value)
    {
        $this->data[$localSessionName][$key] = $value;
    }

    /**
     * Return a value from a sub-session, by key
     *
     * @param string $localSessionName
     * @param string $key
     * @return mixed
     */
    public function pluck($localSessionName, $key)
    {
        if (!isset($this->data[$localSessionName])) {
            return;
        }
        if (!isset($this->data[$localSessionName][$key])) {
            return;
        }
        return $this->data[$localSessionName][$key];
    }

    /**
     * Remove a value from a sub-session, by key
     *
     * @param string $localSessionName
     * @param string $key
     * @return void
     */
    public function rm($localSessionName, $key)
    {
        unset($this->data[$localSessionName][$key]);
    }

    /**
     * Destroy the session
     *
     * @return void
     */
    public function die()
    {
        $this->data = [];
        session_destroy();
    }

    public function keyExists($key)
    {
        return isset($this->data[$key]);
    }
}
