<?php
/**
 * ИСПРАВЛЕННЫЕ функции для работы с навигацией в шапке (без конфликтов)
 */

/**
 * Регистрация основного меню (только если еще не зарегистрировано)
 */
if (!function_exists('register_header_menu_custom')) {
    function register_header_menu_custom()
    {
        $locations = get_registered_nav_menus();
        if (!isset($locations['header_menu'])) {
            register_nav_menus(array(
                'header_menu' => __('Основное меню', 'svetogor'),
            ));
        }
    }
    add_action('init', 'register_header_menu_custom');
}

/**
 * Получение категорий WooCommerce для шапки (первый уровень)
 */
if (!function_exists('get_header_woocommerce_categories')) {
    function get_header_woocommerce_categories()
    {
        if (!class_exists('WooCommerce')) {
            return array();
        }

        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => 0, // Только родительские категории
            'meta_query' => array(
                array(
                    'key' => 'show_in_header',
                    'value' => '1',
                    'compare' => '='
                )
            ),
            'orderby' => 'name',
            'order' => 'ASC'
        );

        return get_terms($args);
    }
}

/**
 * Получение подкатегорий для шапки (второй уровень)
 */
if (!function_exists('get_header_subcategories')) {
    function get_header_subcategories($parent_id)
    {
        if (!class_exists('WooCommerce')) {
            return array();
        }

        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => $parent_id,
            'meta_query' => array(
                array(
                    'key' => 'show_in_header',
                    'value' => '1',
                    'compare' => '='
                )
            ),
            'orderby' => 'name',
            'order' => 'ASC'
        );

        return get_terms($args);
    }
}

/**
 * Получение товаров для категории (третий уровень)
 */
if (!function_exists('get_category_products')) {
    function get_category_products($category_id, $limit = 10)
    {
        if (!class_exists('WooCommerce')) {
            return array();
        }

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ),
            ),
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );

        return get_posts($args);
    }
}

/**
 * Получение иконки категории
 */
if (!function_exists('get_category_icon_url')) {
    function get_category_icon_url($term_id)
    {
        $thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);
        if ($thumbnail_id) {
            return wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
        }
        return get_template_directory_uri() . '/assets/img/ico/default-category.svg';
    }
}

/**
 * Главная функция вывода навигации
 */
if (!function_exists('display_header_navigation')) {
    function display_header_navigation()
    {
        // Получаем стандартные пункты меню
        $menu_locations = get_nav_menu_locations();
        $menu_items = array();

        if (isset($menu_locations['header_menu'])) {
            $menu_items = wp_get_nav_menu_items($menu_locations['header_menu']);
        }

        $wc_categories = get_header_woocommerce_categories();

        echo '<div class="navbar-wrapper">';
        echo '<nav class="navbar navbar-expand-lg navbar-light bg-white" id="navbar">';
        echo '<div class="container flex-wrap">';

        // Логотип
        display_navigation_logo();

        // Кнопка мобильного меню
        echo '<button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">';
        echo '<span class="navbar-toggler-icon"></span>';
        echo '</button>';

        // Меню для десктопа
        echo '<div class="collapse navbar-collapse" id="navbarContent">';
        echo '<ul class="navbar-nav mx-md-auto me-xxl-0">';

        display_desktop_menu_items($menu_items, $wc_categories);

        echo '</ul>';
        echo '</div>';
        echo '</div>';
        echo '</nav>';
        echo '</div>';

        // Мобильное меню
        display_mobile_menu($menu_items, $wc_categories);
    }
}

/**
 * Вывод логотипа
 */
if (!function_exists('display_navigation_logo')) {
    function display_navigation_logo()
    {
        $company_logo = get_company_logo_custom();
        if ($company_logo) {
            echo '<a class="navbar-brand mx-lg-auto ms-xxl-0" href="' . esc_url(home_url('/')) . '">';
            echo '<img src="' . esc_url($company_logo['url']) . '" alt="' . esc_attr($company_logo['alt'] ?: get_bloginfo('name')) . '" />';
            echo '</a>';
        }
    }
}

/**
 * Вывод пунктов меню для десктопа
 */
