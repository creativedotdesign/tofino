<main>
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-6">
        <?php while (have_posts()) : the_post(); ?>
          <h1><?php the_title(); ?></h1>

          <!-- Empty div for form response -->
          <div class="js-form-result"></div>

          <form name="contact" class="contact-form form-processor" id="contact-form">

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
             <label for="message"><?php _e('Message', 'tofino'); ?></label>
             <textarea class="form-control" name="message" id="message" rows="3" placeholder="<?php _e('Message', 'tofino'); ?>" required="required"></textarea>
             <small class="text-muted"><?php _e("Tell us what you want us to know.", 'tofino'); ?></small>
           </fieldset>

          <?php if (true == get_theme_mod('contact_form_captcha')) : ?>
            <!-- Not a Robot -->
            <fieldset class="form-group">
              <div class="g-recaptcha" data-size="normal" data-theme="light" data-sitekey="<?php echo get_theme_mod('captcha_site_key'); ?>"></div>
              <small class="text-muted"><?php _e('Human tester', 'tofino'); ?></small>
              <script type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script>
            </fieldset><?php
          endif; ?>

           <button type="submit" class="btn btn-primary"><?php _e('Submit', 'tofino'); ?></button>

          </form>
        <?php endwhile; ?>
      </div>
      <div class="col-xs-12 col-sm-4 col-sm-offset-2">
        <address>
        <?php echo nl2br(ot_shortcode('address')); ?>
        </address>

        <?php echo ot_shortcode('telephone_number'); ?><br>
        <?php echo ot_shortcode('email_address'); ?>
      </div>
    </div>
  </div>
</main>
