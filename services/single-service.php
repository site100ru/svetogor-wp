<?php
get_header();

// Получаем данные услуги
$service_id = get_the_ID();
$service_title = get_the_title();
$service_content = get_the_content();
$hero_bg_id = get_post_meta($service_id, 'service_hero_bg', true);
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
<section class="hero-section hero-section" <?php if ($hero_bg_url): ?>style="background-image: url('<?php echo esc_url($hero_bg_url); ?>');"<?php endif; ?>>
  <div class="container position-relative">
    <div class="row">
      <div class="col hero-content">
        <h1><?php echo $service_title; ?></h1>
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
          <a href="<?php echo get_post_type_archive_link('services'); ?>"
            class="text-decoration-none text-secondary">Услуги</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $service_title; ?></li>
      </ol>
    </nav>
  </div>
</section>

<!-- КОНТЕНТ УСЛУГИ -->
<?php
// Используем функцию рендеринга контента услуг
render_service_content($service_content);
?>

<?php get_template_part('template-parts/blocks/forms/form'); ?>

<?php get_footer(); ?>