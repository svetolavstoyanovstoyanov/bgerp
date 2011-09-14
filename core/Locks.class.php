<?php

/**
 * Клас 'core_Lock' - Мениджър за заключване на обекти
 *
 *
 * @category   Experta Framework
 * @package    core
 * @author     Milen Georgiev <milen@download.bg>
 * @copyright  2006-2011 Experta Ltd.
 * @license    GPL 3
 * @version    CVS: $Id:$
 * @link
 * @since      v 0.1
 */
class core_Locks extends core_Manager
{
    /**
     * Заглавие на мениджъра
     */
    var $title = 'Заключвания';
    
         
    /**
     * Кои полета ще бъдат показани?
     */
    // var $listFields = 'id,createdOn=Кога?,createdBy=Кой?,what=Какво?';
    
    
    /**
     * Кой може да листва и разглежда?
     */
    var $canRead = 'admin';
    
    
    /**
     * Кой може да добавя, редактира и изтрива?
     */
    var $canWrite = 'no_one';
    
    
    /**
     * Плъгини и MVC класове за предварително зареждане
     */
    var $loadList = 'plg_SystemWrapper';
    

    /**
     * Масив с $objectId на всички заключени обекти от текущия хит
     */
    var $locks = array();
    
    /**
     * Описание на полетата на модела
     */
    function description()
    {
        $this->FLD('objectId', 'varchar(32)', 'caption=Обект');
        $this->FLD('lockExpire', 'int', 'caption=Срок');
        $this->FLD('user', 'key(mvc=core_Users)', 'caption=Потребител');

        $this->setDbUnique('objectId');

        $this->setDbEngine = 'memory';
    }

    
    /**
     * Заключва обект с посоченото $objectId за максимално време $maxDuration, 
     * като за това прави $maxTrays опити, през интервал от 1 секунда
     */
    function add($objectId, $maxDuration = 2, $maxTrays = 1)
    {
        $Locks = cls::get('core_Locks');
        
        // Санитаризираме данните
        $maxTrays = max($maxTrays, 1);
        $maxDuration = max($maxDuration , 0);
        $objectId = str::convertToFixedKey($objectId, 32, 4);

        $lockExpire = time() + $maxDuration;
        
        $rec = $Locks->locks[$objectId];
        
        
        // Ако този обект е заключен от текущия хит, връщаме TRUE
        if($rec) {
            // Ако имаме промяна в крайния срок за заключването
            // отразяваме я в модела
            if($rec->lockExpire < $lockExpire) {
                $rec->lockExpire = $lockExpire;
                $Locks->save($rec);
            }

            return TRUE;
        }
        
        // Изтриваме записа, ако заключването е преминало крайния си срок
        $rec = $Locks->fetch(array("#objectId = '[#1#]'", $objectId));

        if($rec->lockExpire <= time()) {
            $Locks->delete($rec->id);
        }
        
        $rec = new stdClass();
        $rec->lockExpire = $lockExpire;
        $rec->objectId   = $objectId;
        $rec->user       = core_Users::getCurrent();

        do {
            $Locks->save($rec, NULL, 'IGNORE');
            $maxTrays--;
        } while(empty($rec->id) && $maxTrays>0 && (sleep(1) != 1));

        if($rec->id) {
            $this->locks[$objectId] = $rec;
            
            return TRUE;
        }

        return FALSE;
    }

    
    /**
     * Деструктор, който премахва всички локвания от текущия хит
     */
    function __destruct()
    {
        if(count($this->locks)) {
            foreach($this->locks as $rec) {
              //  $this->delete($rec->id);
            }
        }
    }

    
    
}