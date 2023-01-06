<?php

namespace AntCMS;

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
}
