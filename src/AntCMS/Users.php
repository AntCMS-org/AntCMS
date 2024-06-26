<?php

namespace AntCMS;

use Flight;

class Users
{
    public static function getUser(string $username): ?array
    {
        $users = self::getUsers();
        return $users[$username] ?? null;
    }

    /**
     * This function is used to get all the info of a user that is safe to publicize.
     * Mostly intended to create an array that can be safely passed to twig and used to display user information on the page, such as their name.
     */
    public static function getUserPublicalKeys(string $username): array
    {
        $user = self::getUser($username);
        if (is_null($user)) {
            return [];
        }
        unset($user['password']);
        return $user;
    }

    public static function getUsers(): array
    {
        if (file_exists(PATH_USERS)) {
            return AntYaml::parseFile(PATH_USERS);
        }
        Flight::redirect('/profile/firsttime');
        exit;
    }

    public static function addUser(array $data): bool
    {
        $data['username'] = trim((string) $data['username']);
        $data['name'] = trim((string) $data['name']);
        self::validateUsername($data['username']);

        $users = self::getUsers();
        if (array_key_exists($data['username'], $users)) {
            return false;
        }

        if (!Tools::valuesNotNull(['username', 'role', 'display-name', 'password'], $data)) {
            return false;
        }

        $users[$data['username']] = [
            'password' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'name' => $data['display-name'],
        ];
        return AntYaml::saveFile(PATH_USERS, $users);
    }

    public static function updateUser(string $username, array $newData): bool
    {
        foreach ($newData as $key => $value) {
            if (empty($value)) {
                throw new \Exception("Key $key cannot be empty.");
            }
        }

        $users = self::getUsers();
        if (!array_key_exists($username, $users)) {
            throw new \Exception("There was an error when updating the selected user.");
        }

        if (isset($newData['password'])) {
            $users[$username]['password'] = password_hash((string) $newData['password'], PASSWORD_DEFAULT);
        }

        if (isset($newData['role'])) {
            $users[$username]['role'] = $newData['role'];
        }

        if (isset($newData['name'])) {
            $newData['name'] = trim((string) $newData['name']);
            $users[$username]['name'] = $newData['name'];
        }

        if (isset($newData['username'])) {
            $newData['username'] = trim((string) $newData['username']);
            self::validateUsername($newData['username']);
            if (array_key_exists($newData['username'], $users) && $newData['username'] !== $username) {
                throw new \Exception("Username is already taken.");
            }

            $user = $users[$username];
            unset($users[$username]);
            $users[$newData['username']] = $user;
        }

        return AntYaml::saveFile(PATH_USERS, $users);
    }

    public static function setupFirstUser(array $data): bool
    {
        if (file_exists(PATH_USERS)) {
            Flight::redirect('/');
            exit;
        }

        $data['username'] = trim((string) $data['username']);
        $data['name'] = trim((string) $data['name']);
        self::validateUsername($data['username']);

        $users = [
            $data['username'] => [
                'password' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
                'role' => 'admin',
                'name' => $data['name'],
            ],
        ];

        return AntYaml::saveFile(PATH_USERS, $users);
    }

    private static function validateUsername(string $username): bool
    {
        $pattern = '/^[\p{L}\p{M}*0-9]+$/u';
        if (preg_match($pattern, $username) === 0 || preg_match($pattern, $username) === false) {
            throw new \Exception("Invalid username: \"$username\". Usernames can only contain letters, numbers, and combining marks.");
        }
        return true;
    }
}
