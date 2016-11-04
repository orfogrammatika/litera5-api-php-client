<?php

/**
 * Работа с учётными записями пользователей
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);

/**
 * Создание нового пользователя
 * Указав требуемый пароль
 */
$resp = $api->user(
    Litera5\UserRequest::start(COMPANY, API_USER)
        ->name("Василий Петрович")
        ->password("derparol")
        ->permissions([])
);

if ($resp->is_success()) {
    if ($resp->has_valid_signature()) {
        print "Пользователь создан\n";
        print "Новый пароль: " . $resp->password . "\n";
    } else {
        print "Ошибка проверки подписи.\n";
    }
} else {
    print "Ошибка запроса\n";
    print "error_code: " . $resp->error_code . "\n";
    print "error_message: " . $resp->error_message . "\n";
}
