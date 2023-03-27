<?php

namespace AntCMS;

use AntCMS\AntCache;
use AntCMS\AntConfig;
use AntCMS\AntCMS;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
//use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;
use ElGigi\CommonMarkEmoji\EmojiExtension;
use League\CommonMark\Extension\Embed\Bridge\OscaroteroEmbedAdapter;
use League\CommonMark\Extension\Embed\EmbedExtension;
use SimonVomEyser\CommonMarkExtension\LazyImageExtension;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;

class AntMarkdown
{
    /** 
     * @return string 
     */
    public static function renderMarkdown(string $md)
    {
        $antCache = new AntCache();
        $cacheKey = $antCache->createCacheKey($md, 'markdown');
        $config = AntConfig::currentConfig();

        if ($antCache->isCached($cacheKey)) {
            $cachedContent = $antCache->getCache($cacheKey);

            if ($cachedContent !== false && !empty($cachedContent)) {
                return $cachedContent;
            }
        }

        $defaultAttributes = [];
        $themeConfig = AntCMS::getThemeConfig();
        foreach (($themeConfig['defaultAttributes'] ?? []) as $class => $attributes) {
            $reflectionClass = new \ReflectionClass($class);
            $fqcn = $reflectionClass->getName();
            $defaultAttributes[$fqcn] = $attributes;
        }

        $mdConfig = [
            'embed' => [
                'adapter' => new OscaroteroEmbedAdapter(),
                'allowed_domains' => $config['embed']['allowed_domains'] ?? [],
                'fallback' => 'link',
            ],
            'default_attributes' => $defaultAttributes,
        ];

        $environment = new Environment($mdConfig);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());
        //$environment->addExtension(new DisallowedRawHtmlExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());
        $environment->addExtension(new EmojiExtension());
        $environment->addExtension(new EmbedExtension());
        $environment->addExtension(new LazyImageExtension());
        $environment->addExtension(new DefaultAttributesExtension());

        $markdownConverter = new MarkdownConverter($environment);

        $renderedContent = $markdownConverter->convert($md);

        $antCache->setCache($cacheKey, $renderedContent);
        return $renderedContent;
    }
}
