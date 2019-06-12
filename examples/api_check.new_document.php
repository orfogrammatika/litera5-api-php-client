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

function cms_extract_document_name() {
    return "Отличный документ";
}

function cms_extract_document_description() {
    return "Просто замечательный";
}

function cms_extract_html_to_check() {
    return '<h1>Загаловак дакумента для праверки.</h1><p>О тут должон буть текст документа. <img src="http://cs607118.vk.me/v607118554/830f/MbIcpVlpbK4.jpg" data-custom-base-src="/imgs/test.jpg"></p> <img src="../../imgs/test1.jpg"> <a href="http://www.ru">link</a> <a href="/some/path">another</a> <a href="../../some/path">and more</a> <div class="gallery" did="123" data-did="123"></div>';
}

function cms_get_document_check_user() {
    return "withpassword";
}

function cms_save_document_id($document_id) {
    print "Документ создан с идентификатором: " . $document_id . "\n";
}

function cms_save_token($token, $url) {
    print "Токен:" . $token . "\n";
    print "Ссылка для работы с документом:" . $url . "\n";
}

/**
 * Первая проверка и корректировка нового текста
 */

// Генерируем token в зависимости от текущего пользователя и текущего документа,
// чтобы после корректировки вернуть CMS в текущее состояние
$token = cms_generate_proper_token();

// Если CMS поддерживает имена (subjects) документов, то можно использовать их
// для работы в Литере. Если же нет, то можно опустить параметр name, тогда
// название документа будет сформирована из начала текста документа
$name = cms_extract_document_name();

// Если CMS поддерживает краткое описание статьи (description), то его можно передать
// на проверку в Литеру. Если же его проверять не нужно, то этот параметр можно опустить.
$description = cms_extract_document_description();

// Читаем текст для проверки из базы данных или из параметров запроса
$html = cms_extract_html_to_check();

// Читаем из базы данных login пользователя для проверки документа. Например первого автора.
$user = cms_get_document_check_user();

$resp = $api->check(
    Litera5\CheckRequest::start(COMPANY, $user, $token)
        ->name($name)
        ->title($name)
        ->description($description)
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