if (!function_exists('display_desktop_menu_items')) {
    function display_desktop_menu_items($menu_items, $wc_categories)
    {
        $menu_counter = 0;

        // Выводим стандартные пункты меню
        if ($menu_items) {
            $parent_items = array_filter($menu_items, function ($item) {
                return $item->menu_item_parent == 0;
            });

            // Находим позицию пункта "Продукция"
            $products_position = -1;
            for ($i = 0; $i < count($parent_items); $i++) {
                $title_lower = strtolower(trim($parent_items[$i]->title));
                if ($title_lower === 'продукция' || $title_lower === 'products') {
                    $products_position = $i;
                    break;
                }
            }

            foreach ($parent_items as $item) {
                $title_lower = strtolower(trim($item->title));
                $is_products = ($title_lower === 'продукция' || $title_lower === 'products');

                // Добавляем разделитель перед пунктом, НО НЕ если предыдущий пункт был "Продукция"
                $previous_was_products = ($menu_counter > 0 && $menu_counter - 1 == $products_position);

                if ($menu_counter > 0 && !$previous_was_products) {
                    echo '<li class="nav-item d-none d-lg-inline align-content-center">';
                    echo '<img class="nav-link" src="' . get_template_directory_uri() . '/assets/img/ico/menu-decoration-point.svg" />';
                    echo '</li>';
                }

                // Проверяем, является ли это пунктом "Продукция"
                if ($is_products) {
                    echo '<li class="nav-item nav-item-hero dropdown">';
                    echo '<a class="nav-link dropdown-toggle" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="true" href="#">';
                    echo esc_html($item->title);
                    echo '</a>';

                    // Мега-меню для продукции
                    display_products_mega_menu($wc_categories);

                    echo '</li>';
                } else {
                    $active_class = (in_array('current-menu-item', $item->classes) ||
                        in_array('current_page_item', $item->classes)) ? ' active' : '';

                    echo '<li class="nav-item nav-item-hero">';
                    echo '<a class="nav-link' . $active_class . '" href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
                    echo '</li>';
                }

                $menu_counter++;
            }
        }
    }
}

/**
 * Вывод мега-меню для продукции
 */
