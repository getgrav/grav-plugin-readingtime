<?php namespace Grav\Common;

use Grav\Common\Grav;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class TwigReadingTimeFilters extends \Twig_Extension
{
  private $grav;

  public function __construct()
  {
      $this->grav = Grav::instance();
  }

  public function getName()
  {
    return 'TwigReadingTimeFilters';
  }

  public function getFilters()
  {
    return [
      new \Twig_SimpleFilter( 'readingtime', [$this, 'getReadingTime'] )
    ];
  }

  public function getReadingTime( $content, $params = array() )
  {

    $this->mergeConfig($this->grav['page']);
    $language = $this->grav['language'];

    $options = array_merge($this->grav['config']->get('plugins.readingtime'), $params);

    $words = str_word_count(strip_tags($content));
    $wpm = $options['words_per_minute'];

    $minutes_short_count = floor($words / $wpm);
    $seconds_short_count = floor($words % $wpm / ($wpm / 60));

    $round = $options['round'];
    if ($round == 'minutes') {
      $minutes_short_count = round(($minutes_short_count*60 + $seconds_short_count) / 60);

      if ( $minutes_short_count < 1 ) {
        $minutes_short_count = 1;
      }

      $seconds_short_count = 0;
    }

    $minutes_long_count = number_format($minutes_short_count, 2);
    $seconds_long_count = number_format($seconds_short_count, 2);
    $minutes_text = $language->translate(( $minutes_short_count == 1 ) ? 'PLUGIN_READINGTIME.MINUTE' : 'PLUGIN_READINGTIME.MINUTES');
    $seconds_text = $language->translate(( $seconds_short_count == 1 ) ? 'PLUGIN_READINGTIME.SECOND' : 'PLUGIN_READINGTIME.SECONDS');

    $replace = [
      'minutes_short_count'   => $minutes_short_count,
      'seconds_short_count'   => $seconds_short_count,
      'minutes_long_count'    => $minutes_long_count,
      'seconds_long_count'    => $seconds_long_count,
      'minutes_text'          => $minutes_text,
      'seconds_text'          => $seconds_text
    ];

    $result = $options['format'];

    foreach ( $replace as $key => $value ) {
      $result = str_replace('{' . $key . '}', $value, $result);
    }

    return $result;
  }

  private function mergeConfig( Page $page )
  {
    $defaults = (array) $this->grav['config']->get('plugins.readingtime');
    if ( isset($page->header()->readingtime) ) {
      $this->grav['config']->set('plugins.readingtime', array_merge($defaults, $page->header()->readingtime));
    }
  }
}
