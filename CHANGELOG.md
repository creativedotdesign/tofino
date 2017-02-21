## Changelog

### 1.8.0: Feb 21 2017

- Rename .bitbucket-pipelines.yml #221
- Fix single quote converted to html attribute in AjaxForm #226
- Update to Bootstrap Alpha 6 #219
- Add more tracker support #222
- Contact form reply to address #223
- Add custom dashboard widget #224
- Update .gitignore to exclude nested vendor directories #232
- Update dependancies & gulp tasks
- Add social media network colors to SCSS file
- Add truncate SCSS mixin
- Update README files

### 1.7.0: Dec 2 2016

- New gulp tasks for performance / accessibility (PSI, WAVE, W3C)
- New gulp task for tunneling (Ngrok), required for above gulp tasks.
- Social media icon shortcode update
- BS Alpha 5 SCSS
- New validation library [Respect Validation](https://github.com/Respect/Validation)
- Various bug fixes
- Breakup Theme Options
- Restructure folders in lib
- Add yarn.lock file
- Updated README files
- Update dependancies
- Move theme options in the Customizer root panel.
- Add FragmentCache class
- Add page title helper function
- Add template part with arguments help function

### 1.6.0: Feb 19 2016

- Update deps
- Fix jQuery and Cookies JS module imports
- Real SVG Titles
- JS window.loaded function
- Add Vimeo icon to social icons
- Fix tracker issue
- Fix address multiline issue
- Data Tables
- AjaxForm, original button text if multiple buttons within form element
- JS routes files aren't being watched

### 1.5.0: Sept 13 2016

- AjaxForm JS as ES6 module
- Shrinkwrap NPM deps
- Allow custom variables to be passed to email template function
- Update WP_Customizer with native validation
- JS cookie expiry fix
- Add PHP version check
- Remove IE11 temp CSS flexbox fix
- Rename $color to $bar-color in variables.scss & nav.scss
- Update dev deps
- Update Bootstrap version
- Cleaner DOM routing for JS
- Misc bug fixes

### 1.4.0: May 29 2016

- Fix tracker URL issue
- Update NPM dependencies
- Add contact form docs
- Use Style lint replacing Sass lint
- Update login form admin logo hook due to WP 4.5 change
- Fix notices on archive template
- Add Instagram and Soundcloud icon
- Add WooCommerce reset stylesheet
- Update shortcode docs
- Fix mobile menu display issue
- Fix @extend inside @include
- Add WebFontLoader support with no FOUT theme option
- Featured images for all post types unless turned off
- Add pagination with Bootstrap 4 supported styles
- Include theme directory url in JS
- Add support for multiple CC email addresses
- AjaxForm server side field validation
- Various minor bug fixes
- Send custom email to user

### 1.3.0: Mar 31 2016

- SVG Shortcode documentation
- Comment out unused Bootstrap components
- Move lib includes out of composer.json
- Use NPM instead of Bower for front-end dependencies
- Misc bugfixes

This release uses ES6 to include and call Javascript libraries. Browserify and Babelify are included in the scripts build task.

### 1.2.0: Mar 16 2016

- Refactor gulpfile.js and split gulp tasks (and use eslint)
- Update npm dependencies
- Polyfill IE11 flexbox issues
- Fix BrowserSync css injection
- Add WPML template compatibility
- Docblock all functions and files
- Add AjaxForm class
- Update README to include info about deployment
- Replace OptionTree with WP Customizer for theme options
- Remove unit tests until a better solution is implemented
- Include default social network links and icons
- Misc bugfixes

### 1.1.0: Feb 19 2016

- Bugfixes and stabilize dependancies.
- Readme updates
- New: Critical CSS
- New: jQuery move to footer

### 1.0.0: Jan 5 2016

Initial Release
