<?php

/**
 * Диагностика API
 *
 * !!! ВНИМАНИЕ!!! Результаты диагностики содержат ваши секретные настроки API
 * !!! используйте его скрытно и не передавайте третьим лицам, кроме
 * !!! доверенных сотрудников службы поддержки Литера5.
 *
 * !!! Если скрипт выполняется через сервер, а не локально,
 * !!! то обязательно удалите его сразу же после получения
 * !!! данных для диагностики. Не оставляйте в свободном доступе.
 *
 * !!! Прежде чем отправлять результаты диагностики в службу поддержки,
 * !!! пожалуйста, внимательно изучите его содержимое, возможно это уже
 * !!! натолкнёт вас на какие-то полезные мысли. Если у вас на локальной
 * !!! отладочной машине всё работает, а при переносе на боевой сервер
 * !!! что-то идёт "не так", то попробуйте запустить скрипт диагностики
 * !!! там и там, а затем сравнить полученные результаты.
 *
 */

require_once "_litera5_api_config.php";

$api = new Litera5\API(API_SECRET_KEY);

$api->diagnose();
