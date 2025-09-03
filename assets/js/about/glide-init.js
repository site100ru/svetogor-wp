/**
 * Инициализация слайдера (Glide.js)
 *
 * Этот скрипт запускает слайдер на элементе 
 * если такой элемент присутствует в DOM.
 *
 * Используется библиотека Glide.js.
 * 
 * В ШАПКЕ 
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.core.min.css"/>
 * 
 * В ПОДВАЛЕ 
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide"></script>
 * 
 * Слайдер настроен как карусель:
 *  - Показывает 6 элементов по умолчанию
 *  - Промежуток между слайдами — 24px
 *  - На экранах шириной до 992px — 4 элемента
 *  - До 768px — 2 элемента
 *
 * Скрипт выполняется после полной загрузки DOM.
 */

document.addEventListener('DOMContentLoaded', function () {
  const initGlide = function (selector, options) {
    try {
      const element = document.querySelector(selector);
      if (element) {
        const glide = new Glide(selector, options);
        glide.mount();
        console.log(`Слайдер ${selector} успешно инициализирован`);
        return glide;
      } else {
        console.log(`Элемент ${selector} не найден на странице`);
      }
    } catch (error) {
      console.error(`Ошибка при инициализации слайдера ${selector}:`, error);
    }
    return null;
  };

  // Инициализация partners-glide
  initGlide('#partners-glide', {
    type: 'carousel',
    perView: 4,
    gap: 24,
    breakpoints: {
      992: {
        perView: 3,
      },
      768: {
        perView: 1,
      },
    }
  });

  // Инициализация section-works
  initGlide('#section-works', {
    type: 'carousel',
    perView: 3,
    gap: 24,
    breakpoints: {
      992: {
        perView: 2,
      },
      768: {
        perView: 1,
      },
    }
  });

  // Инициализация section-product
  initGlide('#section-product', {
    type: 'carousel',
    perView: 3,
    gap: 24,
    breakpoints: {
      992: {
        perView: 2,
      },
      768: {
        perView: 1,
      },
    }
  });

  // Инициализация clients-glide
  initGlide('#clients-glide', {
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

  // Инициализация slider-light-face
  initGlide('#slider-light-face', {
    type: 'carousel',
    perView: 2,
    gap: 24,
    breakpoints: {
      767: {
        perView: 1,
      },
    }
  });

  // Инициализация slider-contour
  initGlide('#slider-contour', {
    type: 'carousel',
    perView: 2,
    gap: 24,
    breakpoints: {
      767: {
        perView: 1,
      },
    }
  });

  // Инициализация slider-light-face-and-ends
  initGlide('#slider-light-face-and-ends', {
    type: 'carousel',
    perView: 2,
    gap: 24,
    breakpoints: {
      767: {
        perView: 1,
      },
    }
  });
});