if (!function_exists('display_products_mega_menu')) {
    function display_products_mega_menu($categories)
    {
        if (empty($categories)) {
            return;
        }

        echo '<div class="dropdown-menu mega-menu" aria-labelledby="productsDropdown">';
        echo '<div class="container">';
        echo '<div class="row">';

        // Левая колонка - категории
        echo '<div class="col-lg-3">';
        echo '<div class="category-menu">';
        echo '<nav class="nav flex-column">';

        $first_category = true;
        foreach ($categories as $category) {
            $icon_url = get_category_icon_url($category->term_id);
            $active_class = $first_category ? ' active' : '';

            echo '<a class="nav-link' . $active_class . '" href="' . get_term_link($category) . '" data-target="' . $category->term_id . '">';
            echo '<span class="category-icon">';
            echo '<img src="' . esc_url($icon_url) . '" alt="Иконка ' . esc_attr($category->name) . '" />';
            echo '</span>';
            echo '<span>' . esc_html($category->name) . '</span>';
            echo '<span class="category-arrow"></span>';
            echo '</a>';

            $first_category = false;
        }

        echo '</nav>';
        echo '</div>';
        echo '</div>';

        // Правая колонка - подкатегории
        echo '<div class="col-lg-9">';

        $first_content = true;
        foreach ($categories as $category) {
            $subcategories = get_header_subcategories($category->term_id);
            $active_class = $first_content ? ' active' : '';

            echo '<div class="subcategory-content' . $active_class . '" id="' . $category->term_id . '-content">';
            echo '<div class="row">';

            if (!empty($subcategories)) {
                foreach ($subcategories as $subcategory) {
                    $products = get_category_products($subcategory->term_id, 10);

                    echo '<div class="col-md-3">';

                    // ИСПРАВЛЕНО: Создаем якорную ссылку на второй уровень (категорию-родителя)
                    $parent_link = get_term_link($category);
                    $anchor_link = $parent_link . '#' . $subcategory->slug;

                    echo '<a href="' . esc_url($anchor_link) . '" class="subcategory-title h5">';
                    echo esc_html($subcategory->name);
                    echo '</a>';

                    if (!empty($products)) {
                        echo '<ul class="subcategory-list">';
                        foreach ($products as $product) {
                            echo '<li>';
                            echo '<a href="' . get_permalink($product->ID) . '">' . esc_html($product->post_title) . '</a>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    }

                    echo '</div>';
                }
            } else {
                echo '<div class="col-md-12">';
                echo '<p>Подкатегории будут добавлены позже.</p>';
                echo '</div>';
            }

            echo '</div>';
            echo '</div>';

            $first_content = false;
        }

        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Безопасная функция получения логотипа
 */
if (!function_exists('get_company_logo_custom')) {
    function get_company_logo_custom()
    {
        if (function_exists('get_company_logo')) {
            return get_company_logo();
        }

        if (function_exists('get_field')) {
            $logo = get_field('company_logo', 'option');
            if ($logo) {
                return $logo;
            }
        }

        // Fallback
        return array(
            'url' => get_template_directory_uri() . '/assets/img/logo.svg',
            'alt' => get_bloginfo('name')
        );
    }
}

/**
 * Главная функция для вызова в header.php
 */
if (!function_exists('svetogor_header_navigation')) {
    function svetogor_header_navigation()
    {
        display_header_navigation();
    }
}

/**
 * JavaScript для работы меню (безопасное добавление)
 */
if (!function_exists('add_header_navigation_scripts')) {
    function add_header_navigation_scripts()
    {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Функция для работы с категориями меню на десктопе
                const categoryLinks = document.querySelectorAll('.category-menu .nav-link');
                categoryLinks.forEach((link) => {
                    link.addEventListener('mouseover', function () {
                        // Удаляем активный класс со всех ссылок
                        categoryLinks.forEach((l) => l.classList.remove('active'));
                        // Добавляем активный класс текущей ссылке
                        this.classList.add('active');

                        // Показываем соответствующий контент
                        const target = this.getAttribute('data-target');
                        document.querySelectorAll('.subcategory-content').forEach((content) => {
                            content.classList.remove('active');
                        });

                        const targetContent = document.getElementById(`${target}-content`);
                        if (targetContent) {
                            targetContent.classList.add('active');
                        }
                    });
                });

                // Открытие/закрытие мега-меню при наведении на десктопе
                const productsDropdown = document.getElementById('productsDropdown');
                const megaMenu = document.querySelector('.dropdown-menu.mega-menu');

                if (productsDropdown && megaMenu) {
                    // Функция для проверки размера экрана
                    function isDesktop() {
                        return window.innerWidth >= 992;
                    }

                    // Если это десктоп, используем hover для открытия меню
                    function initDesktopBehavior() {
                        if (isDesktop()) {
                            productsDropdown.addEventListener('mouseover', function () {
                                megaMenu.classList.add('show');
                            });

                            const dropdownParent = document.querySelector('.nav-item.dropdown');
                            if (dropdownParent) {
                                dropdownParent.addEventListener('mouseleave', function () {
                                    megaMenu.classList.remove('show');
                                });
                            }
                        }
                    }

                    initDesktopBehavior();

                    // Обновить поведение при изменении размера окна
                    window.addEventListener('resize', function () {
                        if (!isDesktop()) {
                            megaMenu.classList.remove('show');
                        }
                    });
                }

                // Sticky navbar functionality
                const navbar = document.querySelector('#navbar');
                if (navbar) {
                    const placeholder = document.createElement('div');
                    placeholder.className = 'navbar-placeholder';
                    navbar.parentNode.insertBefore(placeholder, navbar.nextSibling);

                    function handleScroll() {
                        const scrollPosition = window.scrollY;

                        if (scrollPosition > 30) {
                            if (!navbar.classList.contains('navbar-fixed')) {
                                placeholder.style.height = navbar.offsetHeight + 'px';
                                placeholder.classList.add('active');
                                navbar.classList.add('navbar-fixed');
                            }
                        } else {
                            navbar.classList.remove('navbar-fixed');
                            placeholder.classList.remove('active');
                        }
                    }

                    window.addEventListener('scroll', handleScroll);
                    window.addEventListener('resize', function () {
                        if (navbar.classList.contains('navbar-fixed')) {
                            placeholder.style.height = navbar.offsetHeight + 'px';
                        }
                    });

                    handleScroll();
                }
            });
        </script>
        <?php
    }
    add_action('wp_footer', 'add_header_navigation_scripts');
}

/**
 * Вывод мобильного меню
 */
if (!function_exists('display_mobile_menu')) {
    function display_mobile_menu($menu_items, $wc_categories)
    {
        echo '<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">';
        echo '<div class="offcanvas-header">';
        echo '<h5 class="offcanvas-title" id="mobileMenuLabel">Меню</h5>';
        echo '<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>';
        echo '</div>';
        echo '<div class="offcanvas-body position-relative">';

        // Уровень 1: Основное меню
        echo '<div class="mobile-view level-1 active" id="main-menu-view">';
        echo '<ul class="navbar-nav">';

        if ($menu_items) {
            $parent_items = array_filter($menu_items, function ($item) {
                return $item->menu_item_parent == 0;
            });

            foreach ($parent_items as $item) {
                $title_lower = strtolower(trim($item->title));

                if ($title_lower === 'продукция' || $title_lower === 'products') {
                    echo '<li class="nav-item">';
                    echo '<div class="mobile-menu-item" data-view="products-menu-view">';
                    echo '<div class="d-flex align-items-center">';
                    echo '<span>' . esc_html($item->title) . '</span>';
                    echo '</div>';
                    echo '<span class="arrow"></span>';
                    echo '</div>';
                    echo '</li>';
                } else {
                    echo '<li class="nav-item">';
                    echo '<a class="nav-link" href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
                    echo '</li>';
                }
            }
        }

        // Дополнительная информация для мобильного меню
        display_mobile_menu_footer();

        echo '</ul>';
        echo '</div>';

        // Уровень 2: Меню категорий продукции
        if (!empty($wc_categories)) {
            echo '<div class="mobile-view level-2" id="products-menu-view">';
            echo '<h5 class="mobile-view-title">Продукция</h5>';

            foreach ($wc_categories as $category) {
                $icon_url = get_category_icon_url($category->term_id);

                echo '<div class="mobile-menu-item" data-view="' . $category->term_id . '-menu-view">';
                echo '<div class="d-flex align-items-center">';
                echo '<img src="' . esc_url($icon_url) . '" alt="Иконка ' . esc_attr($category->name) . '" />';
                echo '<span>' . esc_html($category->name) . '</span>';
                echo '</div>';
                echo '<span class="arrow"></span>';
                echo '</div>';
            }

            echo '<button class="back-button" data-view="main-menu-view">Назад в меню</button>';
            echo '</div>';

            // Уровень 3: Подкатегории
            foreach ($wc_categories as $category) {
                $subcategories = get_header_subcategories($category->term_id);

                echo '<div class="mobile-view level-3" id="' . $category->term_id . '-menu-view">';
                echo '<a href="' . get_term_link($category) . '" class="mobile-view-title h5">' . esc_html($category->name) . '</a>';

                if (!empty($subcategories)) {
                    foreach ($subcategories as $subcategory) {
                        $products = get_category_products($subcategory->term_id, 10);

                        echo '<div class="mb-4">';
                        echo '<a href="' . get_term_link($subcategory) . '">' . esc_html($subcategory->name) . '</a>';

                        if (!empty($products)) {
                            echo '<ul class="list-unstyled ps-3 mt-2">';
                            foreach ($products as $product) {
                                echo '<li class="mb-2">';
                                echo '<a href="' . get_permalink($product->ID) . '" class="text-decoration-none">' . esc_html($product->post_title) . '</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }

                        echo '</div>';
                    }
                } else {
                    echo '<div class="mb-4">';
                    echo '<p>Подкатегории будут добавлены позже.</p>';
                    echo '</div>';
                }

                echo '<button class="back-button" data-view="products-menu-view">Назад к продукции</button>';
                echo '</div>';
            }
        }

        echo '</div>';
        echo '</div>';

        // JavaScript для мобильного меню
        add_mobile_menu_scripts();
    }
}

/**
 * JavaScript для мобильного меню
 */
if (!function_exists('add_mobile_menu_scripts')) {
    function add_mobile_menu_scripts()
    {
        static $scripts_added = false;

        if ($scripts_added) {
            return;
        }

        $scripts_added = true;

        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Обработка мобильной навигации
                const menuItems = document.querySelectorAll('.mobile-menu-item');
                const backButtons = document.querySelectorAll('.back-button');

                function navigateToView(viewId) {
                    document.querySelectorAll('.mobile-view').forEach((view) => {
                        view.classList.remove('active');
                    });

                    const targetView = document.getElementById(viewId);
                    if (targetView) {
                        targetView.classList.add('active');
                    }
                }

                menuItems.forEach((item) => {
                    item.addEventListener('click', function () {
                        const targetView = this.getAttribute('data-view');
                        navigateToView(targetView);
                    });
                });

                backButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        const targetView = this.getAttribute('data-view');
                        navigateToView(targetView);
                    });
                });

                // Обработка закрытия меню
                const closeButton = document.querySelector('.offcanvas .btn-close');
                if (closeButton) {
                    closeButton.addEventListener('click', function () {
                        document.querySelectorAll('.mobile-view').forEach((view) => {
                            view.classList.remove('active');
                        });
                        const mainMenuView = document.getElementById('main-menu-view');
                        if (mainMenuView) {
                            mainMenuView.classList.add('active');
                        }
                    });
                }

                const offcanvasElement = document.querySelector('#mobileMenu');
                if (offcanvasElement) {
                    offcanvasElement.addEventListener('hidden.bs.offcanvas', function () {
                        document.querySelectorAll('.mobile-view').forEach((view) => {
                            view.classList.remove('active');
                        });
                        const mainMenuView = document.getElementById('main-menu-view');
                        if (mainMenuView) {
                            mainMenuView.classList.add('active');
                        }
                    });
                }
            });
        </script>
        <?php
    }
}

