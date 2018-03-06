<?php


/**
 * Инсталиране/Деинсталиране на
 * мениджъри свързани с label
 *
 * @category  bgerp
 * @package   label
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2018 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class label_Setup extends core_ProtoSetup
{
    
    
    /**
     * Версията на пакета
     */
    var $version = '0.1';
    
    
    /**
     * Мениджър - входна точка в пакета
     */
    var $startCtr = 'label_Prints';
    
    
    /**
     * Екшън - входна точка в пакета
     */
    var $startAct = 'default';
    
    
    /**
     * Описание на модула
     */
    var $info = "Отпечатване на етикети";
    
    
    /**
     * Връзки от менюто, сочещи към модула
     */
    var $menuItems = array(
            array(3.66, 'Производство', 'Етикетиране', 'label_Prints', 'default', "label, admin, ceo"),
        );
    
        
    // Инсталиране на мениджърите
    public $managers = array(
        'label_Templates',
        'label_TemplateFormats',
        'label_Media',
        'label_Counters',
        'label_CounterItems',
        'label_Prints',
        'migrate::addDefaultMedia',
        'migrate::labelsToPrint',
        'migrate::counterItemsLabels',
    	'migrate::removePlugin'
    );
    

    /**
     * Роли за достъп до модула
     */
    public $roles = array(
    		array('seeLabel'),
    		array('label', 'seeLabel'),
    		array('labelMaster', 'label'),
    );
    
    
    /**
     * Инсталиране на пакета
     */
    function install()
    {   
        $html = parent::install();
        
        $Plugins = cls::get('core_Plugins');
        $html .= $Plugins->installPlugin('Принтиране на етикети от опаковки', 'label_plg_Print', 'cat_products_Packagings', 'private');
        $html .= $Plugins->installPlugin('Принтиране на етикети от задачи за производство', 'label_plg_Print', 'planning_Tasks', 'private');
        $html .= $Plugins->installPlugin('Принтиране на етикети от ЕН-та', 'label_plg_Print', 'store_ShipmentOrders', 'private');
        
        return $html;
    }
    
    
    /**
     * Де-инсталиране на пакета
     */
    function deinstall()
    {
        // Изтриване на пакета от менюто
        $res .= bgerp_Menu::remove($this);
        
        return $res;
    }
    
    
    /**
     * Миграция за добавяне на медия към шаблоните
     */
    public static function addDefaultMedia()
    {
        // Вземаме размера на първата медия
        $mQuery = label_Media::getQuery();
        while ($mRec = $mQuery->fetch()) {
            $sizes = label_Media::getSize($mRec->width, $mRec->height);
            
            if ($sizes) break;
        }
        
        if (!$sizes) return ;
        
        // Добавяме размера към всички шаблони, които нямат размери
        $tQuery = label_Templates::getQuery();
        $tQuery->where("#sizes IS NULL");
        $tQuery->orWhere("#sizes = ''");
        
        while ($tRec = $tQuery->fetch()) {
            $tRec->sizes = $sizes;
            label_Templates::save($tRec, 'sizes');
        }
    }
    
    
    /**
     * Миграция за преместване на данните от етикетите в отпечатванията
     */
    public function labelsToPrint()
    {
        $clsName = 'label_Labels';
        
        if (!cls::load($clsName, TRUE)) continue;
        
        $clsInst = cls::get($clsName);
        
        if (!$clsInst->db->tableExists($clsInst->dbTableName)) continue;
        
        $pInst = cls::get('label_Prints');
        
        if (!$pInst->db->isFieldExists($pInst->dbTableName, str::phpToMysqlName('labelId'))) return;
        
        $pInst->FLD('labelId', 'key(mvc=label_Labels, select=title)', 'caption=Етикет, mandatory, silent, input=none');
        
        $pQuery = $pInst->getQuery();
        $pQuery->where("#labelId IS NOT NULL");
        $pQuery->where("#labelId != ''");
        
        while ($pRec = $pQuery->fetch()) {
            if (!$pRec->labelId) continue;
            
            $lRec = $clsInst->fetch($pRec->labelId);
            
            if (!$lRec) continue;
            
            $vArr = array('templateId', 'title', 'classId', 'objectId' => 'objId', 'params');
            
            $vArr = arr::make($vArr, TRUE);
            
            foreach ($vArr as $fName => $lFName) {
                $pRec->{$fName} = $lRec->{$lFName};
            }
            
            if ($lRec->state == 'rejected') {
                $pRec->state = 'rejected';
                $vArr['state'] = 'state';
            }
            
            $pRec->_notModified = TRUE;
            
            $pInst->save($pRec, $vArr);
        }
    }
    
    
    /**
     * Миграция за преместване на полетата на броячите
     */
    public function counterItemsLabels()
    {
        $clsName = 'label_Labels';
        
        if (!cls::load($clsName, TRUE)) continue;
        
        $clsInst = cls::get($clsName);
        
        if (!$clsInst->db->tableExists($clsInst->dbTableName)) continue;
        
        $cItemsInst = cls::get('label_CounterItems');
        
        if (!$cItemsInst->db->isFieldExists($cItemsInst->dbTableName, str::phpToMysqlName('labelId'))) return;
        
        $pInst = cls::get('label_Prints');
        
        if (!$pInst->db->isFieldExists($pInst->dbTableName, str::phpToMysqlName('labelId'))) return;
        
        $pInst->FLD('labelId', 'key(mvc=label_Labels, select=title)', 'caption=Етикет, mandatory, silent, input=none');
        $cItemsInst->FLD('labelId', 'key(mvc=label_Labels, select=title)', 'caption=Етикет');
        
        $cQuery = $cItemsInst->getQuery();
        $cQuery->where("#labelId IS NOT NULL");
        $cQuery->where("#labelId != ''");
        
        while ($cRec = $cQuery->fetch()) {
            if (!$cRec->labelId) continue;
            
            $pQuery = $pInst->getQuery();
            $pQuery->where(array("#labelId = '[#1#]'", $cRec->labelId));
            
            $pQuery->orderBy("state", "DESC");
            
            $pRec = $pQuery->fetch();
            
            if (!$pRec) continue;
            
            $cRec->printId = $pRec->id;
            
            $cItemsInst->save($cRec, 'printId');
        }
    }
    
    
    /**
     * Миграция за добавяне на медия към шаблоните
     */
    public static function removePlugin()
    {
    	$Plugins = core_Plugins::getQuery();
    	$Plugins->delete(array("#class LIKE 'planning_Jobs' AND #plugin LIKE 'label_plg_Print'"));
    }
}
