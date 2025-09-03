<?php
get_header();

// Получаем данные текущей записи
$portfolio_id = get_the_ID();
$portfolio_title = get_the_title();
$gallery_images = get_post_meta($portfolio_id, 'portfolio_gallery', true);
$featured_image = get_the_post_thumbnail_url($portfolio_id, 'large');

// Если галерея пустая, используем главное изображение
if (empty($gallery_images) && $featured_image) {
  $attachment_id = get_post_thumbnail_id($portfolio_id);
  if ($attachment_id) {
    $gallery_images = array($attachment_id);
  }
}

// Получаем соседние записи для навигации
$prev_post = get_previous_post();
$next_post = get_next_post();
?>

<section class="section product-section">
  <div class="container">
    <!-- Заголовок работы -->
    <div class="row justify-content-center mt-4">
      <div class="col-12 col-md-8 text-center">
        <h1 class="portfolio-title"><?php echo $portfolio_title; ?></h1>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-md-8 mb-4 mb-lg-0 section-image">

        <?php if (!empty($gallery_images) && is_array($gallery_images)): ?>

          <!-- Основная карусель -->
          <div id="carousel-<?php echo $portfolio_id; ?>" class="carousel slide" data-bs-ride="false"
            data-bs-interval="false">
            <div class="carousel-inner rounded">

              <?php foreach ($gallery_images as $index => $image_id):
                $image_url = wp_get_attachment_image_src($image_id, 'large');
                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                $active_class = ($index === 0) ? 'active' : '';

                if ($image_url): ?>

                  <div
                    class="carousel-item gallery-product-wrapper  gallery-<?php echo $portfolio_id; ?>-wrapper <?php echo $active_class; ?>">
                    <button class="gallery-product gallery-<?php echo $portfolio_id; ?> bg-transparent">
                      <div class="single-product-img approximation img-wrapper position-relative">
                        <img src="<?php echo $image_url[0]; ?>" class="d-block w-100 h-100" loading="lazy"
                          alt="<?php echo esc_attr($image_alt ?: $portfolio_title); ?>">
                      </div>
                    </button>
                  </div>

                <?php endif; ?>
              <?php endforeach; ?>

            </div>

            <!-- Кнопки навигации карусели (только если больше одного изображения) -->
            <?php if (count($gallery_images) > 1): ?>
              <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $portfolio_id; ?>"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $portfolio_id; ?>"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            <?php endif; ?>
          </div>

          <!-- Превьюшки изображений (только если больше одного изображения) -->
          <?php if (count($gallery_images) > 1): ?>
            <div class="row mt-3 product-section-preview">
              <?php foreach ($gallery_images as $index => $image_id):
                $thumb_url = wp_get_attachment_image_src($image_id, 'thumbnail');
                $thumb_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                $active_class = ($index === 0) ? 'active' : '';

                if ($thumb_url): ?>

                  <div class="col">
                    <img src="<?php echo $thumb_url[0]; ?>"
                      class="img-fluid rounded cursor-pointer preview-image shadow-box w-100 <?php echo $active_class; ?>"
                      onclick="changeSlide(<?php echo $index; ?>)"
                      alt="<?php echo esc_attr($thumb_alt ?: 'Превью ' . ($index + 1)); ?>">
                  </div>

                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        <?php else: ?>

          <!-- Если нет изображений в галерее -->
          <div class="text-center py-5">
            <p class="text-muted">Изображения для данной работы не загружены.</p>
          </div>

        <?php endif; ?>

      </div>
    </div>



    <!-- Навигация между работами -->
    <div class="row justify-content-center mt-5">
      <div class="col-12 col-md-8">
        <div class="portfolio-navigation d-flex justify-content-between align-items-center">

          <!-- Кнопка "Предыдущая работа" -->
          <?php if ($prev_post): ?>
            <a href="<?php echo get_permalink($prev_post->ID); ?>" class="btn d-flex align-items-center">
              <span>←</span>
              <span class="d-sm-none">Назад</span>
            </a>
          <?php else: ?>
            <div></div>
          <?php endif; ?>

          <!-- Кнопка "Все работы" -->
          <a href="<?php echo get_post_type_archive_link('portfolio'); ?>" class="btn">
            <span class="d-none d-sm-inline">Все работы</span>
            <span class="d-sm-none">Все</span>
          </a>

          <!-- Кнопка "Следующая работа" -->
          <?php if ($next_post): ?>
            <a href="<?php echo get_permalink($next_post->ID); ?>" class="btn d-flex align-items-center">
              <span class="d-sm-none">Вперед</span>
              <span>→</span>
            </a>
          <?php else: ?>
            <div></div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>
