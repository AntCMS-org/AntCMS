<?php

namespace AntCMS;

use AntCMS\AntCache;

class AntKeywords
{
    public function generateKeywords($content = '', $count = 15)
    {
        $cache = new AntCache();
        $cacheKey = hash('sha3-512', $content).'keywords';

        if ($cache->isCached($cacheKey)) {
            $cachedKeywords = $cache->getCache($cacheKey);

            if ($cachedKeywords !== false && !empty($cachedKeywords)) {
                return $cachedKeywords;
            }
        }

        $stopWords = array('a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'have', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'were', 'will', 'with');
        $symbols = array('$', '€', '£', '¥', 'CHF', '₹', '+', '-', '×', '÷', '=', '>', '<', '.', ',', ';', ':', '!', '?', '"', '\'', '(', ')', '[', ']', '{', '}', '©', '™', '°', '§', '¶', '•');
        $markdownSymbols = array('#', '##', '###', '####', '#####', '~~', '__', '**', '`', '``', '```', '*', '+', '>', '[', ']', '(', ')', '!', '&', '|');

        $words = explode(' ', $content);

        // Remove additional newlines and spaces
        $words = array_map(function ($key) {
            $key = preg_replace('~[\r\n]+~', '', $key);
            return trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $key)));
        }, $words);

        $words = array_diff($words, $stopWords);
        $words = array_diff($words, $symbols);
        $words = array_diff($words, $markdownSymbols);

        // Count the frequency of each word
        $word_counts = array_count_values($words);

        // Sort the word counts in descending order
        arsort($word_counts);

        // The most frequently occurring words are at the beginning of the array
        $count = (count($word_counts) < $count) ? count($word_counts) : $count;
        $keywords = array_slice(array_keys($word_counts), 0, $count);
        $keywords = implode(', ', $keywords);

        $cache->setCache($cacheKey, $keywords);
        return $keywords;
    }
}
