<?php


/**
 * Синхронизиране на фирми между bgERP системи
 *
 *
 * @category  bgerp
 * @package   synck
 *
 * @author    Milen Georgiev <milen@experta.bg>
 * @copyright 2020 - 2020 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 * @title     Синхронизиране на фирми между bgERP системи
 */
class sync_Companies extends sync_Helper
{
    /**
     * Какво друго да експортираме?
     */
    public $exportAlso = array(
            'crm_Companies' => array(
                    array('crm_Locations' => 'contragentCls|contragentId'),
                    array('bank_Accounts' => 'contragentCls|contragentId'),
                    array('cond_ConditionsToCustomers' => 'cClass|cId'),
                    array('price_ListToCustomers' => 'cClass|cId'),
                    array('crm_ext_Cards' => 'contragentClassId|contragentId'),
            ),
            
            'cat_Listings' => array(
                    array('cat_ListingDetails' => 'listId'),
            ),

            'crm_Locations' => array(
                    array('sales_Routes' => 'locationId'),
            ),
            'price_Lists' => array(
                    array('price_ListRules' => 'listId'),
            ),
            'cat_Products' => array(
                    array('cat_products_Packagings' => 'productId'),
                    array('cat_products_Params' => 'classId|productId'),
            ),
    );
    
    
    /**
     *  Връща Json-a на филтрираните обекти
     */
    public function act_Export()
    {
        self::requireRight();
        
        expect(core_Packs::isInstalled('crm'));
        
        core_App::setTimeLimit(1000);
        
        $groupId = sync_Setup::get('COMPANY_GROUP');
        
        expect($groupId);
        
        $res = array();
        
        core_Users::forceSystemUser();
        
        $cQuery = crm_Companies::getQuery();
        while ($rec = $cQuery->fetch("#groupList LIKE '%|{$groupId}|%'")) {
            sync_Map::exportRec('crm_Companies', $rec->id, $res, $this);
            $folderId = $rec->folderId;
            $lRec = cat_Listings::fetch("#state = 'active' AND #folderId = {$folderId}");
            if($lRec) {
                $lRec->_companyId = $rec->id;
                sync_Map::exportRec('cat_Listings', $lRec, $res, $this);
            }
        }
        
        core_Users::cancelSystemUser();
        
        if (Request::get('_bp') && haveRole('admin')) {
            bp($res);
        }
        
        return self::outputRes($res);
    }


    /**
     * Синхронизира двете системи
     */
    public function act_Import()
    {
        self::requireRight('import');
        
        ini_set('memory_limit', '2048M');
        
        expect(core_Packs::isInstalled('crm'));
        
        core_App::setTimeLimit(1000);
        
        $resArr = self::getDataFromUrl(get_called_class());
        
        if (Request::get('_bp')) {
            bp($resArr);
        }
        
        core_Users::forceSystemUser();
        
        Mode::set('preventNotifications', true);
        
        $update = (Request::get('update') == 'none') ? false : true;
        
        foreach ($resArr as $class => $objArr) {
            self::logDebug("$class");
            foreach ($objArr as $id => $rec) {
                sync_Map::importRec($class, $id, $resArr, $this, $update);
            }
        }
        
        cat_ListingDetails::delete("#productId = 0");
        
        crm_Groups::updateGroupsCnt('crm_Persons', 'personsCnt');
    }
}