/**
 * Дополнительная информация в мобильном меню
 */
if (!function_exists('display_mobile_menu_footer')) {
    function display_mobile_menu_footer()
    {
        // Попробуем получить данные из существующих функций
        $company_address = function_exists('get_company_address') ? get_company_address() : '';
        $company_phone = function_exists('get_main_phone_data') ? get_main_phone_data() : array();
        $company_email = function_exists('get_company_email') ? get_company_email() : '';

        if ($company_address || $company_phone || $company_email) {
            echo '<li class="nav-item d-lg-none py-2">';

            if ($company_address) {
                echo '<div class="d-flex align-items-center gap-2">';
                echo '<img src="' . get_template_directory_uri() . '/assets/img/ico/location-ico.svg" style="max-height: 14px" />';
                echo '<span style="font-size: 14px">' . esc_html($company_address) . '</span>';
                echo '</div>';
            }

            if ($company_phone && isset($company_phone['phone_number'])) {
                $phone_href = function_exists('format_phone_for_href') ? format_phone_for_href($company_phone['phone_number']) : $company_phone['phone_number'];
                echo '<a class="top-menu-tel nav-link price-text" style="font-size: 18px" href="tel:' . esc_attr($phone_href) . '">';
                echo esc_html($company_phone['phone_number']);
                echo '</a>';
            }

            if ($company_email) {
                echo '<a href="mailto:' . esc_attr($company_email) . '" class="d-flex align-items-center gap-2">';
                echo '<img src="' . get_template_directory_uri() . '/assets/img/ico/email-ico.svg" style="max-height: 16px" />';
                echo '<span style="font-size: 14px">' . esc_html($company_email) . '</span>';
                echo '</a>';
            }

            echo '</li>';
        }

        // Социальные сети
        echo '<li class="nav-item">';
        echo '<a class="ico-button pe-2" href="https://wa.me/" target="_blank">';
        echo '<img src="' . get_template_directory_uri() . '/assets/img/ico/whatsapp.svg" alt="WhatsApp" />';
        echo '</a>';
        echo '<a class="ico-button pe-0" href="https://t.me/+79511014610" target="_blank">';
        echo '<img src="' . get_template_directory_uri() . '/assets/img/ico/telegram.svg" alt="Telegram" />';
        echo '</a>';
        echo '</li>';
    }
}
?>