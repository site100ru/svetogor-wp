<?php
// Подключаем WordPress (нужно для wp_mail)
require_once(realpath(dirname(__FILE__) . '/../../../../wp-load.php'));

// Если существует переменная POST, то
if ($_POST) {
  // Функция проверки reCAPTCHA v3
  function getCaptcha($SecretKey)
  {
    $Response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LdV1IcUAAAAABnQ0mXIp5Yh7tLEcAXzdqG6rx9Y&response={$SecretKey}");
    $Return = json_decode($Response);
    return $Return;
  }

  /* Принимаем данные обратно */
  $Return = getCaptcha($_POST['g-recaptcha-response']);

  // Если reCAPTCHA пройдена успешно (для v3 используем score)
  if ($Return->success == true && $Return->score > 0.5) {

    // Получаем данные из формы с проверкой существования
    $name = isset($_POST['name']) && $_POST['name'] ? sanitize_text_field($_POST['name']) : 'Не указано';
    $tel = isset($_POST['tel']) && $_POST['tel'] ? sanitize_text_field($_POST['tel']) : 'Не указан';

    // Получаем данные о странице с проверкой существования
    $page_url = isset($_POST['page-url']) && $_POST['page-url'] ? esc_url($_POST['page-url']) : 'Не указан';
    $page_title = isset($_POST['page-title']) && $_POST['page-title'] ? sanitize_text_field($_POST['page-title']) : 'Не указан';
    $referrer = isset($_POST['referrer']) && $_POST['referrer'] ? esc_url($_POST['referrer']) : 'Прямой переход';

    // Проверяем обязательные поля
    if ($tel && $tel !== 'Не указан') {
      // Формируем сообщение для email
      $email_message = "=== ПРОСТАЯ ЗАЯВКА С САЙТА ===\n\n";
      $email_message .= "Контактная информация:\n";
      $email_message .= "Имя: " . $name . "\n";
      $email_message .= "Телефон: " . $tel . "\n\n";

      $email_message .= "Информация о странице:\n";
      $email_message .= "URL страницы: " . $page_url . "\n";
      $email_message .= "Заголовок страницы: " . $page_title . "\n";
      $email_message .= "Дата и время: " . date('d.m.Y H:i:s') . "\n";

      // Настройки для wp_mail
      $to = 'sidorov-vv3@mail.ru, vasilyev-r@mail.ru';
      $subject = 'Простая заявка с сайта svetogor.ru';
      $message = $email_message;

      // Заголовки письма
      $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: Сайт svetogor.ru <noreply@svetogor.ru>',
        'Reply-To: ' . $name . ' <noreply@svetogor.ru>'
      );

      // Отправка через WordPress wp_mail
      $emailSent = wp_mail($to, $subject, $message, $headers);

      if ($emailSent) {
        $_SESSION['win'] = 1;
        $_SESSION['recaptcha'] = '<p class="text-success">Спасибо за заявку! Письмо успешно отправлено. Мы свяжемся с Вами в ближайшее время.</p>';


      } else {
        $_SESSION['win'] = 1;
        $_SESSION['recaptcha'] = '<p class="text-warning">Заявка принята, но возникли проблемы с отправкой email. Мы обработаем вашу заявку вручную.</p>';

      }

      header("Location: " . $_SERVER['HTTP_REFERER']);
      exit;

    } else {
      // Если телефон не заполнен
      $_SESSION['win'] = 1;
      $_SESSION['recaptcha'] = '<p class="text-danger">Обязательное поле с номером телефона не заполнено!</p>';
      header("Location: " . $_SERVER['HTTP_REFERER']);
      exit;
    }

  } else {
    // Если reCAPTCHA не пройдена
    $error_details = '';
    if (isset($Return->{'error-codes'})) {
      $error_details = ' Ошибки: ' . implode(', ', $Return->{'error-codes'});
    }
    if (isset($Return->score)) {
      $error_details .= ' Score: ' . $Return->score;
    }

    $_SESSION['win'] = 1;
    $_SESSION['recaptcha'] = '<p class="text-danger"><strong>Извините!</strong><br>Проверка безопасности не пройдена. Попробуйте еще раз.' . $error_details . '</p>';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }
} else {
  // Если нет POST данных, перенаправляем на главную
  header("Location: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'));
  exit;
}
?>