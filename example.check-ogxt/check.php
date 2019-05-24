<?php

/**
 * Инициация проверки документа
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);

$html = $_POST["html"];
$ogxt = $_POST["ogxt"];
$user = $_POST["user"];

$resp = $api->checkOgxt(
    Litera5\CheckOgxtRequest::start(COMPANY, $user, $html, $ogxt)
);

header("Content-Type: text/plain");

if ($resp->is_success()) {
    if ($resp->has_valid_signature()) {
        // Идентификатор документа
        print "Идентификатор документа: " . $resp->document . "\n";
        // Идентификатор проверки, нужен для того, чтобы получать прогресс и результаты проверки
        print "Идентификатор проверки: " . $resp->check . "\n\n";
        print "Скопируйте идентификатор проверки в буфер обмена, он нам понадобится, чтобы получить результаты проверки при помощи скрипта results.php";
        exit;
    } else {
        print "Ошибка проверки подписи.";
        print_r($resp);
    }
} else {
    print "Ошибка запроса\n";
    print "error_code: " . $resp->error_code . "\n";
    print "error_message: " . $resp->error_message . "\n";
}

