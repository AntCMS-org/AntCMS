<?php

namespace AntCMS;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class AntYaml
{
    /** 
     * @return array<mixed> 
     */
    public static function parseFile(string $file)
    {
        return Yaml::parseFile($file);
    }

    /** 
     * @param array<mixed> $data 
     */
    public static function saveFile(string $file, array $data): bool
    {
        $yaml = Yaml::dump($data);
        return (bool) file_put_contents($file, $yaml);
    }

    /** 
     * @return array<mixed>|null 
     */
    public static function parseYaml(string $yaml)
    {
        try {
            return Yaml::parse($yaml);
        } catch (ParseException) {
            return null;
        }
    }
}
