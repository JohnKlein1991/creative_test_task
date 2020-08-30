<?php


namespace App\Service;

/**
 * Class AuthService
 * @package App\Service
 */
class AuthService
{
    /**
     * @param string $username
     */
    public function login(string $username)
    {
        session_start();
        $_SESSION['username'] = $username;
        session_write_close();
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        session_start();
        return $_SESSION['username'] ?? null;
    }

    /**
     *
     */
    public function logout()
    {
        session_start();
        unset($_SESSION['username']);
        session_write_close();
    }
}
