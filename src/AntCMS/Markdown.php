<?php

namespace AntCMS;

use Michelf\MarkdownExtra;
use AntCMS\AntCache;

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
        $cache = new AntCache();
        $cacheKey = hash('sha3-512', $md).'markdown';

        if ($cache->isCached($cacheKey)) {
            $cachedContent = $cache->getCache($cacheKey);

            if ($cachedContent !== false && !empty($cachedContent)) {
                return $cachedContent;
            }
        }

        $result = MarkdownExtra::defaultTransform($md);
        $result = preg_replace('/(?:~~)([^~~]*)(?:~~)/', '<s>$1</s>', $result);

        foreach (AntMarkdown::emojiMap as $markdown => $unicode) {
            $result = str_replace($markdown, $unicode, $result);
        }

        $cache->setCache($cacheKey, $result);
        return $result;
    }
}
