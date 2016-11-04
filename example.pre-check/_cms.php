<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $api = new Litera5\API(API_SECRET_KEY);

    $token = tempnam(sys_get_temp_dir(), 'litera5-api-');

    $req = Litera5\CheckRequest::start(COMPANY, API_USER, $token)
        ->name($_POST['title'])
        ->title($_POST['title'])
        ->description($_POST['description'])
        ->custom('sample', $_POST['custom-sample'])
        ->html($_POST['html']);

    $resp = $api->check(
        $req
    );

    if ($resp->is_success()) {
        if ($resp->has_valid_signature()) {
            $iframe_url = Litera5\url($resp->url);
            file_put_contents($token, json_encode(array(
                'checking' => true
            )));
            include '_cms_iframe.php';
        } else {
            echo "Ошибка проверки подписи при выполнении запроса CheckRequest.";
            throw new Exception("Ошибка проверки подписи при выполнении запроса CheckRequest.");
        }
    } else {
        echo "Ошибка запроса CheckRequest<br/>" . $req->json() . "<br/>error_code: " . $resp->error_code . "<br/>" . "error_message: " . $resp->error_message . "<br/>";
    }

} else {
    include '_cms_editor.php';
}