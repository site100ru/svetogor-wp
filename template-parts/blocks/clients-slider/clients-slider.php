<?php
/**
 * Шаблон блока "Слайдер клиентов"
 */

// Получаем данные из полей блока ACF
$clients_title = get_field('clients_title') ?: 'Наши клиенты';
$clients_background = get_field('clients_background') ?: 'bg-grey';
$prev_arrow = get_field('carousel_prev_arrow', 'option');
$next_arrow = get_field('carousel_next_arrow', 'option');

// Получаем список клиентов из настроек сайта
$clients_list = get_field('clients_list', 'option');

// Если нет клиентов, не показываем блок
if (empty($clients_list)) {
  if (current_user_can('edit_posts')) {
    echo '<div style="padding: 20px; border: 2px dashed #ccc; text-align: center; color: #666;">
                <p><strong>Блок "Клиенты":</strong> Добавьте клиентов в <a href="' . admin_url('admin.php?page=acf-options-site-icons') . '">настройках сайта</a></p>
              </div>';
  }
  return;
}

// Генерируем уникальный ID для слайдера
$slider_id = 'clients-glide-' . uniqid();

// Определяем классы для фона
$bg_class = ($clients_background === 'bg-grey') ? 'bg-grey' : '';
?>

<section class="section section-glide section-clients <?php echo esc_attr($bg_class); ?>">
  <div class="container">
    <div class="section-title text-center">
      <h2><?php echo esc_html($clients_title); ?></h2>
      <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ico/points.svg" alt="Точки" class="img-fluid" />
    </div>

    <div class="glide" id="<?php echo esc_attr($slider_id); ?>">
      <div class="glide__track" data-glide-el="track">
        <ul class="glide__slides">
          <?php foreach ($clients_list as $client):
            $client_name = $client['client_name'];
            $client_logo = $client['client_logo'];
            $client_website = $client['client_website'];

            // Пропускаем если нет логотипа
            if (!$client_logo)
              continue;
            ?>
            <li class="glide__slide text-center">
              <?php if ($client_website): ?>
                <a href="<?php echo esc_url($client_website); ?>" target="_blank" rel="noopener noreferrer"
                  class="client-link">

                  <img src="<?php echo esc_url($client_logo['url']); ?>" class="img-fluid client-logo mx-auto"
                    alt="<?php echo esc_attr($client_name); ?>" loading="lazy" />
                </a>
              <?php else: ?>
                <img src="<?php echo esc_url($client_logo['url']); ?>" class="img-fluid client-logo  mx-auto"
                  alt="<?php echo esc_attr($client_name); ?>" loading="lazy" />
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <?php if (count($clients_list) > 1): ?>
        <div class="glide__arrows" data-glide-el="controls">
          <button class="glide__arrow glide__arrow--left btn-carousel-left" data-glide-dir="&lt;" data-glide-el="controls"
            aria-label="Предыдущий слайд">
            <img
              src="<?php echo esc_url(isset($prev_arrow['url']) ? $prev_arrow['url'] : get_template_directory_uri() . '/assets/img/ico/arrow-left.svg'); ?>"
              alt="Назад" loading="lazy" />
          </button>
          <button class="glide__arrow glide__arrow--right btn-carousel-right" data-glide-dir="&gt;" data-glide-el="controls"
            aria-label="Следующий слайд">
            <img
              src="<?php echo esc_url(isset($next_arrow['url']) ? $next_arrow['url'] : get_template_directory_uri() . '/assets/img/ico/arrow-right.svg'); ?>"
              alt="Вперед" loading="lazy" />
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Инициализируем Glide слайдер для клиентов
    if (typeof Glide !== 'undefined') {
      const clientsSlider = new Glide('#<?php echo esc_js($slider_id); ?>', {
        type: 'carousel',
        perView: 6,
        gap: 24,
        breakpoints: {
          1400: {
            perView: 5,
          },
          1200: {
            perView: 4,
          },
          992: {
            perView: 3,
          },
          768: {
            perView: 2,
          },
          590: {
            perView: 1,
          },
        }
      });

      clientsSlider.mount();
    }
  });
</script>