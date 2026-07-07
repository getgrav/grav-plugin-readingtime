# Grav <sup>Better</sup>ReadingTime Plugin

**<sup>Better</sup>ReadingTime** is a [Grav](http://github.com/getgrav/grav) plugin which allows Grav to display the reading time of a page's content. This is especially useful for blogs and other sites as it gives the reader a quick idea of how much time they will need to set aside to read the page in full.

***This fork differs from the original in three main ways:***
1. ***Reading label is translated, not just "minutes" and "seconds".***
2. ***Reading time (and label) is cached, in translated form, in the page headers of every page. This negates the need to run the calculation on every page load if reading time is present in the frontmatter.***
3. ***Disabled seconds. Reading time is rounded to the nearest minute for a cleaner view.***

Enabling the plugin is very simple. Just install the plugin folder to `/user/plugins/` in your Grav install. By default, the plugin is enabled.

# Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the resulting folder to `readingtime`.

>> It is important that the folder be named `readingtime` as this is the folder referenced in the plugin's code.

The contents of the zipped folder should now be located in the `/your/site/grav/user/plugins/readingtime` directory.

>> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav), the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) plugins, and a theme to be installed in order to operate.

# Usage

### Initial Setup

Place the following line of code in the theme file you wish to add the ReadingTime plugin for:

```
{% if config.plugins.readingtime.enabled %}
{{ page.header.readingTime }}{{ page|readingtime }}
{% endif %}
```

You need both of these twig variables to make it work. I can't remember why both are required, but if I remove either one it breaks. So just use both.

I haven't tested image views so I can't tell you if they still work. I also dabbled with the "estimated reading time" PR on the original repo but eventually decided not to include it, so you might find vestigial code from that PR here that I forgot to clean up.

>> NOTE: Any time you are making alterations to a theme's files, you will want to duplicate the theme folder in the `user/themes/` directory, rename it, and set the new name as your active theme. This will ensure that you don't lose your customizations in the event that a theme is updated. Once you have tested the change thoroughly, you can delete or back up that folder elsewhere.

### Include image views
The number of seconds to view images is added to the reading time of text when `include_image_views` is set to true.

Images are identified by `<img>` tags in `page.content()`.

The default values for `seconds_per_image` (shown below) mean that the first image adds `12` seconds to the reading time, the second adds `11` seconds, the third adds `10` seconds, and so on.
Only integers, whitespace, and commas are permitted in the string.

```
seconds_per_image: '12,11,10,9,8,7,6,5,4,3'
```

If there are more images in a page than what is defined in `seconds_per_image` (e.g., more than 10 images in the default shown above) then subsequent images take the last value (`3` seconds in the default shown above).

The example below adds `5` seconds reading time for all images.

```
seconds_per_image: 5
```
