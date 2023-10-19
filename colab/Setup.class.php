<?php


/**
 * Кои документи могат да бъдат създавани от контрактор
 */
defIfNot('COLAB_CREATABLE_DOCUMENTS_LIST', '');


/**
 * Регистриране на нов партньор Роли
 */
defIfNot('COLAB_DEFAULT_ROLES_FOR_NEW_PARTNER', '');


/**
 * Регистриране на нов партньор Роли
 */
defIfNot('COLAB_DEFAULT_ROLES_FOR_NEW_PARTNER', '');


/**
 * Email за регистрация на нов партньор към папка на фирма->BG
 */
defIfNot('COLAB_DEFAULT_EMAIL_PARTNER_REGISTRATION_BG', "Уважаеми потребителю. За да се регистрираш като служител на фирма \"[#company#]\", моля последвай този {{линк}} - изтича след [#lifetime#]");


/**
 * Email за регистрация на нов партньор към папка на фирма->EN
 */
defIfNot('COLAB_DEFAULT_EMAIL_PARTNER_REGISTRATION_EN', "Dear User. To have registration as a member of company \"[#company#]\", please follow this {{link}} - it expires after [#lifetime#]");


/**
 * Email за регистрация на нов партньор към папка на лице->BG
 */
defIfNot('COLAB_DEFAULT_EMAIL_PARTNER_PERSON_REGISTRATION_BG', "Уважаеми потребителю. За да се регистрираш моля последвай този {{линк}} - изтича след [#lifetime#]");


/**
 * Email за регистрация на нов партньор към папка на лице->EN
 */
defIfNot('COLAB_DEFAULT_EMAIL_PARTNER_PERSON_REGISTRATION_EN', "Dear User. For registration please follow this {{link}} - it expires after [#lifetime#]");


/**
 * Email за регистрация за е-шоп->BG
 */
defIfNot('COLAB_DEFAULT_EMAIL_ESHOP_REGISTRATION_BG', "Уважаеми потребителю. За да се регистрираш, като потребител в нашия онлайн магазин моля последвай този {{линк}} - изтича след [#lifetime#]");


/**
 * Email за регистрация за е-шоп->EN
 */
defIfNot('COLAB_DEFAULT_EMAIL_ESHOP_REGISTRATION_EN', "Dear User. To have registration as a user in our e-shop please follow this {{link}} - it expires after [#lifetime#]");


/**
 * Валидност на линка за регистрация
 */
defIfNot('COLAB_PARTNER_REGISTRATION_LINK_LIFETIME', '604800');


/**
 * Валидност на линка за регистрация
 */
defIfNot('COLAB_SHARE_USERS_FROM_FOLDER', 'yes');


/**
 * Клас 'colab_Setup'
 *
 * Исталиране/деинсталиране на colab
 *
 *
 * @category  bgerp
 * @package   colab
 *
 * @author    Ivelin Dimov <ielin_pdimov@abv.com>
 * @copyright 2006 - 2015 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 * @link
 */
class colab_Setup extends core_ProtoSetup
{
    /**
     * Версия на пакета
     */
    public $version = '0.1';
    
    
    /**
     * Описание на модула
     */
    public $info = 'Достъп до системата на партньори';
    
    
    // Инсталиране на мениджърите
    public $managers = array(
        'colab_FolderToPartners',
        'colab_DocumentLog',
    );
    
    
    /**
     * Кои документи могат да бъдат създавани по дефолт от контрактори
     */
    private static $defaultCreatableDocuments = 'sales_Sales,doc_Comments,doc_Notes,marketing_Inquiries2,store_ConsignmentProtocols';


    /**
     * Дефинирани класове, които имат интерфейси
     */
    public $defClasses = 'colab_drivers_FoldersTabBlock,colab_drivers_ProfileTabBlock,colab_drivers_SingleThreadTabBlock,colab_drivers_ThreadTabBlock,colab_drivers_NotificationTabBlock';


