<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

class ReadingTimePlugin extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'onPageContentProcessed' => ['onPageContentProcessed', 0]
        ];
    }

    public function onPageContentProcessed(Event $event)
    {
        $page = $event['page'];
        $cacheKey = 'readingtime-' . $page->id();

        // Ensure synchronous cache access
        $readingTime = $this->grav['cache']->fetch($cacheKey);
        if ($readingTime === false) {
            $content = $page->content();
            $readingTime = $this->calculateReadingTime($content);
            $this->grav['cache']->save($cacheKey, $readingTime);
        }

        $this->modifyHeader($page, $readingTime);
    }

    private function modifyHeader($page, $readingTime)
    {
        $header = $page->header();

        $minutes_short_count = $readingTime;
        $minutes_text = ($minutes_short_count == 1) ?
            $this->grav['language']->translate('PLUGIN_READINGTIME.MINUTE') :
            $this->grav['language']->translate('PLUGIN_READINGTIME.MINUTES');

        $readingTimeString = sprintf(
            '%s: %s %s',
            $this->grav['language']->translate('PLUGIN_READINGTIME.READING_LABEL'),
            $minutes_short_count,
            $minutes_text
        );

        $header->readingTime = $readingTimeString;
        $page->header($header);
    }

    private function calculateReadingTime($text)
    {
        $wordCount = str_word_count(strip_tags($text));
        $wordsPerMinute = 200;
        $readingTime = ceil($wordCount / $wordsPerMinute);

        return $readingTime;
    }
}
