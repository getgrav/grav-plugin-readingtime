<?php

namespace Grav\Plugin\ReadingTime;

use Grav\Common\Grav;
use Grav\Common\Page\Page;
use Twig_Extension;

class TwigReadingTimeFilters extends Twig_Extension
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

  public function validatePattern($seconds_per_image)
  {
    // Get regex that is used in the user interface
    $pattern = '/' . $this->grav['plugins']->get('readingtime')->blueprints()->schema()->get('seconds_per_image')['validate']['pattern'] . '/';

    if (preg_match($pattern, $seconds_per_image, $matches) === false) {
      return false;
    }

    // Note: "$matches[0] will contain the text that matched the full pattern"
    // https://www.php.net/manual/en/function.preg-match.php
    return strlen($seconds_per_image) === strlen($matches[0]);
  }

  public function getReadingTime( $content, $params = array() )
  {

    $this->mergeConfig($this->grav['page']);
    $language = $this->grav['language'];

    $options = array_merge($this->grav['config']->get('plugins.readingtime'), $params);

    $words = count(preg_split('/\s+/', strip_tags((string) $content)) ?: []);
    $wpm = $options['words_per_minute'];

    $minutes_short_count = floor($words / $wpm);
    $seconds_short_count = floor($words % $wpm / ($wpm / 60));

    if ($options['include_image_views']) {
      $stripped = strip_tags($content, "<img>");
      $images_in_content = substr_count($stripped, "<img ");

      if ($images_in_content > 0) {
        if ($this->validatePattern($options['seconds_per_image'])) {

          // assumes string only contains integers, commas, and whitespace
          $spi = preg_split('/\D+/', trim($options['seconds_per_image']));
          $seconds_images = 0;

          for ($i = 0; $i < $images_in_content; ++$i) {
            $seconds_images += $i < count($spi) ? $spi[$i] : end($spi);
          }

          $minutes_short_count += floor($seconds_images / 60);
          $seconds_short_count += $seconds_images % 60;
        } else {
          $this->grav['log']->error("Plugin 'readingtime' - seconds_per_image failed regex vadation");
        }
      }
    }

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
    
    if (array_key_exists('minute_label', $options) and $minutes_short_count == 1) {
      $minutes_text = $options['minute_label'];
    } elseif (array_key_exists('minutes_label', $options) and $minutes_short_count > 1) {
      $minutes_text = $options['minutes_label'];
    } else {
      $minutes_text = $language->translate(( $minutes_short_count == 1 ) ? 'PLUGIN_READINGTIME.MINUTE' : 'PLUGIN_READINGTIME.MINUTES');
    }

    if (array_key_exists('second_label', $options) and $seconds_short_count == 1) {
      $seconds_text = $options['second_label'];
    } elseif (array_key_exists('seconds_label', $options) and $seconds_short_count > 1) {
      $seconds_text = $options['seconds_label'];
    } else {
      $seconds_text = $language->translate(( $seconds_short_count == 1 ) ? 'PLUGIN_READINGTIME.SECOND' : 'PLUGIN_READINGTIME.SECONDS');
    }

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
