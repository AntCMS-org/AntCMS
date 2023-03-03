<?php

namespace AntCMS;

use Exception;
use Symfony\Component\Yaml\Exception\ParseException;

class AntUsers
{
    public static function getUser($username)
    {
        $users = Self::getUsers();
        return $users[$username] ?? null;
    }

    /** This function is used to get all the info of a user that is safe to publicize.
     *  Mostly intended to create an array that can be safely passed to twig and used to display user information on the page, such as their name.
     * @param mixed $username 
     * @return array 
     * @throws ParseException 
     */
    public static function getUserPublicalKeys($username)
    {
        $user = Self::getUser($username);
        if (is_null($user)) {
            return [];
        }
        unset($user['password']);
        return $user;
    }

    public static function getUsers()
    {
        if (file_exists(antUsersList)) {
            return AntYaml::parseFile(antUsersList);
        } else {
            AntCMS::redirect('/profile/firsttime');
        }
    }

    public static function addUser($data)
    {
        $users = Self::getUsers();
        if (key_exists($data['username'], $users)) {
            return false;
        }

        if (!AntTools::valuesNotNull(['username', 'role', 'display-name', 'password'], $data)) {
            return false;
        }

        $users[$data['username']] = [
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'name' => $data['display-name'],
        ];
        return AntYaml::saveFile(antUsersList, $users);
    }

    public static function updateUser($username, $newData)
    {
        foreach ($newData as $key => $value) {
            if (empty($value)) {
                throw new Exception("Key $key cannot be empty.");
            }
        }

        $users = self::getUsers();
        if (!key_exists($username, $users)) {
            throw new Exception("There was an error when updating the selected user.");
        }

        if (isset($newData['password'])) {
            $users[$username]['password'] = password_hash($newData['password'], PASSWORD_DEFAULT);
        }

        if (isset($newData['role'])) {
            $users[$username]['role'] = $newData['role'];
        }

        if (isset($newData['name'])) {
            $users[$username]['name'] = $newData['name'];
        }

        if (isset($newData['username'])) {
            if (key_exists($newData['username'], $users) && $newData['username'] !== $username) {
                throw new Exception("Username is already taken.");
            }

            $user = $users[$username];
            unset($users[$username]);
            $users[$newData['username']] = $user;
        }

        return AntYaml::saveFile(antUsersList, $users);
    }

    public static function setupFirstUser($data)
    {
        if (file_exists(antUsersList)) {
            AntCMS::redirect('/');
        }

        $data['username'] = trim($data['username']);
        $data['name'] = trim($data['name']);

        $users = [
            $data['username'] => [
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => 'admin',
                'name' => $data['name'],
            ],
        ];

        return AntYaml::saveFile(antUsersList, $users);
    }
}
