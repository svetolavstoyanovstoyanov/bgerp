<?php


/**
 * Плъгин за превръщане на документи във видими за партньори,
 * за които не е зададено твърдо 'visibleForPartners' пропърти
 * 
 * @category  bgerp
 * @package   colab
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class colab_plg_VisibleForPartners extends core_Plugin
{

    /**
     * След дефиниране на полетата на модела
     *
     * @param core_Mvc $mvc
     */
    public static function on_AfterDescription($mvc)
    {
        if (!$mvc->fields['visibleForPartners']) {
            $mvc->FLD('visibleForPartners', 'enum(no=Не,yes=Да)', 'caption=Споделяне->С партньори, input=none');
        }
    }
    
    
    /**
     * Преди показване на форма за добавяне/промяна.
     *
     * @param core_Manager $mvc
     * @param stdClass $data
     */
    public static function on_AfterPrepareEditForm($mvc, &$data)
    {
        $rec = $data->form->rec; 
        if ($rec->folderId) {
            
            // Полето се показва ако е в папката, споделена до колаборатор
            // Ако няма originId или ако originId е към документ, който е видим от колаборатор
            if (colab_FolderToPartners::fetch(array("#folderId = '[#1#]'", $rec->folderId))) {
                if (!$rec->originId || ($doc = doc_Containers::getDocument($rec->originId)) && ($doc->isVisibleForPartners())) {
                    if (core_Users::isContractor()) {
                        // Ако текущия потребител е контрактор, полето да е скрито
                        $data->form->setField('visibleForPartners', 'input=hidden');
                    } else {
                        $data->form->setField('visibleForPartners', 'input=input');
                    }
                    
                    // Ако документа е създаден от контрактор, тогава да е споделен по-подразбиране
                    if (!$rec->id && core_Users::isContractor($rec->createdBy)) {
                        $data->form->setDefault('visibleForPartners', 'yes');
                    }
                    
                    // Ако няма да се показва на колаборатори по-подразбиране, да е скрито полето
                    if ($rec->visibleForPartners !== 'yes') {
                        $data->form->setField('visibleForPartners', 'autohide');
                    }
                }
            }
        }
    }
    
    
    /**
     * Връща дали документа е видим за партньори
     * 
     * @param core_Mvc $mvc
     * @param NULL|string $res
     * @param integer|stdObject $rec
     */
    public static function on_BeforeIsVisibleForPartners($mvc, &$res, $rec)
    {
        $rec = $mvc->fetchRec($rec);
        
        if (!isset($res)) {
            if ($rec->visibleForPartners === 'yes') {
                $res = TRUE;
            }
        }
    }
}
