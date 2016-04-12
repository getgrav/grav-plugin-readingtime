# Grav ReadingTime Plugin

**ReadingTime** is a [Grav](http://github.com/getgrav/grav) plugin which allows Grav to display the reading time of a page's content. This is especially useful for blogs and other sites as it gives the reader a quick idea of how much time they will need to set aside to read the page in full.

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
{{ page.content|readingtime }}
```

You need to pass a Twig Filter 'readingtime' to display the reading time values. You can translate the labels with this example:

```
{{ page.content|readingtime({'minutes_label': 'Minuti', 'minute_label': 'Minuto', 'seconds_label': 'Secondi', 'second_label': 'Secondo'}) }}
```

I used Italian translation for the labels, you can change with your language.

If you need you can change the format with this avariable variables (the code is default format):

```
{{ page.content|readingtime({'format': '{minutes_short_count} {minutes_text}, {seconds_short_count} {seconds_text}'}) }}
```

Available variables:

|      Variable     |       Description       |                                   Example                                    |
| :---------------- | :---------------------- | :--------------------------------------------------------------------------- |
| `{minute_label}`  | Minute Label (Singular) | `minute`                                                                     |
| `{minutes_label}` | Minutes Label (Plural)  | `minutes`                                                                    |
| `{second_label}`  | Second Label (Singular) | `second`                                                                     |
| `{seconds_label}` | Second Label (Plural)   | `seconds`                                                                    |
| `{format}`        | Display Format          | `{minutes_text} {minutes_short_count}, {seconds_text} {seconds_short_count}` |

Not available to edit but used in the format variable:

|         Variable        |               Description                | Example |
| :---------------------- | :--------------------------------------- | :------ |
| `{minutes_short_count}` | Displays Minutes with Abbreviated Digits | `2`     |
| `{seconds_short_count}` | Displays Seconds with Abbreviated Digits | `9`     |
| `{minutes_long_count}`  | Displays Minutes in Double Digits        | `02`    |
| `{seconds_long_count}`  | Displays Seconds in Double Digits        | `09`    |

Display variables for text labels:

|     Variable     |                                   Description                                    |  Example  |
| :--------------- | :------------------------------------------------------------------------------- | :-------- |
| `{minutes_text}` | Displays the Minutes Text Label (Singular or Plural, Based on Number of Minutes) | `minute`  |
| `{seconds_text}` | Displays the Seconds Text Label (Singular or Plural, Based on Number of Seconds) | `seconds` |

>> NOTE: Any time you are making alterations to a theme's files, you will want to duplicate the theme folder in the `user/themes/` directory, rename it, and set the new name as your active theme. This will ensure that you don't lose your customizations in the event that a theme is updated. Once you have tested the change thoroughly, you can delete or back up that folder elsewhere.
