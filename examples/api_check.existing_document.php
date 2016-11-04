<?php

/**
 * Проверка документов
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);


/**
 * Повторная проверка документа
 */

// Генерируем token в зависимости от текущего пользователя и текущего документа,
// чтобы после корректировки вернуть CMS в текущее состояние
$token = cms_generate_proper_token();

// Читаем текст для проверки из базы данных или из параметров запроса
$html = cms_extract_html_to_check();

// Читаем из базы данных login пользователя для проверки документа. Например первого автора.
$user = cms_get_document_check_user();

// Читаем из базы данных идентификатор документа в Литере, для повторной проверки
$document = cms_get_document_id();

$resp = $api->check(
    Litera5\CheckRequest::start(COMPANY, $user, $token)
        ->document($document)
        ->html($html)
);

if ($resp->is_success()) {
    if ($resp->has_valid_signature()) {
        // Сохраняем идентификатор документа в Литере на случай будущих повторных проверок в базе данных CMS
        cms_save_document_id($resp->document);

        // Сохраняем токен и урл фрейма редактирования для действий после корректировки или на случай ошибки проверки
        cms_save_token($token, $resp->url);

        // Перенаправляем страницу пользователя (в iframe) на Литеру для корректировки документа
        header('Location: ' . Litera5\iframeUrl($resp->url), true, 302);
        exit;
    } else {
        print "Ошибка проверки подписи.\n";
    }
} else {
    print "Ошибка запроса\n";
    print "error_code: " . $resp->error_code . "\n";
    print "error_message: " . $resp->error_message . "\n";
}