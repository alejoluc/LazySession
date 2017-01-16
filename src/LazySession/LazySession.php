<?php

namespace alejoluc\LazySession;

class LazySession implements \ArrayAccess {

    const FLASHED_NEXTREQ = '__flashedNextRequest';
    const FLASHED_THISREQ = '__flashedThisRequest';

    private $flashInitialized = false;

    public function __construct() {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new SessionsDisabledException('Sessions are disabled in this PHP installation');
        }
    }

    private function initFlash() {
        // flashInitialized must be set to true at the top of this function to avoid infinite recursion
        $this->flashInitialized = true;
        $_SESSION[self::FLASHED_THISREQ] = $this->get(self::FLASHED_NEXTREQ, []);
        $_SESSION[self::FLASHED_NEXTREQ] = [];
    }

    /**
     * Starts a session, if it has not been already started. You should not need to call this method on your
     * own, unless you <em>really</em> want to make sure a session is started.
     * @link http://php.net/manual/en/function.session-start.php Official PHP Documentation for session_start()
     * @return bool
     */
    public function start() {
        if (session_status() === PHP_SESSION_ACTIVE || session_start()) {
            if ($this->flashInitialized !== true) {
                $this->initFlash();
            }
            return true;
        }
        return false;
    }

    /**
     * Checks whether a given key exists in the active session
     * @param string $key
     * @return bool
     */
    public function has($key) {
        $this->start();
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Gets the value of a given session key if it exists
     * @param string $key
     * @param mixed $defaultValue If the key does not exist, this will be returned
     * @return mixed If the key exists, it returns the value. Otherwise, it returns $defaultValue
     */
    public function get($key, $defaultValue = null) {
        $this->start();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $defaultValue;
    }

    /**
     * Get the underlying $_SESSION array in full
     * @return array PHP's own $_SESSION array
     */
    public function getAll() {
        $this->start();
        return $_SESSION;
    }

    /**
     * Creates or modifies a session key with the supplied value
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $this->start();
        $_SESSION[$key] = $value;
    }

    /**
     * Deletes a session key and its value, if the key exists
     * @param string $key
     */
    public function delete($key) {
        $this->start();
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
    }

    /* Object-like access implementation */
    public function __get($key) {
        return $this->get($key);
    }
    public function __set($key, $value) {
        $this->set($key, $value);
    }

    public function __isset($key) {
        return $this->has($key);
    }

    public function __unset($key) {
        $this->delete($key);
    }

    /* ArrayAccess interface implementation */
    public function offsetExists($offset) {
        return $this->has($offset);
    }
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->delete($offset);
    }


    /**
     * Gets the current session save path or sets a new save path
     * @param string $savePath
     * @return string If $savePath is not given or is an empty string, it will return the current save path.
     * If it is given, it will return the <em>old</em> path
     * @link http://php.net/manual/en/function.session-save-path.php Official documentation for session_save_path()
     */
    public function savePath($savePath = '') {
        return session_save_path($savePath);
    }

    /**
     * End the current session and store session data.
     *
     * @link http://php.net/manual/en/function.session-commit.php Official documentation for session_commit()
     * @return void
     */
    public function commit() {
        session_write_close(); // session_commit() is an alias of session_write_close()
    }

    /**
     * Preferred way to delete everything in the session, as keys and values of the $_SESSION array will not
     * be available in the current request after calling this, unlike session_destroy()
     * @return void
     */
    public function clear() {
        $this->start();
        $_SESSION = [];
    }

    /**
     * Note that this will not unset the keys and values in the current request, although they
     * will not be present on the next run. Use the clear() method for that. Per PHP's official
     * documentation, that's the recommended way of cleaning up session data.
     * @see clear() The prefered way to do this is to use the clear() method
     * @link http://php.net/manual/en/function.session-destroy.php Official documentation for session_destroy()
     * @return bool
     */
    public function destroy() {
        $this->start();
        return session_destroy();
    }

    /**
     * Note that this will not unset the keys and values in the current request, although they
     * will not be present on the next run. See link to PHP documentation.
     * @link http://php.net/manual/en/function.session-abort.php Official documentation for session_abort()
     */
    public function abort() {
        session_abort();
    }

    /**
     * Gets or sets the session name. This is the name of the session cookie. To set the session name, this needs
     * to be called before session_start(). See link to PHP documentation.
     *
     * From PHP documentation: "The session name is reset to the default value stored in
     * session.name at request startup time. Thus, you need to call session_name() for every request
     * (and before session_start() or session_register() are called)."
     *
     * @link http://php.net/manual/en/function.session-name.php Official documentation for session_name()
     * @param string $name
     * @return string If no ```$name``` is given, or it is an empty string, it will return the name of the
     * current session. If ```$name``` is given and is a non-empty string, it will return the <em>old</em> name.
     */
    public function sessionName($name = '') {
        if (is_string($name) && $name !== '') {
            return session_name($name);
        }
        return session_name();
    }

    /**
     * Gets or sets the session id.
     * To set the session id, this needs to be called before session_start(). See link to PHP documentation
     * @link http://php.net/manual/en/function.session-id.php Official documentation for session_id()
     * @param string $newSessionId
     * @return string If no ```$newSessionId``` is given, or it is an empty string, it will return the id of
     * the current session. If it is given and is a non-empty string, it will return the <em>old</em> id.
     *
     */
    public function sessionId($newSessionId = '') {
        if (is_string($newSessionId) && $newSessionId !== '') {
            return session_id($newSessionId);
        }
        $this->start(); // Unlike session_name(), session_id needs an active session to return it's id
        return session_id();
    }

    /**
     * It is "recommended" that this is called each time an user logs in, but do see warning note in PHP
     * documentation
     * @link http://php.net/manual/en/function.session-regenerate-id.php PHP Official Documentation
     * @param bool [$deleteOldSessionFile = false]
     * @return bool
     */
    public function regenerateId($deleteOldSessionFile = false) {
        $this->start();
        return session_regenerate_id($deleteOldSessionFile);
    }

    /**
     * Sets a custom save handler
     * @link http://php.net/manual/en/function.session-set-save-handler.php PHP Official Documentation
     * @param \SessionHandlerInterface $handler
     * @param bool [$register_shutdown = false]
     * @return bool
     */
    public function setSaveHandler(\SessionHandlerInterface $handler, $register_shutdown = true) {
        return session_set_save_handler($handler, $register_shutdown);
    }


    public function flash($key, $value) {
        $this->start();
        $flashArray = $this->get('__flashedNextRequest');
        if ($flashArray === null) {
            $this->set('__flashedNextRequest', []);
        }
        $_SESSION['__flashedNextRequest'][$key] = $value;
    }

    public function flashGet($key, $defaultValue = null) {
        $this->start();
        if (array_key_exists($key, $_SESSION['__flashedThisRequest'])) {
            $ret = $_SESSION['__flashedThisRequest'][$key];
            unset($_SESSION['__flashedThisRequest'][$key]);
            return $ret;
        }
        return $defaultValue;
    }

    public function flashGetAll() {
        $this->start();
        $ret = $this->get(self::FLASHED_THISREQ, []);
        $_SESSION[self::FLASHED_THISREQ] = [];
        return $ret;
    }

}