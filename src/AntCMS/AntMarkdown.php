<?php

namespace AntCMS;

use AntCMS\AntCache;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use ElGigi\CommonMarkEmoji\EmojiExtension;

class AntMarkdown
{
    public static function renderMarkdown($md)
    {
        $cache = new AntCache();
        $cacheKey = $cache->createCacheKey($md, 'markdown');
        $commonMark = new GithubFlavoredMarkdownConverter();
        $commonMark->getEnvironment()->addExtension(new EmojiExtension());

        if ($cache->isCached($cacheKey)) {
            $cachedContent = $cache->getCache($cacheKey);

            if ($cachedContent !== false && !empty($cachedContent)) {
                return $cachedContent;
            }
        }

        $result = $commonMark->convert($md);

        $cache->setCache($cacheKey, $result);
        return $result;
    }
}
