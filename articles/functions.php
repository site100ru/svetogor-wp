<?php
/**
 * Функции для системы статей (стандартные записи WordPress)
 */

// Переименовываем стандартные "Записи" в "Статьи" и настраиваем их
function rename_posts_to_articles()
{
  global $wp_post_types;

  // Изменяем лейблы
  $labels = &$wp_post_types['post']->labels;
  $labels->name = 'Статьи';
  $labels->singular_name = 'Статья';
  $labels->add_new = 'Добавить новую';
  $labels->add_new_item = 'Добавить новую статью';
  $labels->edit_item = 'Редактировать статью';
  $labels->new_item = 'Новая статья';
  $labels->view_item = 'Посмотреть статью';
  $labels->search_items = 'Поиск статей';
  $labels->not_found = 'Статьи не найдены';
  $labels->not_found_in_trash = 'Статьи не найдены в корзине';
  $labels->all_items = 'Все статьи';
  $labels->menu_name = 'Статьи';
  $labels->name_admin_bar = 'Статья';

  // Изменяем иконку
  $wp_post_types['post']->menu_icon = 'dashicons-edit-page';
}
add_action('init', 'rename_posts_to_articles');

// Убираем рубрики (categories) и метки (tags) для статей
function remove_categories_and_tags_from_posts()
{
  // Убираем поддержку рубрик и меток
  unregister_taxonomy_for_object_type('category', 'post');
  unregister_taxonomy_for_object_type('post_tag', 'post');
}
add_action('init', 'remove_categories_and_tags_from_posts');

// Убираем мета-боксы рубрик и меток из админки
function remove_categories_tags_meta_boxes()
{
  remove_meta_box('categorydiv', 'post', 'side');
  remove_meta_box('tagsdiv-post_tag', 'post', 'side');
}
add_action('admin_menu', 'remove_categories_tags_meta_boxes');


// Функция для подключения шаблонов статей из папки articles
function load_articles_templates($template)
{
  // Для архивной страницы статей (главной страницы блога)
  if (is_home() || is_category() || is_tag() || is_author() || is_date()) {
    $articles_archive = get_template_directory() . '/articles/archive-articles.php';
    if (file_exists($articles_archive)) {
      return $articles_archive;
    }
  }

  // Для отдельной страницы статьи
  if (is_singular('post')) {
    $articles_single = get_template_directory() . '/articles/single-article.php';
    if (file_exists($articles_single)) {
      return $articles_single;
    }
  }

  return $template;
}
add_filter('template_include', 'load_articles_templates');

// Добавление мета-бокса для фонового изображения hero-секции для статей
function add_article_hero_bg_meta_box()
{
  add_meta_box(
    'article_hero_bg',
    'Фоновое изображение для заголовочной секции',
    'article_hero_bg_meta_box_callback',
    'post',
    'side',
    'default'
  );
}
add_action('add_meta_boxes', 'add_article_hero_bg_meta_box');

// Callback функция для мета-бокса фонового изображения статей
function article_hero_bg_meta_box_callback($post)
{
  wp_nonce_field('article_hero_bg_meta_box', 'article_hero_bg_meta_box_nonce');

  $hero_bg_id = get_post_meta($post->ID, 'article_hero_bg', true);
  $hero_bg_url = '';

  if ($hero_bg_id) {
    $hero_bg_url = wp_get_attachment_image_src($hero_bg_id, 'large')[0];
  }
  ?>

  <div id="article-hero-bg-container">
    <div id="article-hero-bg-preview" style="margin-bottom: 15px;">
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

    <button type="button" id="select-article-hero-bg" class="button">Выбрать фоновое изображение</button>
    <button type="button" id="remove-article-hero-bg" class="button"
      style="<?php echo $hero_bg_id ? '' : 'display: none;'; ?>">Удалить фон</button>
    <input type="hidden" id="article-hero-bg-id" name="article_hero_bg" value="<?php echo $hero_bg_id; ?>">
  </div>

  <p class="description" style="margin-top: 10px;">
    Рекомендуемый размер: 1920x600px. Если фон не выбран, будет использоваться стандартный фон из CSS.
  </p>

  <script type="text/javascript">
    jQuery(document).ready(function ($) {
      var mediaUploader;

      $('#select-article-hero-bg').on('click', function (e) {
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

          $('#article-hero-bg-id').val(attachment.id);
          $('#article-hero-bg-preview').html('<img src="' + attachment.sizes.medium.url + '" style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 4px;">');
          $('#remove-article-hero-bg').show();
        });

        mediaUploader.open();
      });

      $('#remove-article-hero-bg').on('click', function () {
        $('#article-hero-bg-id').val('');
        $('#article-hero-bg-preview').html('<div style="width: 100%; height: 80px; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; border-radius: 4px;"><span style="color: #666;">Фон не выбран</span></div>');
        $(this).hide();
      });
    });
  </script>
  <?php
}

// Сохранение данных мета-бокса фонового изображения для статей
function save_article_hero_bg_meta_box($post_id)
{
  if (!isset($_POST['article_hero_bg_meta_box_nonce'])) {
    return;
  }

  if (!wp_verify_nonce($_POST['article_hero_bg_meta_box_nonce'], 'article_hero_bg_meta_box')) {
    return;
  }

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }

  if (isset($_POST['post_type']) && 'post' == $_POST['post_type']) {
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }
  }

  if (isset($_POST['article_hero_bg'])) {
    $hero_bg_id = intval($_POST['article_hero_bg']);
    if ($hero_bg_id) {
      update_post_meta($post_id, 'article_hero_bg', $hero_bg_id);
    } else {
      delete_post_meta($post_id, 'article_hero_bg');
    }
  }
}
add_action('save_post', 'save_article_hero_bg_meta_box');

// Функция для получения краткого описания статьи из стандартного excerpt
function get_article_excerpt($post_id, $length = 150)
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

// Обновляем фильтр render_block для работы со статьями
add_filter('render_block', 'wrap_standard_blocks_with_container_articles', 10, 2);

function wrap_standard_blocks_with_container_articles($block_content, $block)
{
  // Пропускаем если мы не на странице статьи
  if (!is_singular('post')) {
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
    return '<div class="standard-block-wrapper">' . $block_content . '</div>';
  }

  return $block_content;
}

// Функция для вывода контента статей с группировкой стандартных блоков
function render_article_content($content)
{
  // Применяем все фильтры WordPress включая наш
  $processed_content = apply_filters('the_content', $content);

  // Простая замена: группируем блоки с классом standard-block-wrapper
  $pattern = '/(<div class="standard-block-wrapper">.*?<\/div>)/s';
  $parts = preg_split($pattern, $processed_content, -1, PREG_SPLIT_DELIM_CAPTURE);

  $current_standard_group = '';

  foreach ($parts as $part) {
    if (empty(trim($part)))
      continue;

    if (strpos($part, 'standard-block-wrapper') !== false) {
      // Накапливаем стандартные блоки
      $clean_content = preg_replace('/<div class="standard-block-wrapper">(.*?)<\/div>/s', '$1', $part);
      $current_standard_group .= $clean_content;
    } else {
      // Если накопились стандартные блоки, выводим их в контейнере
      if (!empty(trim($current_standard_group))) {
        ?>
        <section class="section single-article-content">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-12 col-lg-8">
                <div class="article-content">
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
    <section class="section single-article-content">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-lg-8">
            <div class="article-content">
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