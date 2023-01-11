<?php

namespace AntCMS;

use AntCMS\AntCache;
use AntCMS\AntConfig;

class AntKeywords
{
    public function generateKeywords($content = '', $count = 15)
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

        // A bunch of characters we don't want to use for keyword generation
        $stopWords = array(' a ', ' an ', ' and ', ' are ', ' as ', ' at ', ' be ', ' by ', ' for ', ' from ', ' has ', ' have ', ' in ', ' is ', ' it ', ' its ', ' of ', ' on ', ' that ', ' the ', ' to ', ' was ', ' were ', ' will ', ' with ');
        $symbols = array('$', '€', '£', '¥', 'CHF', '₹', '+', '-', '×', '÷', '=', '>', '<', '.', ',', ';', ':', '!', '?', '"', '\'', '(', ')', '[', ']', '{', '}', '©', '™', '°', '§', '¶', '•', '_', '/');
        $markdownSymbols = array('#', '##', '###', '####', '#####', '~~', '__', '**', '`', '``', '```', '*', '+', '>', '[', ']', '(', ')', '!', '&', '|');
        $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $commonPronouns = array('he', 'him', 'his', 'she', 'her', 'hers', 'they', 'them', 'theirs');

        //Strip the aforementioned characters away
        $content = strtolower($content);
        $content = str_replace($stopWords, ' ', $content);
        $content = str_replace($symbols, ' ', $content);
        $content = str_replace($markdownSymbols, ' ', $content);
        $content = str_replace($numbers, ' ', $content);
        $content = str_replace($commonPronouns, ' ', $content);

        //Convert to an arrays
        $words = explode(' ', $content);

        // Remove newlines
        $words = array_map(function ($key) {
            return preg_replace('~[\r\n]+~', ' ', $key);
        }, $words);

        // Handle potentially empty keys
        $words = array_filter($words);

        // Then finally we count and sort the keywords, returning the top ones
        $word_counts = array_count_values($words);

        arsort($word_counts);

        $count = (count($word_counts) < $count) ? count($word_counts) : $count;
        $keywords = array_slice(array_keys($word_counts), 0, $count);
        $keywords = implode(', ', $keywords);
        $keywords = mb_substr($keywords, 3);

        $cache->setCache($cacheKey, $keywords);
        return $keywords;
    }
}
