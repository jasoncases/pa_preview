<?php

namespace Proaction\System\Resource\Cache;

use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Users\Resource\UserStatusCache;
use Proaction\Domain\Voice\Resource\Cache\VoiceCache;

/**
 * Acts as a wrapper for the ProactionRedis and Redis instances. The
 * cache is directly interactable, allowing for simple calls in and out
 * of the redis instance.
 */
class Cache
{

    private $clientPrefix; // string id to pull client cache from Redis
    private $redis; // ProactionRedis wrapper

    // These are the keys that identify the various sub-caches. These
    // are the same keys that are used to bust the cache
    private $voiceKey = 'voiceAnnouncements';
    private $userKey = 'usersCache';

    // the main cache array, when a cache is requested, the data comes
    // from values in this array
    private $cache = [];

    /**
     *
     * @param string $prefix  - Client prefix, i.e., `jasoncases`
     * @param        $redis   - \Proaction\Resource\ProactionRedis
     */
    public function __construct($prefix, $redis)
    {
        $this->clientPrefix = $prefix;
        $this->redis = $redis;
        $this->cache = $this->_registerCache();
    }

    /**
     * Bust the cache, or sub-cache and reprocess.
     *
     * @param string $key
     * @return void
     */
    public static function bustAndReloadCache($key = '*')
    {
        $cache = new Cache(ProactionClient::prefix(), ProactionRedis::getInstance());
        $cache->bustCache($key);
        $cache->process();
    }

    /**
     * Allows for the refreshing of the cache. If no key is sent, the
     * whole cache gets busted. Otherwise, only the specific branch of
     * the cache will get reset.
     *
     * ! IMPORTANT - If busting the cache, you MUST remember to call
     * ! process immediately after, or there will be up to a 20 second
     * ! delay while waiting for the next cron job to run and build the
     * ! cache.
     *
     * @param string $key - the key name that matches the desired cache
     *                      to bust. Defaults to '*' for the whole cache
     * @return boolean    - the boolean return from Redis
     */
    public function bustCache($key = "*")
    {
        if ($key === '*') {
            $this->cache = [];
        } else {
            unset($this->cache[$key]);
        }
        return $this->_store();
    }

    /**
     * Calls the child caches and updates their values in the main cache
     * Each child cache is built by its own class, and they are each
     * given the current cache value as a attr in the constructor.
     *
     * >>> Eventually, the children will do some diff'ing and only send
     * >>> data that is required to update
     *
     * @return boolean
     */
    public function process()
    {
        $this->_processVoiceCache();
        $this->_processUserCache();

        return $this->_store();
    }

    private function _processVoiceCache()
    {
        if (!isset($this->cache[$this->voiceKey])) {
            $this->cache[$this->voiceKey] = [];
        }
        $this->cache[$this->voiceKey] = (new VoiceCache($this->cache[$this->voiceKey]))->get();
    }

    private function _processUserCache()
    {
        if (!isset($this->cache[$this->userKey])) {
            $this->cache[$this->userKey] = [];
        }
        $this->cache[$this->userKey] = (new UserStatusCache($this->cache[$this->userKey]))->get();
    }

    /**
     * Return a single (or whole) cache
     *
     * @param string $key
     * @return array
     */
    public function get($key = '*')
    {
        if ($key === '*') {
            return $this->cache;
        }
        return $this->cache[$key];
    }

    /**
     * Log the the user in, in the UserStatusCache. This is an active
     * cache updater, which is then stored back into the redis instance
     *
     * @param int $employee_id
     * @return boolean
     */
    public function logInUser($employee_id)
    {
        $userCache = new UserStatusCache($this->cache[$this->userKey]);
        $this->cache[$this->userKey] = $userCache->logIn($employee_id);
        return $this->_store();
    }

    /**
     * Log the the user out, in the UserStatusCache. This is an active
     * cache updater, which is then stored back into the redis instance
     *
     * @param int $employee_id
     * @return boolean
     */
    public function logOutUser($employee_id)
    {
        $userCache = new UserStatusCache($this->cache[$this->userKey]);
        $this->cache[$this->userKey] = $userCache->logOut($employee_id);
        return $this->_store();
    }

    /**
     * Update the user instance, in the UserStatusCache. This is a
     * general update method, telling the UserStatusCache to rebuild one
     * employee record, which is then stored back to the redis instance
     *
     * @param int $employee_id
     * @return boolean
     */
    public function updateUser($employee_id)
    {
        $userCache = new UserStatusCache($this->cache[$this->userKey]);
        $this->cache[$this->userKey] = $userCache->updateUser($employee_id);
        return $this->_store();
    }

    /**
     * Sets the redis instance. Top level identifier is the Client user
     * prefix, i.e., 'jasoncases'. The data is stored as JSON
     *
     * @return boolean
     */
    private function _store()
    {
        return $this->redis->redis->set($this->clientPrefix, json_encode($this->cache));
    }

    /**
     * Gets the instance from redis. If null, return the initialize
     * cache with defaults, otherwise, decode the JSON and return
     *
     * @return array
     */
    private function _registerCache()
    {
        $c = $this->redis->redis->get($this->clientPrefix);
        if (!$c) {
            return $this->_initializeCache();
        }
        return json_decode($c, true, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Default cache array
     */
    private function _initializeCache()
    {
        return [
            $this->voiceKey => [],
            $this->userKey => [],
        ];
    }
}
