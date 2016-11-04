<?php

/**
 * Настройка API
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);

$resp = $api->setup(
    Litera5\SetupRequest::start(COMPANY)
        ->on_save_corrected("http://cms.company.ru/api/litera5/on_save_corrected.php")
        ->on_iframe_failure("http://cms.company.ru/api/litera5/on_iframe_failure.php")
        ->allow_resize_images(false)
        ->cancel_caption("Cancel and return to CMS")
);

if ($resp->is_success()) {
    if ($resp->has_valid_signature()) {
        print "API настроено\n";
    } else {
        print "Ошибка проверки подписи.\n";
    }
} else {
    print "Ошибка запроса\n";
    print "error_code: " . $resp->error_code . "\n";
    print "error_message: " . $resp->error_message . "\n";
}
