<?php
namespace AntCMS;
use Michelf\Markdown;

class AntMarkdown{
    public static function renderMarkdown($md)
    {
        $result = Markdown::defaultTransform($md);
        $result = preg_replace('/(?:~~)([^~~]*)(?:~~)/', '<s>$1</s>', $result);
        echo $result;
    }
}
?>
