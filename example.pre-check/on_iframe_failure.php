<?php

require_once '_litera5_api_config.php';

$cms = new Litera5\CMS(API_SECRET_KEY);

$request = $cms->extractIFrameFailureRequest();

function cms_log_admin_error($code, $message) {
    print "Ошибка IFrameFailure<br/>" . "error_code: " . $code . "<br/>" . "error_message: " . $message . "<br/>";
}

function cms_get_token_by_url($url) {
    return '';
}

function cms_make_state_recovery_url_by_token($token) {
    return CMS_OUT_URL . '?action=FAILED&token=' . urlencode($token);
}

if ($request->has_valid_signature()) {
    // Записать в лог  $request->code и $request->message и уведомить администратора о проблеме
    cms_log_admin_error($request->code, $request->message);

    // Получить из базы данных token соответствующий ошибочному урлу запроса
    $token = cms_get_token_by_url($request->url);

    // Сформировать URL для возврата из iFrame и восстановления состояния пользователя в CMS
    $url = cms_make_state_recovery_url_by_token($token);

    // Отправить ответа в Литеру
    $cms->respondIFrameFailureResponseJSON($url);
} else {
    http_response_code(401);
    echo "Подпись не соответствует запросу";
}
