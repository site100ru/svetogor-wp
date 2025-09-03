// Глобальный объект для хранения экземпляров каруселей по gridId
const portfolioCarousels = {};

// Функция открытия модального окна с загрузкой галереи через AJAX
function openPortfolioGrid(index, postId, gridId) {
  const modalId = 'portfolioGridModal-' + gridId;
  const containerId = 'dynamic-carousel-container-grid-' + gridId;

  document.getElementById(modalId).style.display = 'block';
  document.getElementById(containerId).innerHTML = '<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 18px;">Загрузка...</div>';

  const ajaxUrl = portfolio_grid_ajax?.ajax_url || '/wp-admin/admin-ajax.php';
  fetch(`${ajaxUrl}?action=get_portfolio_gallery&post_id=${postId}`)
    .then(r => r.json())
    .then(data => {
      if (data.success && data.data.length) {
        createPortfolioGridCarousel(data.data, index, gridId);
      } else {
        document.getElementById(containerId).innerHTML = '<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 18px;">Изображения не найдены</div>';
      }
    })
    .catch(() => {
      document.getElementById(containerId).innerHTML = '<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 18px;">Ошибка загрузки</div>';
    });

  // Регистрация глобального обработчика клавиш
  document.addEventListener('keydown', (e) => handlePortfolioGridKeyPress(e, gridId));
}

// Функция создания карусели
function createPortfolioGridCarousel(images, index, gridId) {
  const container = document.getElementById('dynamic-carousel-container-grid-' + gridId);
  const carouselId = `dynamic-portfolio-grid-carousel-${gridId}-${index}`;

  let html = `
    <div id="${carouselId}" class="carousel slide h-100" data-bs-ride="false" data-bs-interval="false" style="position: fixed; top:0; left:0; width:100%; height:100%;">
      <div class="carousel-inner h-100">
  `;

  images.forEach((img, i) => {
    html += `
      <div class="carousel-item h-100 ${i === 0 ? 'active' : ''}">
        <div class="row h-100 align-items-center">
          <div class="col text-center">
            <img src="${img.url}" class="img-fluid" style="max-width:60vw; max-height:60vh;" loading="lazy" alt="${img.alt||''}" />
            <p style="color:#c8c8c8;">Галерея изображения</p>
          </div>
        </div>
      </div>
    `;
  });

  html += `</div>`;

  if (images.length > 1) {
    // индикаторы
    html += `<div class="carousel-indicators">`;
    images.forEach((_, i) => {
      html += `<button type="button" data-bs-target="#${carouselId}" data-bs-slide-to="${i}" ${i===0?'class="active" aria-current="true"':''} aria-label="Slide ${i+1}"></button>`;
    });
    html += `</div>`;
    // controls
    html += `
      <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    `;
  }

  html += `</div>`;
  container.innerHTML = html;

  if (images.length > 1 && window.bootstrap) {
    const elem = document.getElementById(carouselId);
    // сохраняем экземпляр в глобальном объекте
    portfolioCarousels[gridId] = new bootstrap.Carousel(elem);
  }
}

// Универсальный обработчик клавиш для модального окна
function handlePortfolioGridKeyPress(event, gridId) {
  const modal = document.getElementById('portfolioGridModal-' + gridId);
  if (!modal || modal.style.display !== 'block') return;

  switch (event.key) {
    case 'Escape':
      closePortfolioGridModal(gridId);
      break;
    case 'ArrowRight':
      portfolioCarousels[gridId]?.next();
      break;
    case 'ArrowLeft':
      portfolioCarousels[gridId]?.prev();
      break;
  }
}

// Функция закрытия модального окна
function closePortfolioGridModal(gridId) {
  const modal = document.getElementById('portfolioGridModal-' + gridId);
  const container = document.getElementById('dynamic-carousel-container-grid-' + gridId);
  if (modal) modal.style.display = 'none';
  if (container) container.innerHTML = '';
  delete portfolioCarousels[gridId];
}

// При клике по фону закрываем модалку и очищаем
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.portfolio-grid-modal').forEach(modal => {
    const gridId = modal.id.replace('portfolioGridModal-', '');
    modal.addEventListener('click', e => {
      if (e.target === modal) closePortfolioGridModal(gridId);
    });
  });
});
