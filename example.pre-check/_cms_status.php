<?php

$token = $_GET['token'];

if (file_exists($token)) {
    echo file_get_contents($token);
} else {
    echo json_encode(array('error' => 'Файл с состояние проверки не найден: "' . $token . '"'));
}