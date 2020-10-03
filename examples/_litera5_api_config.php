<?php

/**
 * Установите в true, чтобы логгировать все запросы и ответы API Литеры
 */
const API_DEBUG_LOG = false;

/**
 * Укажите путь файла и раскомментируйте строчку, если хотите писать логи вызовов API в отдельный файл вместо системных логов
 * Работает только если включено `API_DEBUG_LOG`
 */
//const API_DEBUG_FILE = '/tmp/l5_api_debug.log';

require_once "../litera5_api_v1.php";

/**
 * Секретный ключ полученный при подключении API
 */
const API_SECRET_KEY = "some api key received from Litera5";

/**
 * Идентификатор компании полученный при регистрации в Литере5
 */
const COMPANY = "company";

