<?php
/**
 * Функции для системы услуг
 */

// Регистрируем кастомный пост-тайп "Услуги"
function register_services_post_type()
{
  $labels = array(
    'name' => 'Услуги',
    'singular_name' => 'Услуга',
    'add_new' => 'Добавить новую',
    'add_new_item' => 'Добавить новую услугу',
    'edit_item' => 'Редактировать услугу',
    'new_item' => 'Новая услуга',
    'view_item' => 'Посмотреть услугу',
    'search_items' => 'Поиск услуг',
    'not_found' => 'Услуги не найдены',
    'not_found_in_trash' => 'Услуги не найдены в корзине',
    'all_items' => 'Все услуги',
    'menu_name' => 'Услуги',
    'name_admin_bar' => 'Услуга',
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'rewrite' => array('slug' => 'services'),
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 5,
    'menu_icon' => 'dashicons-admin-tools',
    'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
    'show_in_rest' => true, // Для поддержки Gutenberg
  );

  register_post_type('services', $args);
}
add_action('init', 'register_services_post_type');

// Функция для подключения шаблонов услуг из папки services
function load_services_templates($template)
{
  // Для архивной страницы услуг
  if (is_post_type_archive('services')) {
    $services_archive = get_template_directory() . '/services/archive-services.php';
    if (file_exists($services_archive)) {
      return $services_archive;
    }
  }

  // Для отдельной страницы услуги
  if (is_singular('services')) {
    $services_single = get_template_directory() . '/services/single-service.php';
    if (file_exists($services_single)) {
      return $services_single;
    }
  }

  return $template;
}
add_filter('template_include', 'load_services_templates');

// Добавление мета-бокса для фонового изображения hero-секции для услуг
function add_service_hero_bg_meta_box()
{
  add_meta_box(
    'service_hero_bg',
    'Фоновое изображение для заголовочной секции',
    'service_hero_bg_meta_box_callback',
    'services',
    'side',
    'default'
  );
}
add_action('add_meta_boxes', 'add_service_hero_bg_meta_box');

// Callback функция для мета-бокса фонового изображения услуг
function service_hero_bg_meta_box_callback($post)
{
  wp_nonce_field('service_hero_bg_meta_box', 'service_hero_bg_meta_box_nonce');

  $hero_bg_id = get_post_meta($post->ID, 'service_hero_bg', true);
  $hero_bg_url = '';

  if ($hero_bg_id) {
    $hero_bg_url = wp_get_attachment_image_src($hero_bg_id, 'large')[0];
  }
  ?>

  <div id="service-hero-bg-container">
    <div id="service-hero-bg-preview" style="margin-bottom: 15px;">
      <?php if ($hero_bg_url): ?>
        <img src="<?php echo $hero_bg_url; ?>"
          style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 4px;">
      <?php else: ?>
        <div
          style="width: 100%; height: 80px; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
          <span style="color: #666;">Фон не выбран</span>
        </div>
      <?php endif; ?>
    </div>

    <button type="button" id="select-service-hero-bg" class="button">Выбрать фоновое изображение</button>
    <button type="button" id="remove-service-hero-bg" class="button"
      style="<?php echo $hero_bg_id ? '' : 'display: none;'; ?>">Удалить фон</button>
    <input type="hidden" id="service-hero-bg-id" name="service_hero_bg" value="<?php echo $hero_bg_id; ?>">
  </div>

  <p class="description" style="margin-top: 10px;">
    Рекомендуемый размер: 1920x600px. Если фон не выбран, будет использоваться стандартный фон из CSS.
  </p>

  <script type="text/javascript">
    jQuery(document).ready(function ($) {
      var mediaUploader;

      $('#select-service-hero-bg').on('click', function (e) {
        e.preventDefault();

        if (mediaUploader) {
          mediaUploader.open();
          return;
        }

        mediaUploader = wp.media({
          title: 'Выберите фоновое изображение',
          button: {
            text: 'Использовать как фон'
          },
          multiple: false
        });

        mediaUploader.on('select', function () {
          var attachment = mediaUploader.state().get('selection').first().toJSON();

          $('#service-hero-bg-id').val(attachment.id);
          $('#service-hero-bg-preview').html('<img src="' + attachment.sizes.medium.url + '" style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 4px;">');
          $('#remove-service-hero-bg').show();
        });

        mediaUploader.open();
      });

      $('#remove-service-hero-bg').on('click', function () {
        $('#service-hero-bg-id').val('');
        $('#service-hero-bg-preview').html('<div style="width: 100%; height: 80px; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; border-radius: 4px;"><span style="color: #666;">Фон не выбран</span></div>');
        $(this).hide();
      });
    });
  </script>
  <?php
}

