<?php
/**
 * Функции для системы новостей
 */

// Регистрация кастомного типа записей "News"
function create_news_post_type()
{
    $labels = array(
        'name' => 'Новости',
        'singular_name' => 'Новость',
        'menu_name' => 'Новости',
        'name_admin_bar' => 'Новость',
        'archives' => 'Архив новостей',
        'attributes' => 'Атрибуты новости',
        'parent_item_colon' => 'Родительская новость:',
        'all_items' => 'Все новости',
        'add_new_item' => 'Добавить новую новость',
        'add_new' => 'Добавить новую',
        'new_item' => 'Новая новость',
        'edit_item' => 'Редактировать новость',
        'update_item' => 'Обновить новость',
        'view_item' => 'Посмотреть новость',
        'view_items' => 'Посмотреть новости',
        'search_items' => 'Поиск новостей',
        'not_found' => 'Новости не найдены',
        'not_found_in_trash' => 'Новости не найдены в корзине',
        'featured_image' => 'Главное изображение',
        'set_featured_image' => 'Установить главное изображение',
        'remove_featured_image' => 'Удалить главное изображение',
        'use_featured_image' => 'Использовать как главное изображение',
        'insert_into_item' => 'Вставить в новость',
        'uploaded_to_this_item' => 'Загружено для этой новости',
        'items_list' => 'Список новостей',
        'items_list_navigation' => 'Навигация по новостям',
        'filter_items_list' => 'Фильтр новостей',
    );

    $args = array(
        'label' => 'Новость',
        'description' => 'Новости компании',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-admin-post',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => 'news',
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
    );

    register_post_type('news', $args);
}
add_action('init', 'create_news_post_type', 0);

// Обновление rewrite rules при активации темы для новостей
function news_rewrite_flush()
{
    create_news_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'news_rewrite_flush');

// Функция для подключения шаблонов новостей из папки news
function load_news_templates($template)
{
    // Для архивной страницы новостей
    if (is_post_type_archive('news')) {
        $news_archive = get_template_directory() . '/news/archive-news.php';
        if (file_exists($news_archive)) {
            return $news_archive;
        }
    }

    // Для отдельной страницы новости
    if (is_singular('news')) {
        $news_single = get_template_directory() . '/news/single-news.php';
        if (file_exists($news_single)) {
            return $news_single;
        }
    }

    return $template;
}
add_filter('template_include', 'load_news_templates');

// Добавление мета-бокса для фонового изображения hero-секции
function add_news_hero_bg_meta_box()
{
    add_meta_box(
        'news_hero_bg',
        'Фоновое изображение для заголовочной секции',
        'news_hero_bg_meta_box_callback',
        'news',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_news_hero_bg_meta_box');

// Callback функция для мета-бокса фонового изображения
function news_hero_bg_meta_box_callback($post)
{
    wp_nonce_field('news_hero_bg_meta_box', 'news_hero_bg_meta_box_nonce');

    $hero_bg_id = get_post_meta($post->ID, 'news_hero_bg', true);
    $hero_bg_url = '';

    if ($hero_bg_id) {
        $hero_bg_url = wp_get_attachment_image_src($hero_bg_id, 'large')[0];
    }
    ?>

    <div id="news-hero-bg-container">
        <div id="news-hero-bg-preview" style="margin-bottom: 15px;">
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

        <button type="button" id="select-hero-bg" class="button">Выбрать фоновое изображение</button>
        <button type="button" id="remove-hero-bg" class="button"
            style="<?php echo $hero_bg_id ? '' : 'display: none;'; ?>">Удалить фон</button>
        <input type="hidden" id="news-hero-bg-id" name="news_hero_bg" value="<?php echo $hero_bg_id; ?>">
    </div>

    <p class="description" style="margin-top: 10px;">
        Рекомендуемый размер: 1920x600px. Если фон не выбран, будет использоваться стандартный фон из CSS.
    </p>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var mediaUploader;

            $('#select-hero-bg').on('click', function (e) {
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

                    $('#news-hero-bg-id').val(attachment.id);
                    $('#news-hero-bg-preview').html('<img src="' + attachment.sizes.medium.url + '" style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 4px;">');
                    $('#remove-hero-bg').show();
                });

                mediaUploader.open();
            });

            $('#remove-hero-bg').on('click', function () {
                $('#news-hero-bg-id').val('');
                $('#news-hero-bg-preview').html('<div style="width: 100%; height: 80px; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; border-radius: 4px;"><span style="color: #666;">Фон не выбран</span></div>');
                $(this).hide();
            });
        });
    </script>
    <?php
}

// Сохранение данных мета-бокса фонового изображения
function save_news_hero_bg_meta_box($post_id)
{
    if (!isset($_POST['news_hero_bg_meta_box_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['news_hero_bg_meta_box_nonce'], 'news_hero_bg_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['post_type']) && 'news' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    if (isset($_POST['news_hero_bg'])) {
        $hero_bg_id = intval($_POST['news_hero_bg']);
        if ($hero_bg_id) {
            update_post_meta($post_id, 'news_hero_bg', $hero_bg_id);
        } else {
            delete_post_meta($post_id, 'news_hero_bg');
        }
    }
}
add_action('save_post', 'save_news_hero_bg_meta_box');

// Функция для получения краткого описания новости из стандартного excerpt
function get_news_excerpt($post_id, $length = 150)
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

// Добавление колонки с отрывком в админке
function add_news_excerpt_column($columns)
{
    $columns['news_excerpt'] = 'Отрывок';
    return $columns;
}
add_filter('manage_news_posts_columns', 'add_news_excerpt_column');

// Заполнение колонки с отрывком
function fill_news_excerpt_column($column, $post_id)
{
    if ($column === 'news_excerpt') {
        $excerpt = get_news_excerpt($post_id);
        echo !empty($excerpt) ? esc_html(wp_trim_words($excerpt, 15)) : '<em>Не указано</em>';
    }
}
add_action('manage_news_posts_custom_column', 'fill_news_excerpt_column', 10, 2);

// Обновляем фильтр render_block для работы с новостями
add_filter('render_block', 'wrap_standard_blocks_with_container_news', 10, 2);

function wrap_standard_blocks_with_container_news($block_content, $block)
{
    // Пропускаем если мы не на странице новости
    if (!is_singular('news')) {
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

    // Если это любой другой ACF блок - тоже возвращаем как есть
    if (isset($block['blockName']) && strpos($block['blockName'], 'acf/') === 0) {
        return $block_content;
    }

    // Для всех остальных блоков добавляем специальный класс
    if (isset($block['blockName']) && !empty($block['blockName'])) {
        return '<div class="standard-block-wrapper">' . $block_content . '</div>';
    }

    return $block_content;
}

// Функция для вывода контента новостей с группировкой стандартных блоков
function render_news_content($content)
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
                <section class="section single-news-content">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-12 col-lg-8">
                                <div class="news-content">
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
        <section class="section single-news-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">
                        <div class="news-content">
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