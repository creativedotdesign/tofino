<main>
  <div class="container">
    <div class="w-full"><?php
      while (have_posts()) : the_post(); ?>
        <h1><?php echo \Tofino\Helpers\title(); ?></h1>

        <?php the_content(); ?>
        
        <!-- Empty div for form response -->
        <div class="js-form-result"></div>

        <form name="contact" class="contact-form" data-wp-action="contact-form">

        <div class="w-full">
          <!-- Name / Text input -->
          <label class="block">
            <span class="text-gray-700"><?php _e('Name', 'tofino'); ?></span>
            <input type="text" class="block w-full mt-1" placeholder="" />
          </label>

          <!-- Email address -->
          <label class="block">
            <span class="text-gray-700"><?php _e('Email address', 'tofino'); ?></span>
            <input type="email" class="block w-full mt-1" placeholder="" />
          </label>

          <!-- Phone / Text input -->
          <label class="block">
            <span class="text-gray-700"><?php _e('Phone Number', 'tofino'); ?></span>
            <input type="tel" class="block w-full mt-1" placeholder="" />
          </label>

          <!-- Message / Textarea -->
          <label class="block">
            <span class="text-gray-700"><?php _e('Message', 'tofino'); ?></span>
            <textarea class="block w-full mt-1" rows="3"></textarea>
          </label>

          <!-- Checkbox -->
          <div class="block">
            <div class="mt-2">
              <div>
                <label class="inline-flex items-center">
                  <input type="checkbox" required="required" checked />
                  <span class="ml-2"><?php _e('I agree'); ?></span>
                </label>
              </div>
            </div>
          </div>


          <?php if (true == get_theme_mod('contact_form_captcha')) : ?>
            <!-- Not a Robot -->
            <fieldset class="form-group">
              <div class="g-recaptcha" data-size="normal" data-theme="light" data-sitekey="<?php echo get_theme_mod('captcha_site_key'); ?>"></div>
              <small class="text-muted"><?php _e('Human tester', 'tofino'); ?></small>
              <script type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script>
            </fieldset><?php
          endif; ?>

          <button type="submit"><?php _e('Submit', 'tofino'); ?></button>

        </div>
        </form><?php
      endwhile; ?>
    </div>
  </div>
</main>
