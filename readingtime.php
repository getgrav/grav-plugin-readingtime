<?php namespace Grav\Plugin;

use Grav\Common\Plugin;

class ReadingTimePlugin extends Plugin
{
  public static function getSubscriptedEvents()
  {
    return [
      'onPluginsInitialized' => ['onPluginsInitialized', 0]
    ];
  }

  public function onPluginsInitialized()
  {
    if ( $this->isAdmin() ) {
      $this->active = false;
      return;
    }

    $this->enable([
      'onTwigExtensions' => ['onTwigExtensions', 0]
    ]);
  }

  public function onTwigExtensions()
  {
    require_once( __DIR__ . '/classes/TwigReadingTimeFilters.php' );

    $this->grav['twig']->twig->addExtension( new \Grav\Common\TwigReadingTimeFilters() );
  }
}
