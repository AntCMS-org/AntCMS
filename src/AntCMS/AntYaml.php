<?php

namespace AntCMS;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class AntYaml
{
    /**
     * @param string $file 
     * @return array<mixed> 
     */
    public static function parseFile(string $file)
    {
        return Yaml::parseFile($file);
    }

    /**
     * @param string $file 
     * @param array<mixed> $data 
     * @return void 
     */
    public static function saveFile(string $file, array $data)
    {
        $yaml = Yaml::dump($data);
        file_put_contents($file, $yaml);
    }

    /**
     * @param string $yaml 
     * @return array<mixed>|null 
     */
    public static function parseYaml(string $yaml){
        try {
            return Yaml::parse($yaml);
        } catch (ParseException) {
            return null;
        }
    }
}
