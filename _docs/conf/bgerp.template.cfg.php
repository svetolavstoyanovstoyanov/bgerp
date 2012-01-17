<?php

/*****************************************************************************
 *                                                                           *
 *      Примерен конфигурационен файл за приложение в Experta Framework      *
 *                                                                           *
 *      След като се попълнят стойностите на константите, този файл          *
 *      трябва да бъде записан в [conf] директорията под име:                *
 *      [име на приложението].cfg.php                                        *
 *                                                                           *
 *****************************************************************************/




/***************************************************
*                                                  *
* Параметри за връзка с базата данни               *
*                                                  *
****************************************************/ 

// Име на базата данни. По подразбиране е същото, като името на приложението
DEFINE('EF_DB_NAME', EF_APP_NAME);

// Потребителско име. По подразбиране е същото, като името на приложението
DEFINE('EF_DB_USER', EF_APP_NAME);

// По-долу трябва да се постави реалната парола за връзка
// с базата данни на потребителят дефиниран в предходния ред
DEFINE('EF_DB_PASS', 'bgerp'); 

// Сървъра за на базата данни
DEFINE('EF_DB_HOST', 'localhost');
 
// Кодировка на забата данни
DEFINE('EF_DB_CHARSET', 'utf8');


/***************************************************
*                                                  *
* Някои от другите възможни константи              *
*                                                  *
****************************************************/ 

// Къде са външните компоненти? По подразбиране са в
// EF_ROOT_PATH/vendors
# DEFINE( 'EF_VENDORS_PATH', 'PATH_TO_FOLDER');

// Къде са частните компоненти?
DEFINE('EF_PRIVATE_PATH', EF_ROOT_PATH . '/private');

// Базова директория, където се намират по-директориите за
// временните файлове. По подразбиране е в
// EF_ROOT_PATH/temp
# DEFINE( 'EF_TEMP_BASE_PATH', 'PATH_TO_FOLDER');

// Базова директория, където се намират по-директориите за
// потребителски файлове. По подразбиране е в
// EF_ROOT_PATH/uploads
# DEFINE( 'EF_UPLOADS_BASE_PATH', 'PATH_TO_FOLDER');

// Език на интерфейса по подразбиране. Ако не се дефинира
// се приема, че езика по подрзбиране е български
# DEFINE('EF_DEFAULT_LANGUAGE', 'en');

// Дали вместо ник, за име на потребителя да се приема
// неговия имейл адрес. По подразбиране се приема, че
// трябва да се изисква отделен ник, въведен от потребителя
# DEFINE('EF_USSERS_EMAIL_AS_NICK', TRUE);

// Твърдо, фиксирано име на мениджъра с контролерните функции. 
// Ако се укаже, цялото проложение може да има само един такъв 
// мениджър функции. Това е удобство за специфични приложения, 
// при които не е добре името на мениджъра да се вижда в URL-то
# DEFINE('EF_CTR_NAME', 'FIXED_CONTROLER');

// Твърдо, фиксирано име на екшън (контролерна функция). 
// Ако се укаже, от URL-то се изпускат екшъните.
# DEFINE('EF_ACT_NAME', 'FIXED_CONTROLER');

// Начало на първия период в счетоводната система
# DEFINE('BGERP_FIRST_PERIOD_START', '2011–03-01');

// Край на първия период в счетоводната система
# DEFINE('BGERP_FIRST_PERIOD_END', '2011–03-31');

// Дефиниране на основна валута в системата
# DEFINE('BGERP_BASE_CURRENCY', 'BGN');

// id на собствената компания в системата
# DEFINE('BGERP_OWN_COMPANY_ID', '1');

// Име на собствената компания (тази за която ще работи bgERP)
# DEFINE('BGERP_OWN_COMPANY_NAME', 'MyCompany');

// Име на собствената компания (тази за която ще работи bgERP)
# DEFINE('BGERP_OWN_COMPANY_COUNTRY', 'България');

// Вербално заглавие на приложението
# DEFINE('EF_APP_TITLE', 'BGERP '.BGERP_OWN_COMPANY_NAME);

// Настройки на е-майл системата
  // Потребител
# DEFINE( 'BGERP_DEFAULT_EMAIL_USER', 'catchall@bgerp.com');

  // Хост
# DEFINE( 'BGERP_DEFAULT_EMAIL_HOST', 'localhost');

  // Парола
# DEFINE( 'BGERP_DEFAULT_EMAIL_PASSWORD', '*****');
  
  // From:
# DEFINE( 'BGERP_DEFAULT_EMAIL_FROM', 'team@bgerp.com');

  // Домейн по подразбиране
# DEFINE( 'BGERP_DEFAULT_EMAIL_DOMAIN', 'bgerp.com');

  // Максимално време за еднократно фетчване на писма
# DEFINE( 'IMAP_MAX_FETCHING_TIME', 30);

  // Максимално време за еднократно фетчване на писма
# DEFINE( 'MAX_ALLOWED_MEMORY', '800M');

 // Базова директория, където се намират приложенията
# DEFINE( 'EF_APP_BASE_PATH', 'PATH_TO_FOLDER');

 // Директорията с конфигурационните файлове
# DEFINE( 'EF_CONF_PATH', EF_ROOT_PATH . '/conf');

 // С какъв тип да се правят записите в кеша?
# DEFINE( 'CAPTCHA_CACHE_TYPE', 'Captcha');

 // Колко да са високи символите?
# DEFINE( 'CAPTCHA_HEIGHT', 28);

 // Дефинира разрешените домейни за използване на услугата
# DEFINE( 'EF_ALLOWED_DOMAINS', 0);

 // 
# DEFINE( "SENDER_EMAIL", "team@extrapack.com");

 // Подразбиращата се кодировка на съобщенията
# DEFINE( 'PML_CHARSET', 'utf-8');

 // Ника на изпращача по подразбиране
# DEFINE( PML_DEF_NICK', 'support');

 // Адреса на изпращача (Return-Path) на съобщението
# DEFINE( 'PML_SENDER', PML_FROM_EMAIL);

 // Какъв да е метода за изпращане на писма?
 // ("mail", "sendmail", or "smtp")
# DEFINE( 'PML_MAILER','sendmail');

 // Къде се намира Sendmail?
# DEFINE( 'SENDMAIL_PATH','/usr/sbin/sendmail');

 // Дефинираме пътя до кода на PHP_Mailer
# DEFINE( 'PML_CLASS', '5.2/class.phpmailer.php');

 // Да изпраща ли по единично писмата от адесите в 'To:'
# DEFINE( 'PML_SINGLE_TO', FALSE);



