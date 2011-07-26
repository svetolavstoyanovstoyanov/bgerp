<?php

/**
 * Клас 'core_Master' - Мениджър за единичните данни на бизнес обекти
 *
 *
 * @category   Experta Framework
 * @package    core
 * @author     Milen Georgiev <milen@download.bg>
 * @copyright  2006-2009 Experta Ltd.
 * @license    GPL 2
 * @version    CVS: $Id:$
 * @link
 * @since      v 0.1
 */
class core_Master extends core_Manager
{
    
    
    /**
     * Мениджърите на детаилите записи към обекта
     */
    var $details;
    
    
    /**
     * Титлата на обекта в единичен изглед
     */
    var $singleTitle;
    
    
    /**
     * Изпълнява се след конструирането на мениджъра
     */
    function on_AfterDescription($mvc)
    {
        // Списъка с детаилите става на масив
        $this->details = arr::make($this->details, TRUE);
        
        // Зарежда mvc класовете
        $this->load($this->details);
    }
    
    
    /**
     * Връща единичния изглед на обекта
     */
    function act_Single()
    {
        // Проверяваме дали потребителя може да вижда списък с тези записи
        $this->requireRightFor('single');
        
        // Създаваме обекта $data
        $data = new stdClass();
        
        // Трябва да има id
        expect($id = Request::get('id'));
        
        // Трябва да има $rec за това $id
        expect($data->rec = $this->fetch($id));
        
        // Подготвяме полетата за показване
        $this->prepareSingleFields($data);
        
        // Подготвяме вербалните стойности на записа
        $data->row = $this->recToVerbal($data->rec, $data->singleFields);
        
        // Подготвяме титлата
        $this->prepareSingleTitle($data);
        
        // Подготвяме тулбара
        $this->prepareSingleToolbar($data);
        
        // Подготвяме детаилите
        if(count($this->details)) {
            foreach($this->details as $var => $class) {
                $detailData = $data->{$var} = new stdClass();
                $detailData->masterId = $id;
                $detailData->masterData = $data;
                $this->{$var}->prepareDetail($detailData);
            }
        }
        
        // Рендираме изгледа
        $tpl = $this->renderSingle($data);
        
        // Опаковаме изгледа
        $tpl = $this->renderWrapping($tpl, $data);
        
        // Записваме, че потребителя е разглеждал този списък
        $this->log('Single: ' . ($data->log?$data->log:$data->title), $id);
        
        return $tpl;
    }
    
    
    /**
     * Подготвя списъка с полетата, които ще се показват в единичния изглед
     */
    function prepareSingleFields_($data)
    {
        if( isset( $this->singleFields ) ) {
            
            // Ако са зададени $this->listFields използваме ги тях за колони
            $data->singleFields = arr::make($this->listFields, TRUE);
        } else {
            
            // Използваме за колони, всички полета, които не са означени с column = 'none'
            $fields = $this->selectFields("#single != 'none'");
            
            if (count($fields)) {
                foreach ($fields as $name => $fld) {
                    $data->singleFields[$name] = $fld->caption;
                }
            }
        }
        
        if (count($data->singleFields)) {
            
            // Ако титлата съвпада с името на полето, вадим името от caption
            foreach ($data->singleFields as $field => $caption) {
                if (($field == $caption) && $this->fields[$field]->caption) {
                    $data->singleFields[$field] = $this->fields[$field]->caption;
                }
            }
        }
        
        return $data;
    }
    
    
    /**
     * Подготвя титлата в единичния изглед
     */
    function prepareSingleTitle_($data)
    {
        $title = $this->getRecTitle($data->rec);
        
        $data->title = $this->singleTitle . " \"{$title}\"";
        
        return $data;
    }
    
    
    /**
     * Подготвя тулбара за единичния изглед
     */
    function prepareSingleToolbar_($data)
    {
        $data->toolbar = cls::get('core_Toolbar');
        
        $data->toolbar->id = 'SingleToolbar';
        
        if (isset($data->rec->id) && $this->haveRightFor('edit', $data->rec)) {
            $data->toolbar->addBtn('Редактиране', array(
                $this,
                'edit',
                $data->rec->id,
                'ret_url' => TRUE
            ),
            'id=btnEdit,class=btn-edit');
        }
        
        return $data;
    }
    
    
    /**
     * Рендираме общия изглед за 'List'
     */
    function renderSingle_($data)
    {
        // Рендираме общия лейаут
        $tpl = $this->renderSingleLayout($data);
        
        // Поставяме данните от реда
        $tpl->placeObject($data->row);
        
        // Поставя титлата
        $tpl->replace($this->renderSingleTitle($data), 'SingleTitle');
        
        // Поставяме toolbar-а
        $tpl->replace($this->renderSingleToolbar($data), 'SingleToolbar');
        
        // Поставяме детаилите
        if(count($this->details)) {
            foreach($this->details as $var => $class) {
                $tpl->replace($this->{$var}->renderDetail($data->{$var}), 'Detail' . $var);
            }
        }
        
        return $tpl;
    }
    
    
    /**
     * Подготвя шаблона за единичния изглед
     */
    function renderSingleLayout_($data)
    {
        if( count($this->details) ) {
            foreach($this->details as $var => $className) {
                $detailsTpl .= "[#Detail{$var}#]";
            }
        }
        
        if( count($data->singleFields) ) {
            foreach($data->singleFields as $field => $caption) {
                $fieldsHtml .= "<tr><td>{$caption}</td><td>[#{$field}#]</td></tr>";
            }
        }
        
        return new ET("[#SingleToolbar#]<h2>[#SingleTitle#]</h2><table class=listTable>{$fieldsHtml}</table>{$detailsTpl}");
    }
    
    
    /**
     * Рендира титлата на обекта в single view
     */
    function renderSingleTitle_($data)
    {
        return new ET($data->title);
    }
    
    
    /**
     * Рендира тулбара на единичния изглед
     */
    function renderSingleToolbar_($data)
    {
        if(cls::isSubclass($data->toolbar, 'core_Toolbar')) {
            
            return $data->toolbar->renderHtml();
        }
    }
    
    
    /**
     * Връща ролите, които могат да изпълняват посоченото действие
     */
    function getRequiredRoles_($action, $rec = NULL, $userId = NULL)
    {
        if($action == 'single') {
            $action{0} = strtoupper($action{0});
            $action = 'can' . $action;
            
            if(!($requiredRoles = $this->{$action})) {
                $requiredRoles = $this->getRequiredRoles('read', $rec, $userId);
            }
        } else {
            $requiredRoles = parent::getRequiredRoles_($action, $rec, $userId);
        }
        
        return $requiredRoles;
    }
}