<?php 


/**
 * Документ с който се сигнализара някакво несъответствие
 *
 * @category  bgerp
 * @package   support
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2013 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class support_Issues extends core_Master
{
    
    
    /**
     * За конвертиране на съществуващи MySQL таблици от предишни версии
     */
    var $oldClassName = 'issue_Document';
    
    
    /**
     * Заглавие на модела
     */
    var $title = 'Сигнали';
    
    
    /**
     * 
     */
    var $singleTitle = 'Сигнал';
    
    
    /**
     * 
     */
    var $abbr = 'Sig';
    
    
    /**
     * Кой има право да чете?
     */
    var $canRead = 'powerUser';
    
    
    /**
     * Кой има право да променя?
     */
    var $canEdit = 'powerUser';
    
    
    /**
     * Кой има право да добавя?
     */
    var $canAdd = 'powerUser';
    
    
    /**
     * Кой има право да го види?
     */
    var $canView = 'powerUser';
    
    
    /**
     * Кой може да го разглежда?
     */
    var $canList = 'ceo, admin, support';
    
    
    /**
	 * Кой може да разглежда сингъла на документите?
	 */
	var $canSingle = 'powerUser';
    
    
    /**
     * Необходими роли за оттегляне на документа
     */
    var $canReject = 'admin, support';
    
    
    /**
     * Кой има право да го изтрие?
     */
    var $canDelete = 'no_one';
    
    
    /**
     * Кой може да възлага задачата
     */
    var $canAssign = 'support, admin, ceo';
    
    
    /**
     * Поддържани интерфейси
     */
    var $interfaces = 'doc_DocumentIntf, doc_AddToFolderIntf, doc_ContragentDataIntf';
    
    
    /**
     * Кой може да променя активирани записи
     */
    var $canChangerec = 'support, admin, ceo';
    
    
    /**
     * Кой може да добавя външен сигнал?
     */
    var $canNew = 'every_one';


    /**
     * Плъгини за зареждане
     */
    var $loadList = 'support_Wrapper, doc_DocumentPlg, plg_RowTools, plg_Printing, doc_ActivatePlg, bgerp_plg_Blank, plg_Search, 
    				doc_SharablePlg, doc_AssignPlg, plg_Sorting, change_Plugin, doc_plg_BusinessDoc';

    
    /**
     * Дали може да бъде само в началото на нишка
     */
    var $onlyFirstInThread = TRUE;
    
    
    /**
     * Нов темплейт за показване
     */
    var $singleLayoutFile = 'support/tpl/SingleLayoutIssue.shtml';
    
    
    /**
     * Икона по подразбиране за единичния обект
     */
    var $singleIcon = 'img/16/support.png';

    
    /**
     * Поле за търсене
     */
    var $searchFields = 'componentId, typeId, description, title';
    
    
    /**
     * 
     */
    var $listFields = 'id, title, systemId, componentId, typeId';
    
    
    /**
     * Хипервръзка на даденото поле и поставяне на икона за индивидуален изглед пред него
     */
    var $rowToolsSingleField = 'title';
    
    
    /**
     * 
     */
    var $cloneFields = 'componentId, typeId, title, description, priority';
    
    
    /**
     * Кой има право да клонира?
     */
    protected $canClone = 'powerUser';
	
    
    /**
     * Групиране на документите
     */
    var $newBtnGroup = "10.1|Поддръжка";
	
    
	/**
     * Описание на модела (таблицата)
     */
    function description()
    {
        $this->FLD('typeId', 'key(mvc=support_IssueTypes, select=type)', 'caption=Тип, mandatory, width=100%, silent');
        $this->FLD('title', 'varchar', "caption=Заглавие, mandatory, width=100%,silent");
        $this->FLD('description', 'richtext(rows=10,bucket=Support,shareUsersRoles=support,userRolesForShare=support)', "caption=Описание, mandatory");
        $this->FLD('componentId', "key(mvc=support_Components,select=name,allowEmpty)", 'caption=Компонент, changable');
        
        $this->FLD('systemId', 'key(mvc=support_Systems, select=name)', 'caption=Система, input=hidden, silent');
        
        $this->FLD('priority', 'enum(normal=Нормален, warning=Висок, alert=Критичен)', 'caption=Приоритет');

        // Възлагане на задача (за doc_AssignPlg)
        $this->FLD('assign', 'user(roles=powerUser, allowEmpty)', 'caption=Възложено на,input=none, changable');
        
        // Споделени потребители
        $this->FLD('sharedUsers', 'userList(roles=support)', 'caption=Споделяне->Потребители');


        // Контактни данни
        $this->FLD('name', 'varchar(64)', 'caption=Данни за обратна връзка->Име, mandatory, input=none');
        $this->FLD('email', 'email', 'caption=Данни за обратна връзка->Имейл, mandatory, input=none');
        

        // Данни за компютъра на изпращача на сигнала
        $this->FLD('ip', 'ip', 'caption=Ип,input=none');
    	$this->FLD('brid', 'varchar(8)', 'caption=Браузър,input=none');
    }


    /**
     * Екшън за добавяне на запитване от нерегистрирани потребители
     */
    function act_New()
    {
    	$this->requireRightFor('new');

        if($lg = Request::get('Lg')){
    		cms_Content::setLang($lg);
    		core_Lg::push($lg);
    	}
    	
        // Подготовка на формата
        $form = $this->getForm();

        // Правим едни полета да не се показват
        
        $form->setField('priority', 'input=none');
        $form->setField('componentId', 'input=none');

        // А други полета правим да се показват
        if(!haveRole('powerUser')) {
            $form->setField('email', 'input,silent');
            $form->setField('name', 'input,silent');
            $form->setField('folderId', 'input=hidden,silent');
            $form->setField('originId', 'input=hidden,silent');
            $form->setField('id', 'input=hidden,silent');
            $form->setField('sharedUsers', 'input=none');
        }
        
        $form->setField('title', 'input=hidden');

    	// Инпут на формата
    	$form->input(NULL, 'silent');
    	
        $rec = $form->rec;
        expect($rec->systemId);
        expect($sysRec = support_Systems::fetch($rec->systemId));
        
        $allowedTypesArr = support_Systems::getAllowedFieldsArr($sysRec->id);
        
        $systemName = support_Systems::getTitleById($rec->systemId);

        $form->title = "Сигнал към екипа за поддръжка на {$systemName}";
        
        $atOpt = array();
        foreach($allowedTypesArr as $tId) {
            $tRec = support_IssueTypes::fetchField($tId);
            $atOpt[$tId] =  support_IssueTypes::getVerbal($tRec, 'type');
        }

        $form->setOptions('typeId', $atOpt);

        $form->input();
        
        $rec = &$form->rec;

        if(!haveRole('user')) {
            $brid = core_Browser::getBrid(FALSE);
            if($brid) {
                $query = $this->getQuery();
                $query->limit = 1;
                $query->orderBy('#createdOn', 'DESC');
                $lastRec = $query->fetch(array("#brid = '[#1#]'", $brid));
                if($lastRec && !$rec->name) {
                    $rec->name = $lastRec->name;
                }
                if($lastRec && !$rec->email) {
                    $rec->email = $lastRec->email;
                }
            }
        }
        
        if ($sysRec->defaultType) {
            $form->setDefault('typeId', $sysRec->defaultType);
        }
        
    	// След събмит на формата
    	if($form->isSubmitted()){

    		$rec->state = 'active';
            
            if(!haveRole('powerUser')) {
                $rec->ip   = core_Users::getRealIpAddr();
                $rec->brid = core_Browser::getBrid();
            }
    		
    		if(empty($rec->folderId)){
                $sysRec = support_Systems::fetch($rec->systemId);
    			$rec->folderId = $sysRec->folderId;
    		}
    		
    		// Запис и редирект
    		if($this->haveRightFor('new')){
    			$id = $this->save($rec);
    			
    			$cu = core_Users::getCurrent('id', FALSE);
    			
    			// Ако няма потребител, записваме в бисквитка ид-то на последното запитване
    			if(!$cu){
    				setcookie("inquiryCookie[inquiryId]", str::addHash($id, 10), time() + 2592000);
    			}
    			
    			status_Messages::newStatus(tr('Благодарим ви за сигнала'), 'success');
    			
    			return followRetUrl();
    		}
    	}
    	
    	$form->toolbar->addSbBtn('Изпрати', 'save', 'id=save, ef_icon = img/16/ticket.png,title=Изпращане на сигнала');
        if(count(getRetUrl())) {
            $form->toolbar->addBtn('Отказ', getRetUrl(),  'id=cancel, ef_icon = img/16/close16.png,title=Oтказ');
        }
        $tpl = $form->renderHtml();
    	
        // Поставяме шаблона за външен изглед
		Mode::set('wrapper', 'cms_Page');
		
		if($lg){
			core_Lg::pop();
		}
		
    	return $tpl;
    }


    static function on_AfterrecToVerbal($mvc, $row, $rec, $fields = array()) 
    {
        //$row->browser = core_Browser::getBrowserType($rec->browser);
    }



    
    
    /**
     * Проверка дали нов документ може да бъде добавен в
     * посочената папка като начало на нишка
     *
     * @param int $folderId - id на папката
     * @return boolean
     */
    public static function canAddToFolder($folderId)
    {
        // Името на класа
        $coverClassName = doc_Folders::fetchCoverClassName($folderId);
        
        // Ако не support_systems, не може да се добави
        if (strtolower($coverClassName) != 'support_systems') return FALSE;
    }
    
    
    /**
     * 
     */
    function getSystemId($rec)
    {   
        $systemId = FALSE;
  
        if ($rec->systemId) {

            $systemId = $rec->systemId;

        // Ако има компонент
        } elseif($rec->componentId) {
            
            // systemId на съответния компонент
            $systemId = support_Components::fetchField($rec->componentId, 'systemId');   
            
        } elseif ($rec->folderId) {
            
            // Вземаме ковъра на папката
            $cover = doc_Folders::getCover($rec->folderId);
            // Ако ковъра е support_Systems
            if ($cover->className == 'support_Systems') {
             
                // Използваме id' то
                $systemId = $cover->that;
            }
        }
 
        return $systemId;
    }
    
    
    /**
     * 
     */
    function on_AfterPrepareListRows($mvc, $res, $data) {
        
        // Обхождаме записите
        foreach ((array)$data->recs as $key => $rec) {
            
            // Ако има id на система
             $rec->systemId = $mvc->getSystemId($rec);
             
            // Вземаме id' то на папката
            $folderId = $rec->folderId;
                
            // Ако нямамем права за папката, прескачаме
            if (!doc_Folders::haveRightFor('single', $folderId)) {
                continue;
            }
                
            // Линк към папката
            $folderLink = ht::createLink($mvc->getVerbal($rec, 'folderId'), array('doc_Threads', 'list', 'folderId' => $folderId));
                 
            // Заместваме името на системата с линк към папката
            $data->rows[$key]->systemId = $folderLink;
        }
    }
    
    
	/**
     * Реализация  на интерфейсния метод ::getThreadState()
     * Добавянето на сигнал отваря треда
     */
    static function getThreadState($id)
    {
        
        return 'opened';
    }
    
    
    /**
     * 
     */
    public static function on_AfterPrepareEditForm($mvc, $data)
    {
        // Нормален приоритет по подразбиране
        $data->form->setDefault('priority', 'normal');
        
        // Вземаме systemId' то на документа от URL' то
        $systemId = Request::get('systemId', 'key(mvc=support_Systems, select=name)');
        
        // Опитваме се да вземеме return ult' то
        $retUrl = getRetUrl();

        // Ако има systemId
        if ($systemId) {
            
            // Вземаме записите
            $iRec = support_Systems::fetch($systemId);
            
            // Ако имаме права за single до папката
            if ($iRec->folderId && doc_Folders::haveRightFor('single', $iRec->folderId)) {    
                
                // Форсираме създаването на папката
                $folderId = support_Systems::forceCoverAndFolder($iRec);
                
                // Задаваме id' то на папката
                $data->form->rec->folderId = $folderId;    
            }
        } 
        
        // Ако все още не сме определили папката
        if (!$folderId) {
            
            // Ако няма подадено systemId, вземаме id' то на папката по подразбиране
            $folderId = $data->form->rec->folderId;
        }
        
        // Записите за класа, който се явява корица
        $coverClassRec = doc_Folders::fetch($folderId);
        
        // Задаваме systemId да е id' то на ковъра
        $systemId = $coverClassRec->coverId;
        
        // Всички системи, които наследяваме
        $allSystemsArr = support_Systems::getSystems($systemId);

        // Премахваме текущатата
        unset($allSystemsArr[$systemId]);
        
        // Извличаме всички компоненти, със съответното systemId или прототип
        $query = support_Components::getQuery();
        
        $query->where("#systemId = '{$systemId}'");
        
        // Обхождаме всики наследени системи
        foreach ($allSystemsArr as $allSystemId) {
            
            // Добавяме OR
            $query->orWhere("#systemId = '{$allSystemId}'");
        }
        
        $components = array();
        
        // Обхождаме всички открити резултати
        while ($rec = $query->fetch()) {
            
            // Създаваме масив с компонентите
            $components[$rec->id] = support_Components::getVerbal($rec, 'name');
        }

        // Ако няма въведен компонент
        if (!$components) {
            
            // Добавяме съобщение за грешка
            status_Messages::newStatus(tr('Няма въведен компонент на системата.'));
            
            // Ако има права за добавяне на компонент
            if (support_Components::haveRightFor('add')) {
                
                // Линк за препращаме към станицата за добавяне на компонент
                $redirectArr = array('support_Components', 'add', 'systemId' => $systemId, 'ret_url' => $retUrl);    
            } else {
                
                // Ако нямаме права, препащаме където сочи return URL' то
                $redirectArr = $retUrl;
            }
            
            // Препащаме
            return redirect($redirectArr);
        }
        
        // Премахваме повтарящите се
        $components = array_unique($components);
        
        // Променяме съдържанието на полето компоненти с определения от нас масив
        $data->form->setOptions('componentId', $components);
        
        // Разрешените типове за съответната система
        $allowedTypesArr = support_Systems::getAllowedFieldsArr($systemId);

        // Обхождаме масива с всички разрешени типове
        foreach ($allowedTypesArr as $allowedType) {
            
            // Добавяме в масива вербалната стойност на рарешените типове
            $types[$allowedType] = support_IssueTypes::getVerbal($allowedType, 'type');
        }
        
        // Променяме съдържанието на полето тип с определения от нас масив, за да се показват само избраните
        $data->form->setOptions('typeId', $types);

        // Ако няма роля support
        if (!haveRole('support')) {
            
            // Скриваме полето за споделяне
            $data->form->setField('sharedUsers', 'input=none');
        }
        
        if ($systemId) {
            $sysRec = support_Systems::fetch($systemId);
            if ($sysRec->defaultType) {
                $data->form->setDefault('typeId', $sysRec->defaultType);
            }
        }
    }
    
    
    /**
     * Затваря сигналите в даден тред
     * 
     * @param doc_Threads $threadId - id на нишката
     */
    static function closeIssue($threadId)
    {
        // Вземаме всички сингнали от нишката 
        // По сегашната логика трябва да е само един
        $query = static::getQuery();
        $query->where("#threadId = '{$threadId}'");
        
        // Обхождаме записите
        while ($rec = $query->fetch()) {
            
            // Сменяме състоянието на нишката на затворена
            $rec->state = 'closed';
            static::save($rec);
        }
    }
    
    
    /**
     * Подготовка на форма за филтър на списъчен изглед
     */
    static function on_AfterPrepareListFilter($mvc, &$data)
    {
        // Подреждаме по дата по - новите по - напред
        $data->query->orderBy('createdOn', 'DESC');
        
        // Подреждаме сиганлите активните отпред, затворените отзад а другите по между им
        $data->query->XPR('orderByState', 'int', "(CASE #state WHEN 'active' THEN 1 WHEN 'closed' THEN 3 ELSE 2 END)");
        $data->query->orderBy('orderByState');
        
        // Задаваме на полета да имат възможност за задаване на празна стойност
        $data->listFilter->getField('systemId')->type->params['allowEmpty'] = TRUE;
        $data->listFilter->getField('componentId')->type->params['allowEmpty'] = TRUE;
         
        // Добавяме функционално поле за отговорници
        $data->listFilter->FNC('maintainers', 'type_Users(rolesForAll=support|ceo|admin)', 'caption=Отговорник,input,silent', array('attr' => array('onchange' => 'this.form.submit();')));
        
        // Кои полета да се показват
        $data->listFilter->showFields = 'systemId, componentId, maintainers';
        
        // Добавяме бутон за филтриране
        $data->listFilter->toolbar->addSbBtn('Филтрирай', 'default', 'id=filter', 'ef_icon = img/16/funnel.png');
        
        // Да се показват в хоризонтална подредба
        $data->listFilter->view = 'horizontal';
        
        // Вземаме стойността по подразбиране, която може да се покаже
        $default = $data->listFilter->getField('maintainers')->type->fitInDomain('all_users');
        
        // Задаваме стойността по подразбиране
        $data->listFilter->setDefault('maintainers', $default);

        // Полетата да не са задължителни и да се субмитва формата при промяната им
        $data->listFilter->setField('componentId', array('attr' => array('onchange' => 'this.form.submit();')));
        $data->listFilter->setField('componentId', array('mandatory' => FALSE));
        $data->listFilter->setField('systemId', array('attr' => array('onchange' => 'this.form.submit();')));
        $data->listFilter->setField('systemId', array('mandatory' => FALSE));
        
        // Инпутваме
        $data->listFilter->input();
        
        // id' то на системата
        $systemId = $data->listFilter->rec->systemId;
        
        // Ако е избрана система
        if ($systemId) {
            
            // Добавяме външно поле за търсене
            $data->query->EXT("systemId", 'support_Components', "externalName=systemId");

            // Да се показват само сигнали от избраната система
            $data->query->where("#systemId = '{$systemId}'");
            $data->query->where("#componentId = `support_components`.`id`");
        }
        
        // Вземаме всички компоненти от избраната система
        $componentsArr = support_Components::getSystemsArr($systemId);
        
        // Ако има компоненти
        if (count($componentsArr)) {
            
            // Задаваме ги да се показват те
            $data->listFilter->setOptions('componentId', $componentsArr);    
        } else {
            
            // Добавяме празен стринг, за да не се покажат всичките записи 
            $data->listFilter->setOptions('componentId', array('' => ''));
        }
        
        // id' то на компонента
        $componentId = $data->listFilter->rec->componentId;
        
        // Ако е избран компонент
        if ($componentId) {
            
            // Масив с id' тата на еднаквите компоненти по име
            $sameComponentsArr = support_Components::getSame($componentId);
            
            // Обхождаме масива
            foreach ($sameComponentsArr as $sameVal) {
                
                // Ако го има в избраните
                if (isset($componentsArr[$sameVal])) {

                    // Добавяме във where
                    $data->query->orWhereArr('componentId', $sameComponentsArr);  
                    
                    // Прекъсваме по нататъшното изпълнение
                    break;
                }
            }
        }
        
        // Отговорници
        $maintainers = $data->listFilter->rec->maintainers;

        // Очакваме да има избран
        expect($maintainers, 'Няма избран отговорник.');  
            
        // Ако не е избран всички потребители
        if ($maintainers != 'all_users') {
            
            // Ако не са избрани всички потребители
            if (strpos($maintainers, '|-1|') === FALSE) {
                
                // Всички споделени потребители или присъединени(възложен на)
                // Не търси по създадено от и възложено от
                
                // Търсим по споделените потребители
                $data->query->likeKeylist("sharedUsers", $maintainers);
                
                // Масив с избрани потребители
                $maintainersArr = type_Keylist::toArray($maintainers);
                
                // Търсим по възложените потребители
                $data->query->orWhereArr("assign", $maintainersArr, TRUE);
            }        
        }
    }

    
	/**
     * Интерфейсен метод на doc_DocumentInterface
     */
    function getDocumentRow($id)
    {
        $rec = $this->fetch($id);
     
        $row = new stdClass();
        
        // Типа
        $type = str::mbUcfirst(self::getVerbal($rec, 'typeId'));

        // Компонента
        $component = self::getVerbal($rec, 'componentId');
        
        // Добавяме типа към заглавието
        $row->title =  "{$type}: " . $this->getVerbal($rec, 'title');
        
        // Ако е възложено на някой
        if ($rec->assign) {
            
            // В заглавието добавяме потребителя
            $row->subTitle = $this->getVerbal($rec, 'assign');   
        }
        
        if ($component) {
            if ($row->subTitle) {
                $row->subTitle .= ", ";
            }
            $row->subTitle .= "{$component}";
        }

        if($row->authorId = $rec->createdBy) {
            $row->author = $this->getVerbal($rec, 'createdBy');
        } elseif($rec->email && $rec->name) {
            $row->authorEmail = $rec->email;
            $row->author = $this->getVerbal($rec, 'name');
        }
        
        $row->state = $rec->state;
        
        $row->recTitle = $rec->title;
        
        return $row;
    }
    
    
    /**
     * Потребителя, на когото е възложена задачата
     */
    function on_AfterGetShared($mvc, &$shared, $id)
    {
        // Ако има споделени потребители връщамес
        if ($shared) return ;
        
        // Вземаме записа
        $rec = $mvc->fetch($id);
        
        // Ако не е активен, връщаме
        if ($rec->state != 'active') return ;
        
        // Ако има компонент
        if ($rec->componentId) {
            
            // Отговорниците на компонента
            $maintainers = support_Components::fetchField($rec->componentId, 'maintainers');

            // Към отговорниците да не се показва създателя
            $maintainers = keylist::removeKey($maintainers, $rec->createdBy);
            
            // Добавяме към споделените
            $shared = keylist::merge($maintainers, $shared);
        }
    }
    
    
    /**
     * 
     */
    static function on_AfterPrepareListToolbar($mvc, &$data)
    {
        // Вземаме запитване към системата
        $query = support_Systems::getQuery();
        
        // Ако няма система
        if (!$query->count()) {
            
            // Премахваме бутона за добанвяне на нов запис в листовия изглед
            $data->toolbar->removeBtn('btnAdd');
        }
    }
    
    
	/**
     * В кои корици може да се вкарва документа
     * 
     * @return array - интерфейси, които трябва да имат кориците
     */
    public static function getAllowedFolders()
    {
        
    	return array('support_IssueIntf');
    }
    
    
	/**
     * След подготовка на тулбара на единичен изглед.
	 * 
	 * @param core_Mvc $mvc
	 * @param object $data
	 */
    static function on_AfterPrepareSingleToolbar($mvc, &$data)
    {
        // Ако документа е активен
    	if($data->rec->state == 'active'){
    		
    	    // URL за бутоните
    	    $url = array(
    	                    'Ctr' => $mvc,
							'Act' => 'add',
    						'threadId' => $data->rec->threadId,
    						'ret_url' => TRUE);
    	    
    	    // Добавя бутон за добавяне на коригиращи действия
    	    $Correction = cls::get('support_Corrections');
    	    $url['Ctr'] = $Correction;
    		if($Correction->haveRightFor('add')){
    			$data->toolbar->addBtn('Корекция', $url, "ef_icon={$Correction->singleIcon}, row=2, title = Създаване на документ Коригиращи действия");
    		}
    		
    		// Добавя бутон за добавяне на превантивни действия
    	    $Prevention = cls::get('support_Preventions');
    	    $url['Ctr'] = $Prevention;
    	    if($Prevention->haveRightFor('add')){
    			$data->toolbar->addBtn('Превенция', $url, "ef_icon={$Prevention->singleIcon}, row=2, title = Създаване на документ Превантивни действия");
    		}
    		
    		// // Добавя бутон за добавяне на оценка на сигнала
    		$Rating = cls::get('support_Ratings');
    	    $url['Ctr'] = $Rating;
    	    if($Rating->haveRightFor('add')){
    			$data->toolbar->addBtn('Оценка', $url, "ef_icon={$Rating->singleIcon}, row=2, title = Създаване на документ Оценка на сигнал");
    		}
            
    		// Добавя бутон за добавяне на резолюция на сигнала
    		$Resolution = cls::get('support_Resolutions');
    	    $url['Ctr'] = $Resolution;
    	    if($Resolution->haveRightFor('add')){
    			$data->toolbar->addBtn('Резолюция', $url, "ef_icon={$Resolution->singleIcon}, row=2 , title = Създаване на документ Резолюция на сигнал");
    		}
    	}
    }
    
    
    /**
     * Интерфейсен метод
     * 
     * @param integer $id
     * 
     * @return object
     * @see doc_ContragentDataIntf
     */
    public static function getContragentData($id)
    {
        if (!$id) return ;
        $rec = self::fetch($id);
        
        $contrData = new stdClass();
        
        if ($rec->createdBy > 0) {
            $personId = crm_Profiles::fetchField("#userId = '{$rec->createdBy}'", 'personId');
            $contrData = crm_Persons::getContragentData($personId);
        } else {
            $contrData->email = $rec->email;
            $contrData->person = $rec->name;
        }
        
        return $contrData;
    }
    
    /**
     * Да се показвали бърз бутон за създаване на документа в папка
     */
    public function mustShowButton($folderRec, $userId = NULL)
    {
    	$Cover = doc_Folders::getCover($folderRec->id);
    	
    	// Показваме бутона само ако корицата на папката поддържа интерфейса 'support_IssueIntf'
    	return ($Cover->haveInterface('support_IssueIntf')) ? TRUE : FALSE;
    }
}
