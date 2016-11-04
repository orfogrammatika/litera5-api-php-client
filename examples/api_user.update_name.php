<?php

/**
 * Работа с учётными записями пользователей
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);

/**
 * Изменение имени существующего пользователя
 */
$resp = $api->user(
    Litera5\UserRequest::start(COMPANY, "withpassword")
        ->name("Васисуалий Петрович")
);

if ($resp->is_success()) {
    if ($resp->has_valid_signature()) {
        print "Пользователь изменён\n";
    } else {
        print "Ошибка проверки подписи.\n";
    }
} else {
    print "Ошибка запроса\n";
    print "error_code: " . $resp->error_code . "\n";
    print "error_message: " . $resp->error_message . "\n";
}
