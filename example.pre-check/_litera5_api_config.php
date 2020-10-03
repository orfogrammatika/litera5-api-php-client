<?php

require_once "../litera5_api_v1.php";

/**
 * Секретный ключ полученный при подключении API
 */
const API_SECRET_KEY = "some api key received from Litera5";

/**
 * Идентификатор компании полученный при регистрации в Литере5
 */
const COMPANY = "company";

/**
 * Пользователь который будет использоваться для экспериментов
 */
const API_USER = 'api_test';

/**
 * Базовый путь до CMS
 */
const CMS_BASE_URL = 'http://localhost:5555';

/**
 * Путь до страницы выхода из iframe
 */
define('CMS_OUT_URL', CMS_BASE_URL . '/_cms_out.php');