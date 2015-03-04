<?php


/**
 * Клас 'change_Plugin' - Плъгин за променя само на избрани полета
 *
 * @category  vendors
 * @package   change
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2013 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class change_Plugin extends core_Plugin
{
    
	
	/**
     * След дефиниране на полетата на модела
     * 
     * @param core_Mvc $mvc
     */
    public static function on_AfterDescription(core_Mvc $mvc)
    {
        // Ако няма добавено поле за версия
        if (!$mvc->fields['version']) {
            
            // Добавяме
            $mvc->FLD('version', 'varchar', 'caption=Версия->Номер,input=none,width=100%');
        }
        
        // Ако няма добавено поле за подверсия
        if (!$mvc->fields['subVersion']) {
            
            // Добавяме
            $mvc->FLD('subVersion', 'int', 'caption=Подверсия,input=none');
        }
        
//        $mvc->loadList = arr::make($mvc->loadList, TRUE);
//        $mvc->loadList['plg_Modified'] = 'plg_Modified';
    }
    
    
    /**
     * Добавя бутони за контиране или сторниране към единичния изглед на документа
     */
    function on_AfterPrepareSingleToolbar($mvc, $data)
    {
        // Ако има права за промяна
        if ($mvc->haveRightFor('changerec', $data->rec)) {
            $changeUrl = array(
                $mvc,
                'changeFields',
                $data->rec->id,
                'ret_url' => array($mvc, 'single', $data->rec->id),
            );
            
            // Добавяме бутона за промяна
            $data->toolbar->addBtn('Промяна', $changeUrl, array('id'=>'conto','order'=>'20', 'ef_icon'=>'img/16/to_do_list.png', 'title'=>'Промяна на документа'));    
        }
    }
    
    
	/**
     *  
     */
    public static function on_BeforeAction($mvc, &$tpl, $action)
    {
        // Ако екшъна не е changefields, да не се изпълнява
        if (strtolower($action) != 'changefields') return ;
        
        // Ако има права за промяна
        $mvc->requireRightFor('changerec');
        
        $data = new stdClass();

        $data->action = 'changefields';
        
        // Създаване и подготвяне на формата
        $mvc->prepareEditForm($data);
        
        // Вземаме формата към този модел
        $form = &$data->form;
        
        // Екшъна, който ще се използва
        $form->setAction($mvc, 'changefields');
        
        // Записите от формата
        $fRec = &$form->rec;
        
        // Очакваме да има такъв запис
        expect($rec = $mvc->fetch($fRec->id));
        
        // Вземаме всички позволени полета
        $allowedFieldsArr = static::getAllowedFields($form, $mvc->changableFields);
        
        // Очакваме да има зададени полета, които ще се променят
        expect(count($allowedFieldsArr));
        
        // Полетата, които ще записваме в лога
        $fieldsArrLogSave = $allowedFieldsArr;
        
        // Дабавяме версията
        $allowedFieldsArr['version'] = 'version';
        
        // Полетата, които ще се показва
        $fieldsArrShow = $allowedFieldsArr;
        
        // Добавяме подверсията
        $allowedFieldsArr['subVersion'] = 'subVersion';
        
        // Стринга, за инпутване
        $inputFields = implode(',', $allowedFieldsArr);
        
        // Добавяме и id
        $inputFields .= ',id';
        
        // Въвеждаме полетата
        $form->input($inputFields, 'silent');
        
        // Очакваме потребителя да има права за съответния запис
        $mvc->requireRightFor('single', $fRec);
        
        // Изискваме да има права за промяна на записа
        $mvc->requireRightFor('changerec', $fRec);

        // Генерираме събитие в AfterInputEditForm, след въвеждането на формата
        $mvc->invoke('AfterInputEditForm', array($form));
        
        // URL' то където ще се редиректва
        $retUrl = getRetUrl();
        
        // Ако няма такова URL, връщаме към single' а
        $retUrl = ($retUrl) ? ($retUrl) : array($mvc, 'single', $fRec->id);
        
        // id на класа
        $classId = core_Classes::getId($mvc);
        
        // Масива с първата и последната версия
        $versionArr = change_Log::getFirstAndLastVersion($classId, $rec->id);
        
        // Ако формата е изпратена без грешки
        if($form->isSubmitted()) {
            
            if (is_null($rec->version) && is_null($rec->subVersion)) {
                $rec->version = 0;
                $rec->subVersion = 1;
            }
            
            // Ако не е подадена версия
            if (!$fRec->version) {
                
                // Да е нула
                $fRec->version = '0';
            }
            
            // Ако сме променили версията
            if ((string)$fRec->version != (string)$rec->version) {
                
                // Нулираме флага
                $fRec->NoChange = FALSE;
                
                // Подверсията
                $subVersion = 0;
                
                // Ако има id
                if ($rec->id) {
                    
                    // Вземаме последните подверсии за съответнате версии
                    $lastSubVersionsArr = change_Log::getLastSubVersionsArr($mvc, $rec->id);
                }
                
                // Ако я има съответната версия
                if ($lastSubVersionsArr[$fRec->version]) {
                    
                    // Вземаме подверсията
                    $subVersion = $lastSubVersionsArr[$fRec->version];
                }
            } else {
                
                // Подверсията
                $subVersion = $rec->subVersion;
            }
            
            // Ако не е зададено да не се променя
            if (!$fRec->NoChange) {
                
                // Увеличаваме подверсията
                $subVersion++;
            
                // Добавяме подверсията
                $fRec->subVersion = $subVersion;
                
                // Извикваме фунцкията, за да дадем възможност за добавяне от други хора
                $mvc->invoke('AfterInputChanges', array($rec, $fRec));
                
                // Записваме промени
                $mvc->save($fRec);
            
                // Записваме лога на промените
                $savedRecsArr = change_Log::create($mvc->className, $fieldsArrLogSave, $rec, $fRec);
                
                // Извикваме фунцкия, след като запишем
                $mvc->invoke('AfterSaveLogChange', array($savedRecsArr));
            }
            
            // Редиректваме
            return redirect($retUrl);
        }
        
        // Ако няма грешки
        if (!$form->gotErrors()) {
            
            // Вземаме данните
            $vRec = $rec;
            
            // Ако няма последна версия
            if (!$versionArr['last']) {
                
                // Ако има първа версия
                if ($versionArr['first']) {

                    // Версията, която ще използваме е първата
                    $versionKey = $versionArr['first'];
                }
            } else {
                
                // Версията, която ще използваме е последната
                $versionKey = $versionArr['last'];
            }
            
            // Вземаме записитеи за съответния ред
            $gRecArr = change_Log::getRecForVersion($classId, $rec->id, $versionKey, $fieldsArrLogSave);
            
            // Обхождаме масива
            foreach ((array)$gRecArr as $field => $gRec) {
                
                // Ако няма запис - прескачаме
                if (!$gRec) continue;
                
                // Добавяме полето към записа
                $vRec->$field = $gRec->value;
                
                // Добавяме версията
                $vRec->version = $gRec->version;
            }
            
            // Ако има избрана версия, от нея да се вземат всичките данни
            if ($versionKey) {
                
                // Обхождаме стария запис
                foreach ((array)$fieldsArrShow as $field) {
                    
                    // Добавяме старта стойност
                    $form->rec->$field = $vRec->$field;
                }
            }
        }
        
        // Задаваме да се показват само полетата, които ни интересуват
        $form->showFields = $fieldsArrShow;
        
        // Добавяме бутоните на формата
        $form->toolbar->addSbBtn('Запис', 'save', 'ef_icon = img/16/disk.png');
        $form->toolbar->addBtn('Отказ', $retUrl, 'ef_icon = img/16/close16.png');
        
        $form->title = 'Промяна';
        
        try {
            
            // Ако имплементира doc_DocumentIntf
            if (cls::haveInterface('doc_DocumentIntf', $mvc)) {
                
                // Титлата на документа
                $title = $mvc->getDocumentRow($fRec->id)->title;
                
                // Ако има открито заглавие
                if ($title) {
                    
                    // Титлата на формата
                    $form->title .= " на|*: <i>{$title}</i>";
                }
            }
        } catch (core_exception_Expect $e) {}
        
        // Ако има избрана версия
        if ($versionKey) {
            
            // Вземаме стринга
            $versionStr = change_Log::getVersionStrFromKey($mvc, $versionKey);
                
            // Към заглавието добавяме вербалното представяне на версията
            $form->title .= "|* <b style='color:red;'>{$versionStr}</b>";
        }
        
        // Рендираме изгледа
        $tpl = $mvc->renderWrapping($form->renderHtml());
        
        return FALSE;
    }
    
    
    /**
     * 
     */
    public static function on_AfterPrepareSingle($mvc, $data)
    {
        // id на класа
        $classId = core_Classes::getId($mvc);
        
        // Масив с най - новата и най - старата версия
        $selVerArr = change_Log::getFirstAndLastVersion($classId, $data->rec->id);
        
        // Последна версия
        $lastVersion = change_Log::getLastVersionIdFromDoc($classId, $data->rec->id);
        
        // Вземаме формата
        $form = $mvc->getForm();
        
        // Вземаме всички полета, които могат да се променят
        $allowedFieldsArr = (array)static::getAllowedFields($form, $mvc->changableFields);
        
        if ($selVerArr['first'] != $lastVersion) {
            
            // Ако има избрана версия
            if ($selVerArr['first']) {
                
                $lastArr = array();
                
                // Вземаме стойността за съответното поле, за първата версия
                $firstArr = change_Log::getVerbalValue($classId, $data->rec->id, $selVerArr['first'], $allowedFieldsArr);
                
                // Ако има последна версия
                if ($selVerArr['last']) {
                    
                    // Стринга на последната версия
                    $lastVersionStr = change_Log::getLastVersionIdFromDoc($mvc, $data->rec->id);
                    
                    // Ако последната версия е последния вариант
                    if ($selVerArr['last'] == $lastVersionStr) {
                        
                        // Обхождаме всички позволени полеоте, които ще се променят
                        foreach ($allowedFieldsArr as $allowedField) {
                            
                            // Добавяме в масива
                            $lastArr[$allowedField] = $data->row->$allowedField;
                        }
                    } else {
                        
                        // Вземаме стойността за съответното поле, за последната версия
                        $lastArr = change_Log::getVerbalValue($classId, $data->rec->id, $selVerArr['last'], $allowedFieldsArr);
                    }
                    
                } else {
                    
                    // Флаг, който посочва, че няма последна версия
                    $noLast = TRUE;
                }
                
                // Обхождаме всички позволени версии
                foreach ($allowedFieldsArr as $allowedField) {
                    
                    // Вземаме първата версия
                    $first = $firstArr[$allowedField];
                    
                    // Ако няма последна версия
                    if ($noLast) {
                        
                        // Задаваме първата версия
                        $data->row->$allowedField = $first;
                    } else {
                        
                        // Вземаме последната версия
                        $last = $lastArr[$allowedField];
                        
                        // Сравняваме двата варианта
                        $newFieldVal = lib_Diff::getDiff($first, $last);
                        
                        // Добавяме pending полетата от новия запис
                        if ($first instanceof core_Et) {
                            $newFieldVal = new ET($newFieldVal);
                            foreach ((array)$first->pending as $pending) {
                                $newFieldVal->addSubstitution($pending->str, $pending->place, $pending->once, $pending->mode);
                            }
                        }
                        
                        // Добавяме pending полетата от стария запис
                        if ($last instanceof core_Et) {
                            $newFieldVal = new ET($newFieldVal);
                            foreach ((array)$last->pending as $pending) {
                                $newFieldVal->addSubstitution($pending->str, $pending->place, $pending->once, $pending->mode);
                            }
                        }
                        
                        $data->row->$allowedField = $newFieldVal;
                    }
                }
            }
        }
        
        // Вербално представяне на избраните версии
        $firstSelVerArr = change_Log::getVersionAndDateFromKey($mvc, $selVerArr['first']);
        $lastVerDocArr = change_Log::getVersionAndDateFromKey($mvc, $lastVersion);
        
        // Ако има избрана версия
        if ($selVerArr['first']) {
            
            // Добавяме в променлива
            $data->row->LastSavedVersion = $lastVerDocArr['versionStr'];
            
            // Ако е върната дата
            if ($lastVerDocArr['createdOn']) {
                $data->row->LastSavedVersionDate = dt::mysql2verbal($lastVerDocArr['createdOn'], 'd-m-y');
            }
        } else {
            
            // Добавяме в друга променлива
            $data->row->LastVersion = $lastVerDocArr['versionStr'];
            
            // Ако е върната дата
            if ($lastVerDocArr['createdOn']) {
                $data->row->LastVersionDate = dt::mysql2verbal($lastVerDocArr['createdOn'], 'd-m-y');
            }
        }
        
        // Първата избрана версия
        $data->row->FirstSelectedVersion = $firstSelVerArr['versionStr'];
        
        // Ако е върната дата
        if ($firstSelVerArr['createdOn']) {
            $data->row->FirstSelectedVersionDate = dt::mysql2verbal($firstSelVerArr['createdOn'], 'd-m-y');
        }
        
        // Ако последната версия е последния вариант
        if ($lastVersionStr && ($selVerArr['last'] == $lastVersion)) {
            
            // Последната избрана версия
            $data->row->LastSelectedVersion = $lastVerDocArr['versionStr'];
            
            // Ако е върната дата
            if ($lastVerDocArr['createdOn']) {
                $data->row->LastSelectedVersionDate = dt::mysql2verbal($lastVerDocArr['createdOn'], 'd-m-y');
            }
        } else {
            
            $lastSelVerArr = change_Log::getVersionAndDateFromKey($mvc, $selVerArr['last']);
            
            // Последната избрана версия
            $data->row->LastSelectedVersion = $lastSelVerArr['versionStr'];
            
            // Ако е върната дата
            if ($lastSelVerArr['createdOn']) {
                $data->row->LastSelectedVersionDate = dt::mysql2verbal($lastSelVerArr['createdOn'], 'd-m-y');
            }
        }
    }
    
    
    /**
     * Връща масив с всички полета, които ще се променят
     * 
     * @param core_Form $form
     * 
     * return array $allowedFieldsArr
     */
    static function getAllowedFields($form, $changableFields=array())
    {
        // Масива, който ще връщаме
        $allowedFieldsArr = array();
        
        // Обхождаме всички полета
        foreach ($form->fields as $field => $filedClass) {
            
            // Ако могат да се променят
            if ($filedClass->changable) {
                
                // Добавяме в масива
                $allowedFieldsArr[$field] = $field;
            }
        }
        
        // Преобразуваме в масив
        $changableFieldsArr = arr::make($changableFields, TRUE);
        
        // Събираме двата масива
        $allowedFieldsArr += $changableFieldsArr;
        
        return $allowedFieldsArr;
        
    }
    
    
	/**
     * Извиква се след въвеждането на данните от Request във формата ($form->rec)
     * 
     * @param core_Mvc $mvc
     * @param core_Form $form
     */
    public static function on_AfterInputEditForm($mvc, &$form)
    {
        // Ако формата е изпратена успешно
        if ($form->isSubmitted()) {
            
            // Ако редактраиме записа
            if ($id = $form->rec->id) {
                
                // Вземаме записа
                $rec = $mvc->fetch($id);
                
                // Вземаме всички, полета които могат да се променят
                $allowedFieldsArr = static::getAllowedFields($form, $mvc->changableFields);
                
                // Обхождаме полетта
                foreach ((array)$allowedFieldsArr as $field) {
                    
                    // Ако има променя
                    if ($form->rec->$field != $rec->$field) {
                        
                        // Вдигаме флага
                        $haveChange = TRUE;
                    }
                }
                
                // Ако няма промени
                if (!$haveChange) {
                    
                    // Вдигаме флага
                    $form->rec->NoChange = TRUE;
                }
            }
        }
        
        // Ако формата е изпратена успешно
        if ($form->isSubmitted()) {
            
            if (!$form->rec->id) {
                $form->rec->version = '0';
                $form->rec->subVersion = 1;
            }
        }
    }
    
    
    /**
     * Прихваща извикването на GetChangeLink.
     * Създава линк, който води до промяната на записа
     * 
     * @param object $mvc
     * @param string $res
     * @param integer $id
     * @param string $title - Ако е подаден, връща линк с иконата и титлата. Ако липсва, връща само линк с иконата.
     */
    function on_AfterGetChangeLink(&$mvc, &$res, $id, $title=FALSE)
    {
        // Ако нямаме права да редактираме, да не се показва линка
        if (!$mvc->haveRightFor('changerec', $id)) return ;
        
        // URL' то за промяна
        $changeUrl = array($mvc, 'changefields', $id, 'ret_url' => TRUE);
        
        // Иконата за промяна
        $editSbf = sbf("img/16/edit.png");
        
        // Ако е подаде заглавието
        if ($title) {
            
            // Създаваме линк с загллавието
            $attr = array();
            $attr['class'] = 'linkWithIcon';
            $attr['style'] = 'background-image:url(' . $editSbf . ');';
            
            $res = ht::createLink($title, $changeUrl, NULL, $attr); 
        } else {
            
            // Ако не е подадено заглавиет, създаваме линк с иконата
            $res = ht::createLink('<img src=' . $editSbf . ' width="12" height="12">', $changeUrl);
        }
    }
    
    
    /**
     * Изпълнява се след подготовката на ролите, които могат да изпълняват това действие.
     *
     * @param core_Mvc $mvc
     * @param string $requiredRoles
     * @param string $action
     * @param stdClass $rec
     * @param int $userId
     */
    public static function on_AfterGetRequiredRoles($mvc, &$requiredRoles, $action, $rec = NULL, $userId = NULL)
    {
        if ($rec && $action == 'changerec') {
            if (($requiredRoles != 'no_one') && (!$mvc->canChangeRec($rec))) {
                $requiredRoles = 'no_one';
            } 
        }
    }
    
    
    /**
     * Проверява дали може да се променя записа в зависимост от състоянието на документа
     * 
     * @param core_Mvc $mvc
     * @param boolean $res
     * @param string $state
     */
    function on_AfterCanChangeRec($mvc, &$res, $rec)
    {
        $res = TRUE;
        
        // Чернова и затворени документи не могат да се променят
        if ($rec->state == 'draft' || $rec->state == 'closed') {
            
            $res = FALSE;
        } 
    }
}
