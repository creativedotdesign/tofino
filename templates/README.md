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
* index.php
* single.php
* page.php

## Front page

Front page is the first page you see when arriving on the website.

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

## Frontpage with Home page

If you want to set both a static Frontpage and a Home page. For example you have a blog separate to the main website content and Frontpage.

Create a page called home.php in the root including header.php and footer.php with a get_template_part pointing to a new template you'd create e.g. `templates/content-page-blog.php`

## Email templates

A default template has been provided (email/default-form.html) which is used in the AjaxForm class. Customize this html file or create a new file.
