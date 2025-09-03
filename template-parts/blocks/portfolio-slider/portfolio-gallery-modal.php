<?php
/**
 * Общий шаблон модального окна для галереи портфолио
 * template-parts/blocks/portfolio-slider/portfolio-gallery-modal.php
 */

// Определяем ID модального окна и контейнера
if (!isset($modal_id)) {
  $modal_id = 'productGalleryModal'; // Для обратной совместимости
}

// Определяем суффикс для контейнера
if (strpos($modal_id, 'portfolioModal-') === 0) {
  $container_suffix = str_replace('portfolioModal-', '', $modal_id);
  $container_id = 'dynamic-carousel-container-' . $container_suffix;
} else {
  $container_suffix = '';
  $container_id = 'dynamic-carousel-container';
}
?>

<!-- Модальное окно для галереи портфолио -->
<div id="<?php echo esc_attr($modal_id); ?>" class="portfolio-gallery-modal" style="
        background: rgba(0, 0, 0, 0.85);
        display: none;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
    ">
  <!-- Динамический слайдер будет загружаться здесь -->
  <div id="<?php echo esc_attr($container_id); ?>"></div>

  <!-- Кнопка закрытия галереи -->
  <button type="button" onclick="closePortfolioGallery('<?php echo esc_attr($container_suffix); ?>');"
    class="btn-close btn-close-white" style="position: fixed; top: 25px; right: 25px; z-index: 99999"
    aria-label="Close"></button>
</div>

<script>
  // Инициализация обработчиков для модального окна
  document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('<?php echo esc_js($modal_id); ?>');
    const modalSuffix = '<?php echo esc_js($container_suffix); ?>';

    if (modal) {
      // Закрываем модальное окно при клике на фон
      modal.addEventListener('click', function (event) {
        if (event.target === modal) {
          closePortfolioGallery(modalSuffix);
        }
      });
    }
  });
</script>