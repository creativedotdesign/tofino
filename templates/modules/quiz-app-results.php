<?php
if (!is_plugin_active('dci-quiz-app/index.php')) {
  echo 'Please install and activate the DCI Quiz Plugin.';
} else {
  $DCI_Quiz = new DCI_Quiz();

  $terms = null;

  if (isset($_POST['checkedTerms'])) {
    $terms = $_POST['checkedTerms'];
  }

  $results = $DCI_Quiz->get_quiz_results($terms);

  // Check if we have results
  if (!$results->have_posts()) {
    $no_results = true;

    // If no results, get all results
    $results = $DCI_Quiz->get_quiz_results();
  } else {
    $no_results = false;
  }
}

if ($results->have_posts()) : 
  if ($no_results) : 
    $no_results_text = get_field('quiz_no_results_text', 'option'); ?>
    <div class="container py-16">
      <div class="w-10/12 mx-auto text-center">
        <?php echo $no_results_text; ?>
      </div>
    </div><?php
  endif; ?>

  <section>
    <div class="container py-16">
      <div class="w-10/12 mx-auto"><?php
        $total_posts = count($results->posts);
        $i = 1;

        while ($results->have_posts()) : $results->the_post();
          $result_image_id = get_field('quiz_result_image');
          $cta_link = get_field('quiz_result_cta') ?>

          <!-- Result -->
          <div class="mb-6 last:mb-0">

            <!-- Result Count -->
            <span class="text-blue-500 font-bold">
              <?php echo $i; ?> of <?php echo $total_posts; ?>
            </span>

            <!-- Image -->
            <div class="w-full">
              <div class="relative aspect-[5/3]"><?php
                if ($result_image_id) :
                  $result_image_values = \Tofino\Helpers\responsive_image_attribute_values($result_image_id); ?>
                  <img src="<?php echo $result_image_values['src']; ?>"
                      srcset="<?php echo $result_image_values['srcset']; ?>"
                      sizes="<?php echo $result_image_values['sizes']; ?>"
                      loading="lazy"
                      alt="<?php echo $result_image_values['alt']; ?>"
                      width="315"
                      height="185"
                      class="absolute inset-0 object-cover w-full h-full"
                  /><?php
                endif; ?>
              </div>
            </div>
            <!-- Close Image -->

            <!-- Content Wrapper -->
            <div class="flex flex-col lg:flex-row lg:justify-between">

              <!-- Place Details -->
              <div class="w-full flex flex-col lg:w-1/2 lg:pr-6">

                <!-- Place Name -->
                <span class="text-blue-500 font-bold uppercase mb-3">
                  <?php the_title(); ?>
                </span>

                <!-- Title -->
                <h4 class="text-3xl mb-2">
                  <?php echo get_field('quiz_result_title'); ?>
                </h4>

                <!-- Sub Title -->
                <span class="text-lg mb-1">
                  <?php echo get_field('quiz_result_sub_title'); ?>
                </span>

                <!-- Text -->
                <p>
                  <?php echo get_field('quiz_result_text'); ?>
                </p>

                <!-- CTA --><?php
                if ($cta_link) :
                  $cta_target = esc_attr($cta_link['target']); ?>
                  <a href="<?php echo esc_url($cta_link['url']); ?>" target="<?php echo ($cta_target === '_blank' ? $cta_target : '_self'); ?>"
                    class="mt-4 self-start"
                  >
                    <?php echo esc_html($cta_link['title']); ?>
                  </a><?php
                endif; ?>

              </div>
              <!-- Close Place Details --><?php

              if (have_rows('quiz_result_metrics')) : ?>
                <!-- Metrics -->
                <div class="w-full mt-4 lg:w-1/2 lg:pl-6"><?php
                  while (have_rows('quiz_result_metrics')) : the_row(); ?>

                    <!-- Metric -->
                    <div class="flex flex-col py-5">

                      <!-- Top Text -->
                      <span class="text-blue-500 mb-1 text-sm">
                        <?php echo get_sub_field('text'); ?>
                      </span>

                      <!-- Metric Text -->
                      <span class="text-2xl">
                        <?php echo get_sub_field('metric'); ?>
                      </span>

                      <!-- Bottom Text -->
                      <span class="text-sm">
                        <?php echo get_sub_field('bottom_text'); ?>
                      </span>

                    </div><?php

                  endwhile; ?>
                </div><?php 
              endif; ?>

            </div>
            <!-- Close Content Wrapper -->

          </div>
          <!-- Close Result --><?php

          $i++;
        endwhile;
        wp_reset_query(); ?>

        <div class="w-full flex justify-center">
          <a href="#dci-show-quiz"
            >
            Take the Quiz again
          </a>
        </div>

      </div>
    </div>
  </section><?php
endif;