// Сохранение данных мета-бокса фонового изображения для услуг
function save_service_hero_bg_meta_box($post_id)
{
  if (!isset($_POST['service_hero_bg_meta_box_nonce'])) {
    return;
  }

  if (!wp_verify_nonce($_POST['service_hero_bg_meta_box_nonce'], 'service_hero_bg_meta_box')) {
    return;
  }

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }

  if (isset($_POST['post_type']) && 'services' == $_POST['post_type']) {
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }
  }

  if (isset($_POST['service_hero_bg'])) {
    $hero_bg_id = intval($_POST['service_hero_bg']);
    if ($hero_bg_id) {
      update_post_meta($post_id, 'service_hero_bg', $hero_bg_id);
    } else {
      delete_post_meta($post_id, 'service_hero_bg');
    }
  }
}
add_action('save_post', 'save_service_hero_bg_meta_box');

// Функция для получения краткого описания услуги из стандартного excerpt
function get_service_excerpt($post_id, $length = 150)
{
  $post = get_post($post_id);

  // Используем стандартный excerpt WordPress
  if (!empty($post->post_excerpt)) {
    return $post->post_excerpt;
  }

  // Если excerpt пустой, автоматически обрезаем контент
  $content = strip_tags($post->post_content);
  $content = wp_trim_words($content, 25, '...');

  return $content;
}

// Обновляем фильтр render_block для работы с услугами
add_filter('render_block', 'wrap_standard_blocks_with_container_services', 10, 2);

function wrap_standard_blocks_with_container_services($block_content, $block)
{
  // Пропускаем если мы не на странице услуги
  if (!is_singular('services')) {
    return $block_content;
  }

  // Пропускаем пустые блоки
  if (empty(trim($block_content))) {
    return $block_content;
  }

  // Автоматически определяем все ACF блоки (начинающиеся с 'acf/')
  if (isset($block['blockName']) && strpos($block['blockName'], 'acf/') === 0) {
    return $block_content;
  }

  // Для всех остальных блоков добавляем специальный класс
  if (isset($block['blockName']) && !empty($block['blockName'])) {
    return '<div class="standard-block-wrapper-service">' . $block_content . '</div>';
  }

  return $block_content;
}

// Функция для вывода контента услуг с группировкой стандартных блоков
function render_service_content($content)
{
  // Применяем все фильтры WordPress включая наш
  $processed_content = apply_filters('the_content', $content);

  // Простая замена: группируем блоки с классом standard-block-wrapper-service
  $pattern = '/(<div class="standard-block-wrapper-service">.*?<\/div>)/s';
  $parts = preg_split($pattern, $processed_content, -1, PREG_SPLIT_DELIM_CAPTURE);

  $current_standard_group = '';

  foreach ($parts as $part) {
    if (empty(trim($part)))
      continue;

    if (strpos($part, 'standard-block-wrapper-service') !== false) {
      // Накапливаем стандартные блоки
      $clean_content = preg_replace('/<div class="standard-block-wrapper-service">(.*?)<\/div>/s', '$1', $part);
      $current_standard_group .= $clean_content;
    } else {
      // Если накопились стандартные блоки, выводим их в контейнере
      if (!empty(trim($current_standard_group))) {
        ?>
        <section class="section single-service-content">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-12 col-lg-8">
                <div class="service-content">
                  <?php echo $current_standard_group; ?>
                </div>
              </div>
            </div>
          </div>
        </section>
        <?php
        $current_standard_group = '';
      }

      // Выводим кастомный блок как есть
      echo $part;
    }
  }

  // Если остались стандартные блоки в конце
  if (!empty(trim($current_standard_group))) {
    ?>
    <section class="section single-service-content">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-lg-8">
            <div class="service-content">
              <?php echo $current_standard_group; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php
  }
}
?>