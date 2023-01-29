<?php

namespace AntCMS;

use AntCMS\AntCache;
use AntCMS\AntConfig;
use DonatelloZa\RakePlus\RakePlus;

class AntKeywords
{
    /** 
     * @return string 
     */
    public function generateKeywords(string $content = '', int $count = 15)
    {
        $antCache = new AntCache();
        $cacheKey = $antCache->createCacheKey($content, 'keywords');

        if (!AntConfig::currentConfig('generateKeywords')) {
            return '';
        }

        if ($antCache->isCached($cacheKey)) {
            $cachedKeywords = $antCache->getCache($cacheKey);

            if ($cachedKeywords !== false && !empty($cachedKeywords)) {
                return $cachedKeywords;
            }
        }

        $keywords = RakePlus::create($content, 'en_US', $count)->keywords();
        $keywords = implode(",", $keywords);
            
        $antCache->setCache($cacheKey, $keywords);
        return $keywords;
    }
}
