<?php get_header(); ?>

<!-- ХЛЕБНЫЕ КРОШКИ/ ЗАГОЛОВОК -->
<section class="section-mini">
  <div class="container">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="mb-0">
      <ol class="breadcrumb bg-transparent p-0 m-0">
        <li class="breadcrumb-item">
          <a href="<?php echo home_url(); ?>" class="text-decoration-none text-secondary">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ico/breadcrumbs.svg" loading="lazy" />
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Услуги</li>
      </ol>
    </nav>
  </div>
</section>

<!-- КОНТЕНТ -->
<section class="section section-page-comprehensive box-shadow-main">
  <div class="container">
    <!-- Комплексное оформление салона красоты -->
    <div class="section-content-cards">
      <div class="section-title text-center">
        <h3>Услуги</h3>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ico/points.svg" alt="Точки"
          class="img-fluid" />
      </div>

      <div class="row row-cards">
        <?php
        // Настройка пагинации - количество услуг на страницу
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $services_query = new WP_Query(array(
          'post_type' => 'services',
          'posts_per_page' => -1,
          'paged' => $paged,
          'post_status' => 'publish',
          'orderby' => 'date',
          'order' => 'DESC'
        ));

        if ($services_query->have_posts()):
          while ($services_query->have_posts()):
            $services_query->the_post();
            $service_id = get_the_ID();
            $service_title = get_the_title();
            $service_excerpt = get_service_excerpt($service_id);
            $featured_image = get_the_post_thumbnail_url($service_id, 'medium');
            $service_link = get_permalink($service_id);
            ?>

            <!-- Карточка услуги -->
            <div class="col-12 col-md-6 mb-4">
              <a href="<?php echo $service_link; ?>" class="card card-services-arhive">
                <div class="row g-0 align-items-center h-100">
                  <div class="col-12 col-lg-4 text-center card-img-container">
                    <?php if ($featured_image): ?>
                      <img src="<?php echo $featured_image; ?>" alt="<?php echo esc_attr($service_title); ?>"
                        class="img-fluid" />
                    <?php else: ?>
                      <img src="<?php echo wc_placeholder_img_src(); ?>" alt="<?php echo esc_attr($service_title); ?>"
                        class="img-fluid" />
                    <?php endif; ?>
                  </div>
                  <div class="col-12 col-lg-8">
                    <div class="card-body">
                      <h5 class="card-title mb-3"><?php echo $service_title; ?></h5>
                      <p class="card-text">
                        <?php echo $service_excerpt; ?>
                      </p>
                      <span class="btn btn-invert">Подробнее</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>

            <?php
          endwhile;
        else:
          ?>
          <div class="col-12">
            <div class="text-center py-5">
              <h4>Услуги не найдены</h4>
              <p class="text-muted">В данный момент услуг нет. Зайдите позже!</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php wp_reset_postdata(); ?>
    </div>
  </div>
</section>

<!-- КОНТЕНТ ИЗ АДМИНКИ -->
<?php

$services_page = get_page_by_path('services');
if ($services_page && $services_page->post_content) {
  render_service_content($services_page->post_content);
}
?>

<?php get_template_part('template-parts/blocks/forms/form'); ?>


<?php get_footer(); ?>