<?php
/**
 * Автоматическая транслитерация кириллических символов в латиницу при создании ярлыков
 */
function custom_transliterate_slug($slug, $post_ID = null, $post_status = null, $post_type = null, $post_parent = null, $original_slug = null)
{
  // Если слаг уже задан вручную и содержит только латинские символы, оставляем как есть
  if (!empty($slug) && !preg_match('/[А-Яа-яЁё]/u', $slug)) {
    return $slug;
  }

  // Получаем название поста/термина если слаг пустой
  if (empty($slug)) {
    // Проверяем, является ли это термином (категорией)
    if (isset($_POST['tag-name'])) {
      $slug = $_POST['tag-name'];
    } elseif (isset($_POST['name'])) {
      $slug = $_POST['name'];
    } elseif ($post_ID) {
      $post = get_post($post_ID);
      if ($post) {
        $slug = $post->post_title;
      }
    }
  }

  if (empty($slug)) {
    return $slug;
  }

  // Таблица транслитерации
  $converter = array(
    'а' => 'a',
    'б' => 'b',
    'в' => 'v',
    'г' => 'g',
    'д' => 'd',
    'е' => 'e',
    'ё' => 'e',
    'ж' => 'zh',
    'з' => 'z',
    'и' => 'i',
    'й' => 'y',
    'к' => 'k',
    'л' => 'l',
    'м' => 'm',
    'н' => 'n',
    'о' => 'o',
    'п' => 'p',
    'р' => 'r',
    'с' => 's',
    'т' => 't',
    'у' => 'u',
    'ф' => 'f',
    'х' => 'kh',
    'ц' => 'ts',
    'ч' => 'ch',
    'ш' => 'sh',
    'щ' => 'sch',
    'ъ' => '',
    'ы' => 'y',
    'ь' => '',
    'э' => 'e',
    'ю' => 'yu',
    'я' => 'ya',

    'А' => 'A',
    'Б' => 'B',
    'В' => 'V',
    'Г' => 'G',
    'Д' => 'D',
    'Е' => 'E',
    'Ё' => 'E',
    'Ж' => 'Zh',
    'З' => 'Z',
    'И' => 'I',
    'Й' => 'Y',
    'К' => 'K',
    'Л' => 'L',
    'М' => 'M',
    'Н' => 'N',
    'О' => 'O',
    'П' => 'P',
    'Р' => 'R',
    'С' => 'S',
    'Т' => 'T',
    'У' => 'U',
    'Ф' => 'F',
    'Х' => 'Kh',
    'Ц' => 'Ts',
    'Ч' => 'Ch',
    'Ш' => 'Sh',
    'Щ' => 'Sch',
    'Ъ' => '',
    'Ы' => 'Y',
    'Ь' => '',
    'Э' => 'E',
    'Ю' => 'Yu',
    'Я' => 'Ya',
  );

  // Дополнительная замена для предлогов и союзов
  $words_map = array(
    'и' => 'and',
    'в' => 'in',
    'с' => 'with',
    'по' => 'by',
    'за' => 'for',
    'на' => 'on',
    'под' => 'under',
    'над' => 'above',
  );

  // Преобразуем предлоги и союзы
  foreach ($words_map as $cyr_word => $lat_word) {
    // Слово целиком
    $slug = preg_replace('/\b' . $cyr_word . '\b/u', $lat_word, $slug);
  }

  // Транслитерация оставшихся символов
  $slug = strtr($slug, $converter);

  // WordPress стандартная фильтрация слага
  $slug = sanitize_title_with_dashes($slug, '', 'save');

  return $slug;
}

// Хуки для применения транслитерации
add_filter('sanitize_title', 'custom_transliterate_slug', 9, 1);
add_filter('name_save_pre', 'custom_transliterate_slug', 9, 1);

// Для терминов (категорий, тегов и т.д.)
function custom_transliterate_term_slug($slug, $term)
{
  return custom_transliterate_slug($slug);
}
add_filter('pre_term_slug', 'custom_transliterate_term_slug', 10, 2);