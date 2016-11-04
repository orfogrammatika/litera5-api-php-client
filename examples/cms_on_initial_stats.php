<?php

require_once "_litera5_api_config.php";

$cms = new Litera5\CMS(API_SECRET_KEY);

function document_required_manual_edit($stats) {
    // Проанализировать результаты проверки и принять решение нуждается ли документ в обработке при помощи инструментов
    // Литеры или нет
    return false;
}

$request = $cms->extractInitialStatsRequest();

if ($request->has_valid_signature()) {
    if (isset($request->error)) {
        // Показать пользователю сообщение об ошибке и удалить iframe с проверкой
    }
    $cancel = false;
    if (isset($request->stats)) {
        if (document_required_manual_edit($request->stats))
            $cancel = true;
    }

    // Отправить ответ в Литеру
    $cms->respondInitialStatsResponseJSON($cancel);
} else {
    http_response_code(401);
    echo "Подпись не соответствует запросу";
}
