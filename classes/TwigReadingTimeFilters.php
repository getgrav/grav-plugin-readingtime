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
    $estimate_range = ($options['estimate_range'] / 100);
    $range_str = $options['range_str'];

    $minutes_short_count = floor($words / $wpm);
    $seconds_short_count = floor($words % $wpm / ($wpm / 60));

    $minutes_low_range = floor(($words * (1 - $estimate_range)) / $wpm);
    $minutes_high_range = floor(($words * (1 + $estimate_range)) / $wpm);

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

          $minutes_low_range += floor(($seconds_images * (1 - $estimate_range)) / 60);
          $minutes_high_range += floor(($seconds_images * (1 - $estimate_range)) / 60);
        } else {
          $this->grav['log']->error("Plugin 'readingtime' - seconds_per_image failed regex vadation");
        }
      }
    }

    $round = $options['round'];
    if ($round == 'minutes') {
      $minutes_short_count = round(($minutes_short_count * 60 + $seconds_short_count) / 60);

      $minutes_low_range = round(($minutes_low_range * 60 + $seconds_low_range) / 60);
      $minutes_high_range = round(($minutes_high_range * 60 + $seconds_high_range) / 60);

      if ( $minutes_short_count < 1 ) {
        $minutes_short_count = 1;

        $minutes_low_range = 0;
        $minutes_high_range = 1;
      }

      $seconds_short_count = 0;
    }

    $minutes_long_count = number_format($minutes_short_count, 2);
    $seconds_long_count = number_format($seconds_short_count, 2);
    $minutes_low_range_long = number_format($minutes_low_range, 2);
    $minutes_high_range_long = number_format($minutes_high_range, 2);

    if ($minutes_low_range == $minutes_high_range or $minutes_low_range == 0) {
      $minutes_short_range = $minutes_short_count;
      $minutes_long_range = $minutes_long_count;
    } elseif ($minutes_low_range == 0) {
      $minutes_short_range = $minutes_short_count;
      $minutes_long_range = $minutes_long_count;
    } else {
      $minutes_short_range = (
        $minutes_low_range . $range_str . $minutes_high_range
      );
      $minutes_long_range = (
        $minutes_low_range_long . $range_str . $minutes_high_range_long
      );
    }

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
      'minutes_short_count' => $minutes_short_count,
      'minutes_short_range' => $minutes_short_range,
      'seconds_short_count' => $seconds_short_count,
      'minutes_long_count'  => $minutes_long_count,
      'minutes_long_range'  => $minutes_long_range,
      'seconds_long_count'  => $seconds_long_count,
      'minutes_text'        => $minutes_text,
      'seconds_text'        => $seconds_text
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
