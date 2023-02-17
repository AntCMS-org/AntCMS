<?php

namespace AntCMS;

use Exception;

class AntUsers
{
    public static function getUser($username)
    {
        $users = Self::getUsers();
        return $users[$username] ?? null;
    }

    public static function getUsers()
    {
        if (file_exists(antUsersList)) {
            $users = AntYaml::parseFile(antUsersList);
        } else {
            self::generateEmptyUsers();
            $users = AntYaml::parseFile(antUsersList);
        }

        return $users;
    }

    public static function updateUser($username, $newData)
    {
        $users = self::getUsers();
        if (!isset($users[$username])) {
            throw new Exception("There was an error when updating the selected user.");
        }

        if (isset($newData['password'])) {
            $users[$username]['password'] = password_hash($newData['password'], PASSWORD_DEFAULT);
        }

        if (isset($newData['role'])) {
            $users[$username]['role'] = $newData['role'];
        }

        return AntYaml::saveFile(antUsersList, $users);
    }

    private static function generateEmptyUsers()
    {
        $users = [
            'Admin' => [
                'password' => '',
                'role' => 'admin',
                'name' => 'Administrator'
            ],
        ];

        return AntYaml::saveFile(antUsersList, $users);
    }
}
