<?php



/**
 * @category  bgerp
 * @package   bgerp
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class bgerp_BaseImporter extends core_Manager {
    
    
    /**
     * Интерфейси, поддържани от този мениджър
     */
    var $interfaces = 'bgerp_ImportIntf';
    
    
    /**
     * Заглавие
     */
    var $title = "Стандартен импорт";
    
    /*
     * Имплементация на bgerp_ImportIntf
     */
    
    
    /**
     * Инициализиране драйвъра
     */
    function init($params = array())
    {
        $this->mvc = $params['mvc'];
    }
    
    
    /**
     * Функция, връщаща полетата в които ще се вкарват данни
     * в мениджъра-дестинация
     * Не връща полетата които са hidden, input=none,enum,key и keylist
     */
    public function getFields()
    {
        $fields = array();
        $Dfields = $this->mvc->selectFields();
        
        foreach($Dfields as $name => $fld){
            if($fld->input != 'none' && $fld->input != 'hidden' &&
                $fld->kind != 'FNC' && !($fld->type instanceof type_Enum) &&
                !($fld->type instanceof type_Key) && !($fld->type instanceof type_KeyList)){
                $fields[$name] = array('caption' => $fld->caption, 'mandatory' => $fld->mandatory);
            }
        }
        
        return $fields;
    }
    
    
    /**
     * Инпортиране на csv-файл в даден мениджър
     * @param array $rows - масив с обработени csv данни, получен от Експерта в bgerp_Import
     * @param array $fields - масив с съответстията на колоните от csv-то и
     * полетата от модела array[{поле_oт_модела}] = {колона_от_csv}
     * @return string $html - съобщение с резултата
     */
    public function import($rows, $fields)
    {
        $html = '';
        $created = $updated = 0;
        core_Debug::startTimer('import');
        
        foreach ($rows as $row){
            $rec = new stdClass();
            
            foreach($fields as $name => $position){
                if($position != -1){
                    $value = $row[$position];
                    $rec->{$name} = $value;
                }
            }
            
            // Ако записа е уникален, създаваме нов, ако не е обновяваме стария
            $fieldsUn = array();
            
            if(!$this->mvc->isUnique($rec, $fieldsUn, $exRec)){
                $rec->id = $exRec->id;
                $updated++;
            } else {
                $created++;
            }
            
            $this->mvc->save($rec);
        }
        
        core_Debug::stopTimer('import');
        
        $html .= "Импортирани {$created} нови записа, обновени {$updated} съществуващи записа<br />";
        $html .= "Общо време: " . round(core_Debug::$timers['import']->workingTime, 2) . " с";
        
        return $html;
    }
    
    
    /**
     * Драйвъра може да се показва към всички мениджъри
     */
    public function isApplicable($className)
    {
        return TRUE;
    }
}