<?php

namespace AntCMS;

use Michelf\MarkdownExtra;
use PDO;

class AntMarkdown
{
    const emojiMap = array(
        ':smile:' => '😄',
        ':grinning:' => '😀',
        ':blush:' => '😊',
        ':wink:' => '😉',
        ':heart_eyes:' => '😍',
        ':kissing_heart:' => '😘',
        ':tongue:' => '😝',
        ':stuck_out_tongue_winking_eye:' => '😜',
        ':joy:' => '😂',
        ':satisfied:' => '😌',
        ':yum:' => '😋',
        ':neutral_face:' => '😐',
        ':expressionless:' => '😑',
        ':unamused:' => '😒',
        ':sweat_smile:' => '😅',
        ':sweat:' => '😓',
        ':pensive:' => '😔',
        ':confused:' => '😕',
        ':disappointed:' => '😞',
        ':confounded:' => '😖',
        ':fearful:' => '😨',
        ':cold_sweat:' => '😰',
        ':cry:' => '😢',
        ':sob:' => '😭',
        ':angry:' => '😠',
        ':rage:' => '😡',
        ':triumph:' => '😤',
        ':sleepy:' => '😴',
        ':dizzy_face:' => '😵',
        ':mask:' => '😷',
        ':scream:' => '😱',
        ':flushed:' => '😳',
        ':frowning:' => '😦',
        ':anguished:' => '😧',
        ':fearful:' => '😨',
        ':weary:' => '😩',
        ':exploding_head:' => '🤯',
        ':grimacing:' => '😬',
        ':heart:' => '💓',
        ':thumbsup:' => '👍',
        ':thumbsdown:' => '👎'
    );

    public static function renderMarkdown($md)
    {
        $result = MarkdownExtra::defaultTransform($md);
        $result = preg_replace('/(?:~~)([^~~]*)(?:~~)/', '<s>$1</s>', $result);

        foreach (AntMarkdown::emojiMap as $markdown => $unicode) {
            $result = str_replace($markdown, $unicode, $result);
        }

        return $result;
    }
}
