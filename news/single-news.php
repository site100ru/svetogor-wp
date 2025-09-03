<?php
get_header();

// Получаем данные текущей новости
$news_id = get_the_ID();
$news_title = get_the_title();
$news_content = get_the_content();
$hero_bg_id = get_post_meta($news_id, 'news_hero_bg', true);
$hero_bg_url = '';

// Получаем URL фонового изображения
if ($hero_bg_id) {
  $hero_bg_data = wp_get_attachment_image_src($hero_bg_id, 'full');
  if ($hero_bg_data) {
    $hero_bg_url = $hero_bg_data[0];
  }
}
?>

<!-- HERO СЕКЦИЯ -->
<section class="hero-section hero-section" <?php if ($hero_bg_url): ?>style="background-image: url('<?php echo esc_url($hero_bg_url); ?>');" <?php endif; ?>>
  <div class="container position-relative">
    <div class="row">
      <div class="col hero-content">
        <h1><?php echo $news_title; ?></h1>
      </div>
    </div>
  </div>
</section>

<!-- ХЛЕБНЫЕ КРОШКИ -->
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
        <li class="breadcrumb-item">
          <a href="<?php echo get_post_type_archive_link('news'); ?>" class="text-decoration-none text-secondary">
            Новости
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          <?php echo wp_trim_words($news_title, 6); ?>
        </li>
      </ol>
    </nav>
  </div>
</section>

<!-- ОСНОВНОЙ КОНТЕНТ -->
<?php
// Оптимизированное решение через WordPress hooks
if (!empty($news_content)) {
  render_news_content($news_content);
} else {
  // Если контента нет, показываем заглушку
  ?>
  <section class="section single-news-content">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
          <div class="news-content">
            <p class="text-muted">Содержимое новости не добавлено.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php
}
?>

<!-- Другие новости -->
<?php
// Получаем последние 3 новости, исключая текущую
$recent_news = new WP_Query(array(
  'post_type' => 'news',
  'posts_per_page' => 3,
  'post__not_in' => array($news_id),
  'post_status' => 'publish',
  'orderby' => 'date',
  'order' => 'DESC'
));

// Выводим блок только если есть другие новости
if ($recent_news->have_posts()): ?>
  <section class="section section-glide box-shadow-main no-border bg-grey">
    <div class="container">
      <div class="section-title text-center">
        <h2>Другие новости</h2>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ico/points.svg" alt="Точки" class="img-fluid">
      </div>

      <!-- Новости -->
      <div class="tab-pane fade show active" id="news" role="tabpanel" aria-labelledby="news-tab">
        <div class="row g-4 justify-content-center">

          <?php
          while ($recent_news->have_posts()):
            $recent_news->the_post();
            $other_news_id = get_the_ID();
            $other_news_title = get_the_title();
            $other_news_excerpt = get_news_excerpt($other_news_id);
            $other_news_date = get_the_date('d/m/Y');
            $other_featured_image = get_the_post_thumbnail_url($other_news_id, 'medium');
            $other_news_link = get_permalink($other_news_id);
            ?>

            <!-- Карточка новости -->
            <div class="col-12 col-md-6 col-lg-4">
              <a href="<?php echo $other_news_link; ?>" class="card h-100 bg-linear-gradient-wrapper text-decoration-none">
                <div class="card-img-container">
                  <?php if ($other_featured_image): ?>
                    <img src="<?php echo $other_featured_image; ?>" alt="<?php echo esc_attr($other_news_title); ?>"
                      class="card-img-top">
                  <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?php echo $other_news_title; ?></h5>
                  <p class="card-text mb-0">
                    <?php echo $other_news_excerpt; ?>
                  </p>
                  <div class="mt-auto d-flex justify-content-start align-items-center">
                    <span class="text-muted small"><?php echo $other_news_date; ?></span>
                  </div>
                </div>
              </a>
            </div>

          <?php endwhile; ?>

        </div>

        <!-- Кнопка Все новости -->
        <div class="mt-5 text-center">
          <a href="<?php echo get_post_type_archive_link('news'); ?>" class="btn">Все новости</a>
        </div>
      </div>
    </div>
  </section>
<?php endif;
?>

<?php get_footer(); ?>