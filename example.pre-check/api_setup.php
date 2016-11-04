<?php

/**
 * Настройка API
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);

$resp = $api->setup(
    Litera5\SetupRequest::start(COMPANY)
        ->on_save_corrected(CMS_BASE_URL . "/on_save_corrected.php")
        ->on_iframe_failure(CMS_BASE_URL . "/on_iframe_failure.php")
        ->on_initial_stats(CMS_BASE_URL . "/on_initial_stats.php")
        ->get_stats(true)
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