    /**
     * Описание на конфигурационните константи
     */
    public $configDescription = array(
        'COLAB_CREATABLE_DOCUMENTS_LIST' => array('keylist(mvc=core_Classes,select=name)', 'caption=Кои документи могат да се създават от партньори->Документи,optionsFunc=colab_Setup::getDocumentOptions'),
        'COLAB_DEFAULT_ROLES_FOR_NEW_PARTNER' => array('keylist(mvc=core_Roles,select=name)', 'caption=Регистриране на нов партньор->Роли,optionsFunc=colab_Setup::getExternalRoles'),
        'COLAB_PARTNER_REGISTRATION_LINK_LIFETIME' => array('time', 'caption=Регистриране на нов партньор->Валидност (линк)'),

        'COLAB_SHARE_USERS_FROM_FOLDER' => array('enum(yes=Да,no=Не)', 'caption=Възможност за споделяне на потребители от колаборатори->Избор'),

        'COLAB_DEFAULT_EMAIL_PARTNER_REGISTRATION_BG' => array('richtext(rows=3)', 'caption=Email за регистрация на нов партньор към папка на фирма->BG'),
        'COLAB_DEFAULT_EMAIL_PARTNER_REGISTRATION_EN' => array('richtext(rows=3)', 'caption=Email за регистрация на нов партньор към папка на фирма->EN'),

        'COLAB_DEFAULT_EMAIL_PARTNER_PERSON_REGISTRATION_BG' => array('richtext(rows=3)', 'caption=Email за регистрация на нов партньор към папка на лице->BG'),
        'COLAB_DEFAULT_EMAIL_PARTNER_PERSON_REGISTRATION_EN' => array('richtext(rows=3)', 'caption=Email за регистрация на нов партньор към папка на лице->EN'),

        'COLAB_DEFAULT_EMAIL_ESHOP_REGISTRATION_BG' => array('richtext(rows=3)', 'caption=Email за регистрация за е-шоп->BG'),
        'COLAB_DEFAULT_EMAIL_ESHOP_REGISTRATION_EN' => array('richtext(rows=3)', 'caption=Email за регистрация за е-шоп->EN'),
        );
    
    
    /**
     * Допустими външни хора за партньори
     */
    public static function getExternalRoles()
    {
        $res = array();
        $roles = core_Roles::getRolesByType('external', null, true);
        foreach ($roles as $id){
            $res[$id] = core_Roles::getVerbal($id, 'role');
        }
        
        return $res;
    }
    
    
    /**
     * Инсталиране на пакета
     */
    public function install()
    {
        $html = parent::install();
        
        // Зареждаме мениджъра на плъгините
        $Plugins = cls::get('core_Plugins');
        
        // Закачане на плъгин за споделяне на папки с партньори към фирмите
        $html .= $Plugins->installPlugin('Споделяне на папки на фирми с партньори', 'colab_plg_FolderToPartners', 'crm_Companies', 'private');
        
        // Закачане на плъгин за споделяне на папки с партньори към лицата
        $html .= $Plugins->installPlugin('Споделяне на папки на лица с партньори', 'colab_plg_FolderToPartners', 'crm_Persons', 'private');
        
        // Закачане към системи
        $html .= $Plugins->installPlugin('Споделяне системи с партньори', 'colab_plg_FolderToPartners', 'support_Systems', 'private');
        
        // Закачане към проекти
        $html .= $Plugins->installPlugin('Споделяне проекти с партньори', 'colab_plg_FolderToPartners', 'doc_UnsortedFolders', 'private');
        
        // Закачане към складове
        $html .= $Plugins->installPlugin('Споделяне складове с партньори', 'colab_plg_FolderToPartners', 'store_Stores', 'private');
        
        // Закачаме плъгина към документи, които са видими за партньори
        $html .= $Plugins->installPlugin('Colab за приходни банкови документи', 'colab_plg_Document', 'bank_IncomeDocuments', 'private');
        $html .= $Plugins->installPlugin('Colab за разходни банкови документи', 'colab_plg_Document', 'bank_SpendingDocuments', 'private');
        $html .= $Plugins->installPlugin('Colab за приходни касови ордери', 'colab_plg_Document', 'cash_Pko', 'private');
        $html .= $Plugins->installPlugin('Colab за разходни касови ордери', 'colab_plg_Document', 'cash_Rko', 'private');
        $html .= $Plugins->installPlugin('Colab за артикули в каталога', 'colab_plg_Document', 'cat_Products', 'private');
        $html .= $Plugins->installPlugin('Colab за декларации за съответствие', 'colab_plg_Document', 'dec_Declarations', 'private');
        $html .= $Plugins->installPlugin('Colab за входящи имейли', 'colab_plg_Document', 'email_Incomings', 'private');
        $html .= $Plugins->installPlugin('Colab за изходящи имейли', 'colab_plg_Document', 'email_Outgoings', 'private');
        $html .= $Plugins->installPlugin('Colab за запитвания', 'colab_plg_Document', 'marketing_Inquiries2', 'private');
        $html .= $Plugins->installPlugin('Colab за ценоразписи', 'colab_plg_Document', 'price_ListDocs', 'private');
        $html .= $Plugins->installPlugin('Colab за фактури за продажби', 'colab_plg_Document', 'sales_Invoices', 'private');
        $html .= $Plugins->installPlugin('Colab за проформа фактури', 'colab_plg_Document', 'sales_Proformas', 'private');
        $html .= $Plugins->installPlugin('Colab за изходящи оферти', 'colab_plg_Document', 'sales_Quotations', 'private');
        $html .= $Plugins->installPlugin('Colab за договори за продажба', 'colab_plg_Document', 'sales_Sales', 'private');
        $html .= $Plugins->installPlugin('Colab за предавателни протоколи', 'colab_plg_Document', 'sales_Services', 'private');
        $html .= $Plugins->installPlugin('Colab за протоколи за отговорно пазене', 'colab_plg_Document', 'store_ConsignmentProtocols', 'private');
        $html .= $Plugins->installPlugin('Colab за складови разписки', 'colab_plg_Document', 'store_Receipts', 'private');
        $html .= $Plugins->installPlugin('Colab за експедиционни нареждания', 'colab_plg_Document', 'store_ShipmentOrders', 'private');
        $html .= $Plugins->installPlugin('Colab за протоколи за отговорно пазене', 'colab_plg_Document', 'store_ConsignmentProtocols', 'private');
        $html .= $Plugins->installPlugin('Colab за резолюция на сигнал', 'colab_plg_Document', 'support_Resolutions', 'private');
        $html .= $Plugins->installPlugin('Colab за коментар', 'colab_plg_Document', 'doc_Comments', 'private');
        $html .= $Plugins->installPlugin('Colab за бележка', 'colab_plg_Document', 'doc_Notes', 'private');
        $html .= $Plugins->installPlugin('Colab за задачи', 'colab_plg_Document', 'cal_Tasks', 'private');
        $html .= $Plugins->installPlugin('Colab за регистрация на потребители', 'colab_plg_UserReg', 'core_Users', 'private');
        $html .= $Plugins->installPlugin('Colab за напомняния', 'colab_plg_Document', 'cal_Reminders', 'private');

        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на коментари', 'colab_plg_VisibleForPartners', 'doc_Comments', 'private');
        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на бележки', 'colab_plg_VisibleForPartners', 'doc_Notes', 'private');
        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на задачи', 'colab_plg_VisibleForPartners', 'cal_Tasks', 'private');
        $defaultCreatableDocuments = arr::make(self::$defaultCreatableDocuments);
        $html .= $Plugins->installPlugin('Colab за справки', 'colab_plg_Document', 'frame2_Reports', 'private');
        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на справки', 'colab_plg_VisibleForPartners', 'frame2_Reports', 'private');

        $html .= $Plugins->installPlugin('Colab за ПО', 'colab_plg_Document', 'planning_Tasks', 'private');
        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на ПО', 'colab_plg_VisibleForPartners', 'planning_Tasks', 'private');
        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на напомняния', 'colab_plg_VisibleForPartners', 'cal_Reminders', 'private');
        $html .= $Plugins->installPlugin('Colab за ПП', 'colab_plg_Document', 'planning_DirectProductionNote', 'private');
        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на ПП', 'colab_plg_VisibleForPartners', 'planning_DirectProductionNote', 'private');
        $html .= $Plugins->installPlugin('Colab за ПВ', 'colab_plg_Document', 'planning_ConsumptionNotes', 'private');
        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на ПВ', 'colab_plg_VisibleForPartners', 'planning_ConsumptionNotes', 'private');
        $html .= $Plugins->installPlugin('Colab за ПВР', 'colab_plg_Document', 'planning_ReturnNotes', 'private');
        $html .= $Plugins->installPlugin('Плъгин за споделяне с партньори на ПВР', 'colab_plg_VisibleForPartners', 'planning_ReturnNotes', 'private');

        cls::get('cal_Tasks')->setupMvc();
        cls::get('cal_Reminders')->setupMvc();
        cls::get('planning_Tasks')->setupMvc();
        cls::get('planning_ReturnNotes')->setupMvc();
        cls::get('planning_ConsumptionNotes')->setupMvc();
        cls::get('planning_DirectProductionNote')->setupMvc();

        foreach ($defaultCreatableDocuments as $docName) {
            $Doc = cls::get($docName);
            $title = mb_strtolower($Doc->title);
            $html .= $Plugins->installPlugin("Colab плъгин за {$title}", 'colab_plg_CreateDocument', $docName, 'private');
        }
        
        $html .= core_Roles::addOnce('distributor', null, 'external');
        $html .= core_Roles::addOnce('agent', null, 'external');
        
        return $html;
    }
    
    
    /**
     * Помощна функция връщаща всички класове, които са документи
     */
    public static function getDocumentOptions()
    {
        $options = core_Classes::getOptionsByInterface('colab_CreateDocumentIntf', 'title');
        
        return $options;
    }
    
    
    /**
     * Форсира, кои документи да могат да се създават от партньори
     */
    public static function forceCreatableDocuments()
    {
        $res = '';
        $arr = array();
        $defaultCreatableDocuments = arr::make(self::$defaultCreatableDocuments);
        foreach ($defaultCreatableDocuments as $docName) {
            $Doc = cls::get($docName);
            if (cls::haveInterface('colab_CreateDocumentIntf', $Doc)) {
                $classId = $Doc->getClassId();
                $arr[$classId] = $classId;
            }
        }
        
        // Записват се ид-та на документите, които могат да се създават от контрактори
        if (countR($arr)) {
            core_Packs::setConfig('colab', array('COLAB_CREATABLE_DOCUMENTS_LIST' => keylist::fromArray($arr)));
            $res = "<li style='color:green'>Задаване на дефолт документи, които могат да се създават от партньори";
        }
        
        return $res;
    }
    
    
    /**
     * Зареждане на начални данни
     */
    public function loadSetupData($itr = '')
    {
        $res = '';
        $config = core_Packs::getConfig('colab');
        if (strlen($config->COLAB_CREATABLE_DOCUMENTS_LIST) === 0) {
            $res = self::forceCreatableDocuments();
        }

        return $res;
    }
}
