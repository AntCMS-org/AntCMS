<?php

namespace AntCMS;

use ElGigi\CommonMarkEmoji\EmojiExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\Embed\Bridge\OscaroteroEmbedAdapter;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;
use SimonVomEyser\CommonMarkExtension\LazyImageExtension;
use Symfony\Contracts\Cache\ItemInterface;

class Markdown
{
    public static function parse(string $md, ?string $cacheKey = null): string
    {
        $cacheKey ??= Cache::createCacheKey($md, 'markdown');

        return Cache::get($cacheKey, function (ItemInterface $item) use ($md): string {

            // Fire the `onBeforeMarkdownParsed` and use the potentially modified markdown content for parsing
            $event = HookController::fire('onBeforeMarkdownParsed', ['markdown' => $md]);
            $markdown = $event-> getParameters()['markdown'] ?? $md;

            $config = Config::get();
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
            $environment->addExtension(new StrikethroughExtension());
            $environment->addExtension(new TableExtension());
            $environment->addExtension(new TaskListExtension());
            $environment->addExtension(new EmojiExtension());
            $environment->addExtension(new EmbedExtension());
            $environment->addExtension(new LazyImageExtension());
            $environment->addExtension(new DefaultAttributesExtension());

            $markdownConverter = new MarkdownConverter($environment);

            $parsed = $markdownConverter->convert($markdown)->__toString();
            $event = HookController::fire('onAfterMarkdownParsed', ['html' => $parsed]);
            return $event->getParameters()['html'] ?? $parsed;
        });
    }
}
