<?php

require_once '_litera5_api_config.php';

$cms = new Litera5\CMS(API_SECRET_KEY);

function cms_save_status($token, $error, $stats) {
    $data = array();
    if (isset($error)) {
        $data['error'] = $error;
    }
    if (isset($stats)) {
        $data['success'] = true;
    }
    file_put_contents($token, json_encode($data));
}

$request = $cms->extractInitialStatsRequest();

if ($request->has_valid_signature()) {
    cms_save_status($request->token, $request->error, $request->stats);
    $cms->respondInitialStatsResponseJSON(true);
} else {
    http_response_code(401);
    echo "Подпись не соответствует запросу";
}
