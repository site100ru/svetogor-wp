// JavaScript для блока слайдера клиентов

document.addEventListener('DOMContentLoaded', function () {
  // Находим все слайдеры клиентов на странице
  const clientsSliders = document.querySelectorAll('[id^="clients-glide-"]');

  clientsSliders.forEach(function (sliderElement) {
    const sliderId = '#' + sliderElement.id;

    // Инициализируем Glide слайдер для каждого блока клиентов
    if (typeof Glide !== 'undefined') {
      const clientsSlider = new Glide(sliderId, {
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

      try {
        clientsSlider.mount();

        // Добавляем дополнительные события
        clientsSlider.on('mount.after', function () {
          console.log('Clients slider mounted:', sliderId);
        });

        // Пауза на hover
        sliderElement.addEventListener('mouseenter', function () {
          if (clientsSlider.settings.autoplay) {
            clientsSlider.pause();
          }
        });

        sliderElement.addEventListener('mouseleave', function () {
          if (clientsSlider.settings.autoplay) {
            clientsSlider.play();
          }
        });

      } catch (error) {
        console.error('Error mounting clients slider:', error);
      }
    }
  });

  // Добавляем эффекты hover для логотипов клиентов
  const clientLogos = document.querySelectorAll('.client-logo');

  // Добавляем эффекты для ссылок клиентов
  const clientLinks = document.querySelectorAll('.client-link');
  clientLinks.forEach(function (link) {
    link.addEventListener('mouseenter', function () {
      const img = this.querySelector('.client-logo');
      if (img) {
        img.style.opacity = '0.8';
        img.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      }
    });

    link.addEventListener('mouseleave', function () {
      const img = this.querySelector('.client-logo');
      if (img) {
        img.style.opacity = '1';
      }
    });
  });
});