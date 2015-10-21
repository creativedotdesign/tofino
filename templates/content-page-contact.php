<main class="wrapper" role="main">
  <div class="container">
    <div class="row">
      <div class="col-xs-6">
        <?php while (have_posts()) : the_post(); ?>
          <h1><?php the_title(); ?></h1>

          <form name="contact" id="contact-form">
          <!-- Empty div container for form response -->
          <div class="js-form-result"></div>


            <!-- Name / Text input -->
            <fieldset class="form-group">
              <label for="name"><?php _e('Name', 'tofino'); ?></label>
              <input type="text" name="name" class="form-control" id="name" placeholder="<?php _e('Your name', 'tofino'); ?>" required="required">
              <small class="text-muted"><?php _e("So we know who you are.", 'tofino'); ?></small>
            </fieldset>

            <!-- Email address -->
            <fieldset class="form-group">
              <label for="email"><?php _e('Email address', 'tofino'); ?></label>
              <input type="email" name="email" class="form-control" id="email" placeholder="<?php _e('Your email', 'tofino'); ?>" required="required">
              <small class="text-muted"><?php _e("We'll never share your email with anyone else.", 'tofino'); ?></small>
            </fieldset>

            <!-- Message / Textarea -->
            <fieldset class="form-group">
             <label for="message">Message</label>
             <textarea class="form-control" name="message" id="message" rows="3" placeholder="Message" required="required"></textarea>
             <small class="text-muted"><?php _e("Tell us what you want us to know.", 'tofino'); ?></small>
           </fieldset>

          <?php if (true == ot_get_option('enable_captcha_checkbox')) : ?>
            <!-- Not a Robot -->
            <fieldset class="form-group">
              <div class="g-recaptcha" data-size="normal" data-theme="light" data-sitekey="<?php echo ot_get_option('captcha_site_key'); ?>"></div>
              <small class="text-muted"><?php _e('Human tester', 'tofino'); ?></small>
              <script type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script>
            </fieldset><?php
          endif; ?>

           <button type="submit" class="btn btn-primary"><?php _e('Submit', 'tofino'); ?></button>

          </form>
        <?php endwhile; ?>
      </div>
      <div class="col-xs-4 col-xs-offset-2">
        <?php echo ot_shortcode('address'); ?>
        <?php echo ot_shortcode('telephone_number'); ?>
        <?php echo ot_shortcode('email_address'); ?>
      </div>
    </div>
  </div>
</main>
