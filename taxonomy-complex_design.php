<?php
/**
 * Шаблон архива таксономии "Комплексное оформление"
 */

defined('ABSPATH') || exit;

get_header('shop');

// Получаем текущий термин
$current_term = get_queried_object();
$linked_categories = get_term_meta($current_term->term_id, 'linked_categories', true);
$linked_categories = is_array($linked_categories) ? $linked_categories : array();

// Принудительно подключаем стили и скрипты для блока портфолио
wp_enqueue_style('glide-css', 'https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css', array(), '3.6.0');
wp_enqueue_script('glide-js', 'https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js', array(), '3.6.0', true);
wp_enqueue_script('portfolio-slider-js', get_template_directory_uri() . '/template-parts/blocks/portfolio-slider/portfolio-slider.js', array('jquery'), filemtime(get_template_directory() . '/template-parts/blocks/portfolio-slider/portfolio-slider.js'), true);
wp_localize_script('portfolio-slider-js', 'portfolio_ajax', array(
  'ajax_url' => admin_url('admin-ajax.php')
));
?>

<!-- ХЛЕБНЫЕ КРОШКИ -->
<section class="section-mini">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-0">
      <ol class="breadcrumb bg-transparent p-0 m-0">
        <li class="breadcrumb-item">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="text-decoration-none text-secondary">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ico/breadcrumbs.svg" loading="lazy" />
          </a>
        </li>
        <li class="breadcrumb-item">Комплексное оформление</li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo esc_html($current_term->name); ?></li>
      </ol>
    </nav>
  </div>
</section>

<!-- КОНТЕНТ -->
<section class="section section-page-comprehensive box-shadow-main">
  <div class="container">
    <div class="section-content-cards">
      <div class="section-title text-center">
        <h3>
          Комплексное оформление <br />
          <?php echo esc_html(mb_strtolower($current_term->name)); ?>
        </h3>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ico/points.svg" alt="Точки" class="img-fluid">
      </div>

      <?php if (!empty($linked_categories)): ?>
        <div class="row row-cards">
          <?php
          foreach ($linked_categories as $category_id) {
            $category = get_term($category_id, 'product_cat');
            if ($category && !is_wp_error($category)) {
              // Получаем фотографию категории из кастомного поля
              $category_photo_id = get_term_meta($category_id, 'category_photo', true);
              $thumbnail_url = '';

              if ($category_photo_id) {
                $thumbnail_url = wp_get_attachment_image_url($category_photo_id, 'medium');
              }

              // Fallback на placeholder
              if (!$thumbnail_url) {
                $thumbnail_url = wc_placeholder_img_src();
              }
              ?>
              <div class="col-12 col-md-6 mb-4 mb-md-0">
                <a href="<?php echo esc_url(get_term_link($category_id, 'product_cat')); ?>" class="card"
                  style="height: calc(100% - 12px);">
                  <div class="row g-0 align-items-center h-100">
                    <div class="col-12 col-lg-4 text-center card-img-container">
                      <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($category->name); ?>"
                        class="img-fluid" />
                    </div>
                    <div class="col-12 col-lg-8">
                      <div class="card-body">
                        <h5 class="card-title mb-3"><?php echo esc_html($category->name); ?></h5>
                        <p class="card-text">
                          <?php
                          if ($category->description) {
                            echo wp_trim_words($category->description, 20, '...');
                          } else {
                            echo 'Посмотрите нашу продукцию в категории ' . esc_html($category->name) . '.';
                          }
                          ?>
                        </p>
                        <span class="btn btn-invert">Подробнее</span>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
              <?php
            }
          }
          ?>
        </div>
      <?php else: ?>
        <div class="row">
          <div class="col-12 text-center">
            <p>К этому оформлению пока не привязаны категории товаров.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php get_template_part('template-parts/blocks/forms/extended-form'); ?>

<?php if ($current_term->description): ?>
  <section class="section bg-grey">
    <div class="container">
      <div class="row">
        <div class="col-12 col-lg-10 mx-auto text-lg-center">
          <?php echo wpautop(wp_kses_post($current_term->description)); ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php
// БЛОК ПОРТФОЛИО (без данных из админки)
$portfolio_template = get_template_directory() . '/template-parts/blocks/portfolio-slider/portfolio-slider.php';
if (file_exists($portfolio_template)) {
  // Переопределяем только фон
  add_filter('acf/load_value', function ($value, $post_id, $field) {
    if ($field['name'] == 'slider_background') {
      return 'white'; 
    }
    if ($field['name'] == 'show_all_works_button') {
      return True;
    }
    return $value;
  }, 10, 3);

  // Подключаем шаблон
  include $portfolio_template;

  // Убираем фильтр
  remove_all_filters('acf/load_value');
}
?>


<?php
get_footer('shop');
?>