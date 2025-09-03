<?php
/*
Template Name: Страница с хлебными крошками
*/

get_header();

// Получаем данные текущей страницы
$page_id = get_the_ID();
$page_title = get_the_title();
$page_content = get_the_content();
$hero_bg_id = get_post_meta($page_id, 'page_hero_bg', true);
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
        <h1><?php echo $page_title; ?></h1>
      </div>
    </div>
  </div>
</section>

<!-- ХЛЕБНЫЕ КРОШКИ -->
<section class="section-mini">
  <div class="container">
    <?php render_page_breadcrumbs($page_title); ?>
  </div>
</section>

<!-- ОСНОВНОЙ КОНТЕНТ -->
<?php
// Используем систему рендеринга контента аналогично новостям
if (!empty($page_content)) {
  render_page_content($page_content);
} else {
  // Если контента нет, показываем заглушку
  ?>
  <section class="section single-page-content">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
          <div class="page-content">
            <p class="text-muted">Содержимое страницы не добавлено.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php
}
?>

<?php get_footer(); ?>