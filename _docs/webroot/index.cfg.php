<?php
// Игнорираме нотисите
DEFINE('CORE_ERROR_REPORTING_LEVEL', E_ALL & ~E_NOTICE & ~E_DEPRECATED);
error_reporting(CORE_ERROR_REPORTING_LEVEL);

// Път за записване на дебъг информация
// DEFINE('DEBUG_FATAL_ERRORS_PATH', '/tmp/bgerp/debug');

// Път за записване на изключенията и грешките
# DEFINE('DEBUG_FATAL_ERRORS_FILE', '/tmp/bgerp/errors');

// Дали да са включени финкциите за дебъг и настройка
DEFINE('EF_DEBUG', "localhost,127.0.0.1,::1");

// Името на папката със статичните ресурсни файлове:
// css, js, png, gif, jpg, flv, swf, java, xml, txt, html ...
// Тази папка се намира в webroot-a, заедно с този файл
// Самите статични файлове могат физически да не са в тази папка
// Ако не се дефинира си остава `sbf`
 # DEFINE('EF_SBF', 'sbf');

// Общата коренна директория на [bgerp], [conf],
// [uploads] и др. Не е задължително всички посочени
// директории да са в тази папка. В основният конфигурационен файл
// може да им се зададат различни пътища
 # DEFINE('EF_ROOT_PATH', '[#PATH_TO_FOLDER#]');

// Конфигурационите файлове. По подразбиране е в
// EF_ROOT_PATH/conf
 # DEFINE( 'EF_CONF_PATH', '[#PATH_TO_FOLDER#]');

// Името на приложението. Допускат се само малки латински букви и цифри
// Ако не е дефинирано, системата се опитва да го открие сама
 # DEFINE('EF_APP_NAME', 'bgerp');

// Името на приложението. Допускат се само малки латински букви и цифри
// Ако не е дефинирано, системата се опитва да го открие сама
 # DEFINE('EF_APP_CODE_NAME', 'bgerp');
