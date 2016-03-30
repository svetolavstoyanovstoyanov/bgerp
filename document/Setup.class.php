<?php


/**
  * class forum_Setup
 *
 * Инсталиране/Деинсталиране на
 * мениджъри свързани с Форума
 *
 *
 * @category  bgerp
 * @package   forum
 * @author    Ивелин Димов <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class document_Setup extends core_ProtoSetup
{


	/**
	 * Версия на пакета
	 */
	var $version = '0.1';


	/**
	 * Мениджър - входна точка в пакета
	 */
	var $startCtr = 'document_Products';


	/**
	 * Екшън - входна точка в пакета
	 */
	var $startAct = 'default';


	/**
	 * Описание на модула
	 */
	var $info = "Онлайн Магазин";

	
	/**
     * Описание на конфигурационните константи за този модул
     */
//    var $configDescription = array(
//
//            'FORUM_DEFAULT_THEME' => array ('class(interface=forum_ThemeIntf,select=title)', 'caption=Тема по подразбиране във форум->Тема'),
//
//    		'FORUM_THEMES_PER_PAGE' => array ('int', 'caption=Tемите в една страница->Брой'),
//
//    		'FORUM_GREETING_MESSAGE' => array ('text', 'mandatory, caption=Съобщение за поздрав->Съобщение'),
//
//    		'FORUM_POSTS_PER_PAGE' => array ('int', 'mandatory, caption=Постовете на една страница->Брой'),
//        );
    

    /**
     * Списък с мениджърите, които съдържа пакета
     */
   var $managers = array(
	   	'document_Products',
		'document_Tags',
	   	'document_Orders',
	   	'document_OrderDetails',



   );
    

    /**
     * Роли за достъп до модула
     */
    var $roles = 'estore';
    

    /**
     * Връзки от менюто, сочещи към модула
     */
    var $menuItems = array(
            array(3.54, 'Сайт', 'Estore', 'document_Products', 'list', "cms,forum, admin, ceo"),
        );
    
    
    /**
     * Инсталиране на пакета
     */
//    function install()
//    {
//    	$html = parent::install();
//
//    	// Добавяме класа връщащ темата в core_Classes
//        $html .= core_Classes::add('forum_DefaultTheme');
//
//        return $html;
//    }
    
    
	/**
	 * Де-инсталиране на пакета
	 */
//	function deinstall()
//	{
//		// Изтриване на пакета от менюто
//		$res .= bgerp_Menu::remove($this);
//
//		return $res;
//	}
//
    
    /**
     * Връща JS файлове, които са подходящи за компактиране
     */
//    public function getCommonCss()
//    {
//
//        return 'forum/tpl/styles.css';
//    }
}