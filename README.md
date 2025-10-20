# The Colour Mode Switcher plugin for MyBB

This plugin implements Javascript-based colour mode switching (dark, light, and auto-detect modes) for the [MyBB](https://mybb.com/) forum software version 1.8.*.

It was written for [Psience Quest](https://psiencequest.net/forums/) where I (Laird) had integrated the Roundo Lite and Roundo Darko themes into a single theme, and wanted a convenient way for members to switch between the two.

I've uploaded it to GitHub mostly for reference purposes for the development of colour mode switching for MyBB itself in version 1.9.

# Manual additions/requirements

1. A manually-created "darkmode.css" stylesheet within the set of stylesheets associated with the (combined Roundo) theme stipulated by tid in the plugin's settings. I synthesised this stylesheet based on the differences between Roundo Darko and Roundo Lite. The plugin references this stylesheet within `cms_hookin__global_intermediate()`.

2. The addition of the following lines to the `headerinclude` template in that Roundo theme:

```php
<script src="{$mybb->asset_url}/jscripts/colourmodeswitcher.js?ver=1.1.0"></script>
{$GLOBALS['colourmodeswitcher_head_html']}
```

3. The addition of the following lines to the `header_welcomeblock_member` template in that Roundo theme:

```php
<div class="float_right" id="member_colourmode_icons">
	<a href="" id="colourmode_light" title="Light colour scheme"{$GLOBALS['colourmode_light_class']}><i class="fas fa-sun"></i></a>
	<a href="" id="colourmode_dark" title="Dark colour scheme"{$GLOBALS['colourmode_dark_class']}><i class="fas fa-moon"></i></a>
	<a href="" id="colourmode_detect" title="Auto-detect colour scheme from OS"{$GLOBALS['colourmode_detect_class']}><i class="fas fa-adjust"></i></a>
</div>
```

# Notes, especially for converting to a MyBB 1.9 implementation

* The switcher icons are based on Font Awesome. I can't recall whether or not that's already available in 1.9, but if not, and the same icons are desired, then the following line (or similar; this is simply what Roundo uses) is additionally necessary in the `<head>` section:

```html
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
```

* The key to the auto-detect mode is the selective `@import` - based on either `[prefers-color-scheme](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-color-scheme): dark` or `prefers-dark-interface`, the latter of which is apparently non-standard and by now defunct, so can probably be omitted - of the darkmode CSS enclosed within the `<style>` element with id `colourmodeswitcher_style_element_detect`. This element (and thus the selective import) is selectively enabled/disabled via its `media` attribute (with `max-width` set to `1px` to disable it) depending on whether or not auto-detect mode is enabled/disabled.

* The plugin tries (successfully, at least mostly, I think) to support colour mode switching in the default editor (SCEditor) too, and, to that end, includes a dark mode stylesheet for that editor.

# Licence

GNU GPL version 3 or later.