<?php

namespace AntCMS;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class AntYaml
{
    public static function parseFile($file)
    {
        return Yaml::parseFile($file);
    }

    public static function saveFile($file, $data)
    {
        $yaml = Yaml::dump($data);
        file_put_contents($file, $yaml);
    }

    public static function parseYaml($yaml){
        try {
            return Yaml::parse($yaml);
        } catch (ParseException $exception) {
            return null;
        }
    }
}
