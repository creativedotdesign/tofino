# templates

Template files go here. The naming of your template files is important. By naming a file correctly you can make it be used without any further work.

## Possible formats

 * `content-single-{post-type}-{post-slug}.php`
 * `content-single-{post-type}.php`
 * `content-single.php`
 * `content-page-{slug}.php`
 * `content-page.php`
 * `archive-{category}.php`
 * `archive-{taxonomy}-{term}.php`
 * `archive-{posttype}-{taxonomy}-{term}.php`
 * `archive-{post-type}.php`
 * `archive.php`

## Default Templates

Wordpress gives some default templates in the root theme folder. Apart from `header.php` and `footer.php` *you shouldn't have to edit these or create any new ones* - just create your own templates in this `templates` subdirectory.

Therefore *do not edit or create these files*:

* archive.php
* home.php
* index.php
* frontpage.php
* single.php

## Frontpage

Frontpage is the first page you see when arriving on the website.

To create a custom front page:

1. Create a new page
2. Select it in Settings > Reading
3. Create a custom template e.g. `templates/content-page-home.php`

## Home

The home page is the default post archive page.

To create a custom home page:

1. Create a new page
2. Select it in Settings > Reading
3. Create a custom template e.g. `templates/archive.php`