</section>

<script>
  // Глобальные переменные для лайтбокса
  let currentLightboxIndex = 0;
  const galleryImages = [
    <?php if (!empty($gallery_images)): ?>
                    <?php foreach ($gallery_images as $index => $image_id):
                      $full_image = wp_get_attachment_image_src($image_id, 'full');
                      if ($full_image): ?>
                                                        {
            url: '<?php echo $full_image[0]; ?>',
            alt: '<?php echo esc_js(get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $portfolio_title); ?>'
          }<?php echo ($index < count($gallery_images) - 1) ? ',' : ''; ?>
                                    <?php endif; ?>
                    <?php endforeach; ?>
    <?php endif; ?>
  ];

  // Функция смены слайда по клику на превьюшку
  function changeSlide(slideIndex) {
    $('#carousel-<?php echo $portfolio_id; ?>').carousel(slideIndex);

    // Обновляем активный класс у превьюшек
    document.querySelectorAll('.preview-image').forEach((img, index) => {
      img.classList.toggle('active', index === slideIndex);
    });
  }

  // Функция показа изображения в лайтбоксе
  function showLightboxImage() {
    if (galleryImages[currentLightboxIndex]) {
      const img = document.getElementById('lightboxImage');
      img.src = galleryImages[currentLightboxIndex].url;
      img.alt = galleryImages[currentLightboxIndex].alt;

      // Обновляем счетчик
      const counter = document.getElementById('lightboxCounter');
      if (counter) {
        counter.textContent = `${currentLightboxIndex + 1} / ${galleryImages.length}`;
      }
    }
  }

  // Функции навигации в лайтбоксе
  function lightboxPrev() {
    currentLightboxIndex = (currentLightboxIndex - 1 + galleryImages.length) % galleryImages.length;
    showLightboxImage();
  }

  function lightboxNext() {
    currentLightboxIndex = (currentLightboxIndex + 1) % galleryImages.length;
    showLightboxImage();
  }

  // Обработчик клавиш в лайтбоксе
  function handleLightboxKeydown(event) {
    switch (event.key) {
      case 'Escape':
        galleryOff();
        break;
      case 'ArrowLeft':
        if (galleryImages.length > 1) lightboxPrev();
        break;
      case 'ArrowRight':
        if (galleryImages.length > 1) lightboxNext();
        break;
    }
  }

  // Автоматическое обновление активной превьюшки при смене слайда
  <?php if (count($gallery_images) > 1): ?>
    document.addEventListener('DOMContentLoaded', function () {
      const carousel = document.getElementById('carousel-<?php echo $portfolio_id; ?>');
      if (carousel) {
        carousel.addEventListener('slide.bs.carousel', function (event) {
          const nextIndex = event.to;
          document.querySelectorAll('.preview-image').forEach((img, index) => {
            img.classList.toggle('active', index === nextIndex);
          });
        });
      }
    });
  <?php endif; ?>

  // Закрытие лайтбокса по клику на фон
  document.addEventListener('DOMContentLoaded', function () {
    const lightbox = document.getElementById('portfolioLightbox');
    if (lightbox) {
      lightbox.addEventListener('click', function (event) {
        if (event.target === lightbox) {
          galleryOff();
        }
      });
    }
  });
</script>

<?php get_footer(); ?>