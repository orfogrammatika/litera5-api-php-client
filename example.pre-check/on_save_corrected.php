<?php

require_once '_litera5_api_config.php';

$cms = new Litera5\CMS(API_SECRET_KEY);

$request = $cms->extractSaveCorrectedRequest();

function cms_save_document_by_token($token, $title, $description, $custom, $html, $stats) {
    $results = array(
        'title' => $title,
        'description' => $description,
        'custom' => $custom,
        'html' => $html,
        'stats' => $stats
    );
    file_put_contents($token, json_encode($results));
}

function cms_make_state_recovery_url_by_token($token) {
    return CMS_OUT_URL . '?action=SAVED&token=' . urlencode($token);
}

if ($request->has_valid_signature()) {

    // Используя $request->token записать в базу данных CMS откорректированный текст заметки $request->html
    cms_save_document_by_token($request->token, $request->title, $request->description, $request->custom, $request->html, $request->stats);

    // Сформировать URL для возврата из iFrame и восстановления состояния пользователя в CMS
    $url = cms_make_state_recovery_url_by_token($request->token);

    // Отправить ответа в Литеру
    $cms->respondSaveCorrectedResponseJSON($url);
} else {
    http_response_code(401);
    echo "Подпись не соответствует запросу";
}
