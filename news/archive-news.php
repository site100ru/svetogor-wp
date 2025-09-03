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
        <li class="breadcrumb-item active" aria-current="page">Новости</li>
      </ol>
    </nav>
  </div>
</section>

<!-- КОНТЕНТ -->
<section class="section section-portfolio box-shadow-main no-border">
  <div class="container">
    <div class="section-content-cards">
      <!-- Заголовок -->
      <div class="section-title text-center">
        <h3>Новости</h3>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ico/points.svg" alt="Точки" class="img-fluid">
      </div>

      <!-- Карточки -->
      <div class="row">
        <?php
        // Настройка пагинации - 9 новостей на страницу
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $news_query = new WP_Query(array(
          'post_type' => 'news',
          'posts_per_page' => 15,
          'paged' => $paged,
          'post_status' => 'publish',
          'orderby' => 'date',
          'order' => 'DESC'
        ));

        if ($news_query->have_posts()):
          while ($news_query->have_posts()):
            $news_query->the_post();
            $news_id = get_the_ID();
            $news_title = get_the_title();
            $news_excerpt = get_news_excerpt($news_id);
            $news_date = get_the_date('d/m/Y');
            $featured_image = get_the_post_thumbnail_url($news_id, 'medium');
            $news_link = get_permalink($news_id);
            ?>

            <div class="col-12 col-md-6 col-lg-4 mb-4">
              <a href="<?php echo $news_link; ?>" class="card h-100 m-0 bg-linear-gradient-wrapper text-decoration-none">
                <div class="card-img-container">
                  <?php if ($featured_image): ?>
                    <img src="<?php echo $featured_image; ?>" alt="<?php echo esc_attr($news_title); ?>" class="card-img-top">
                  <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title">
                    <?php echo $news_title; ?>
                  </h5>
                  <p class="card-text mb-0">
                    <?php echo $news_excerpt; ?>
                  </p>
                  <div class="mt-auto d-flex justify-content-start align-items-center pt-2">
                    <span class="text-muted small mt-1"><?php echo $news_date; ?></span>
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
              <h4>Новости не найдены</h4>
              <p class="text-muted">В данный момент новостей нет. Зайдите позже!</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Пагинация -->
      <?php if ($news_query->max_num_pages > 1): ?>
        <?php custom_pagination($news_query); ?>
      <?php endif; ?>

      <?php wp_reset_postdata(); ?>
    </div>
  </div>
</section>

<?php get_footer(); ?>