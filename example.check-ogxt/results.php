<?php

/**
 * Получение результатов проверки
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);

$check = $_POST["check"];

$resp = $api->checkOgxtResults(
    Litera5\CheckOgxtResultsRequest::start(COMPANY, $check)
);

header("Content-Type: text/plain");

if ($resp->is_success()) {
    if ($resp->has_valid_signature()) {
        // Идентификатор документа
        print "Статус проверки: " . $resp->state . "\n";
        // Идентификатор проверки, нужен для того, чтобы получать прогресс и результаты проверки
        print "Прогресс проверки в процентах: " . $resp->progress . "\n";
        print "Сообщение прогресса: " . $resp->message . "\n\n\n";

        if (Litera5\CheckState::CHECKED_SUCCESS == $resp->state) {
            print "Список аннотаций, которые можно отображать пользователю: ";
            print_r($resp->annotations);
            print "\n\n";
            print "Статистический отчёт: ";
            print_r($resp->stats);
        }

        exit;
    } else {
        print "Ошибка проверки подписи.";
        print_r($resp);
    }
} else {
    print "Ошибка запроса<br>";
    print "error_code: " . $resp->error_code . "<br/>";
    print "error_message: " . $resp->error_message . "<br/>";
}
