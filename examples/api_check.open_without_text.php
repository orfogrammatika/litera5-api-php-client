<?php

/**
 * Проверка документов
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);


function cms_generate_proper_token() {
    $token = array(
        "user" => 13,
        "doc" => 5
    );
    return json_encode($token);
}

function cms_get_document_check_user() {
    return "withpassword";
}

function cms_get_document_id() {
    return "54103ac2e4b05b9f8fb0bb1f";
}

function cms_save_document_id($document_id) {
    print "Идентификатор рабочего документа: " . $document_id . "\n";
}

function cms_save_token($token, $url) {
    print "Токен:" . $token . "\n";
    print "Ссылка для работы с документом:" . $url . "\n";
}

/**
 * Открытие последней корректируемой версии документа, без перезаписи текста
 * (может понадобиться на случай, когда Литера не смогла доставить корректуру в CMS)
 */

// Генерируем token в зависимости от текущего пользователя и текущего документа,
// чтобы после корректировки вернуть CMS в текущее состояние
$token = cms_generate_proper_token();

// Читаем из базы данных login пользователя для проверки документа. Например первого автора.
$user = cms_get_document_check_user();

// Читаем из базы данных идентификатор документа в Литере, для повторной проверки
$document = cms_get_document_id();

$resp = $api->check(
    Litera5\CheckRequest::start(COMPANY, $user, $token)
        ->document($document)
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
        print "Ошибка проверки подписи.";
    }
} else {
    print "Ошибка запроса";
    print "error_code: " . $resp->error_code;
    print "error_message: " . $resp->error_message;
}
