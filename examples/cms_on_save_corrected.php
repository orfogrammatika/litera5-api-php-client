<?php

require_once "_litera5_api_config.php";

$cms = new Litera5\CMS(API_SECRET_KEY);

$request = $cms->extractSaveCorrectedRequest();

function cms_save_document_by_token($token, $html, $request) {
    if ($html == null || $html == '') {
        // Пользователь отказался от своих изменений
    } else {
        // Пользователь хочет сохранить изменения в CMS

        // Доступ к результатам проверки произвольных полей
        $custom_sample = $request->custom["sample"];
        $custom_subtitle = $request->custom["subtitle"];
    }
}

function cms_make_state_recovery_url_by_token($token) {
    return 'http://www.ru';
}

if ($request->has_valid_signature()) {

    // Используя $request->token записать в базу данных CMS откорректированный текст заметки $request->html
    cms_save_document_by_token($request->token, $request->html, $request);

    // Сформировать URL для возврата из iFrame и восстановления состояния пользователя в CMS
    $url = cms_make_state_recovery_url_by_token($request->token);

    // Отправить ответ в Литеру
    $cms->respondSaveCorrectedResponseJSON($url);
} else {
    http_response_code(401);
    echo "Подпись не соответствует запросу";
}
