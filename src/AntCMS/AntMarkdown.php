<?php

namespace AntCMS;

use AntCMS\AntCache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
//use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;
use ElGigi\CommonMarkEmoji\EmojiExtension;

class AntMarkdown
{
    /**
     * @param string $md 
     * @return string 
     */
    public static function renderMarkdown(string $md)
    {
        $cache = new AntCache();
        $cacheKey = $cache->createCacheKey($md, 'markdown');
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());
        //$environment->addExtension(new DisallowedRawHtmlExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());
        $environment->addExtension(new EmojiExtension());
        $converter = new MarkdownConverter($environment);

        if ($cache->isCached($cacheKey)) {
            $cachedContent = $cache->getCache($cacheKey);

            if ($cachedContent !== false && !empty($cachedContent)) {
                return $cachedContent;
            }
        }

        $result = $converter->convert($md);

        $cache->setCache($cacheKey, $result);
        return $result;
    }
}
