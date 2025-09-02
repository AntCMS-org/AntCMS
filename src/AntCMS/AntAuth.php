<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * A rudimentary authentication management class.
 * Will eventually be replaced when we have a more advanced key-store
 * 
 * @package AntCMS
 */
class AntAuth
{
    private array $users = [];
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        if ($this->filesystem->exists(PATH_USERS)) {
            $this->users = AntYaml::parseFile(PATH_USERS);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    function __destruct()
    {
        AntYaml::saveFile(PATH_USERS, $this->users);
    }

    /**
     * Adds / updates a user
     * 
     * @param string $username 
     * @param string $password 
     * @return void 
     */
    public function setUser(string $username, string $password)
    {
        if ($this->filesystem->exists(PATH_USERS)) {
            $this->users = AntYaml::parseFile(PATH_USERS, true);
        }

        $this->users[$username] = $password;
    }

    /**
     * Deletes a user
     * 
     * @param mixed $username 
     * @return void 
     * @throws IOException 
     * @throws ParseException 
     */
    public function deleteUser($username)
    {
        if ($this->filesystem->exists(PATH_USERS)) {
            $this->users = AntYaml::parseFile(PATH_USERS, true);
        }

        unset($this->users[$username]);
    }

    /**
     * Attempt to log in a user
     */
    public function login(string $username, string $password): bool
    {
        if (isset($this->users[$username]) && password_verify($password, $this->users[$username])) {

            $_SESSION['username'] = $username;
            return true;
        }
        return false;
    }

    /**
     * Log out the user
     */
    public function logout(): void
    {
        unset($_SESSION['username']);
    }

    /**
     * Check if a user is logged in
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['username']);
    }

    /**
     * Get the current logged-in username
     */
    public function currentUser(): ?string
    {
        return $_SESSION['username'] ?? null;
    }
}
