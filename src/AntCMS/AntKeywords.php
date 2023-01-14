<?php

namespace AntCMS;

use AntCMS\AntCache;
use AntCMS\AntConfig;
use DonatelloZa\RakePlus\RakePlus;

class AntKeywords
{
    /**
     * @param string $content 
     * @param int $count 
     * @return string 
     */
    public function generateKeywords(string $content = '', int $count = 15)
    {
        $cache = new AntCache();
        $cacheKey = $cache->createCacheKey($content, 'keywords');

        if (!AntConfig::currentConfig('generateKeywords')) {
            return '';
        }

        if ($cache->isCached($cacheKey)) {
            $cachedKeywords = $cache->getCache($cacheKey);

            if ($cachedKeywords !== false && !empty($cachedKeywords)) {
                return $cachedKeywords;
            }
        }

        $keywords = RakePlus::create($content, 'en_US', $count)->keywords();
        $keywords = implode(",", $keywords);    
        $cache->setCache($cacheKey, $keywords);
        return $keywords;
    }
}
