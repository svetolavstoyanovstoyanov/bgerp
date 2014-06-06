<?php



/**
 * Документ 'Покупка'
 *
 *
 * @category  bgerp
 * @package   purchase
 * @author    Stefan Stefanov <stefan.bg@gmail.com> и Ivelin Dimov<ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @title     Покупки
 */
class purchase_Purchases extends core_Master
{
    
    
    /**
     * Заглавие
     */
    public $title = 'Покупки';


    /**
     * Поддържани интерфейси
     */
    public $interfaces = 'doc_DocumentIntf, email_DocumentIntf, doc_ContragentDataIntf, bgerp_DealAggregatorIntf, bgerp_DealIntf, acc_TransactionSourceIntf=purchase_TransactionSourceImpl';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'plg_RowTools, purchase_Wrapper, plg_Sorting, plg_Printing, doc_plg_TplManager, acc_plg_DealsChooseOperation, doc_DocumentPlg, acc_plg_Contable,
				        plg_ExportCsv, cond_plg_DefaultValues, doc_plg_HidePrices, doc_SharablePlg,
				        doc_EmailCreatePlg, bgerp_plg_Blank, doc_plg_BusinessDoc, acc_plg_DocumentSummary, plg_Search';
    
    
    /**
     * За конвертиране на съществуващи MySQL таблици от предишни версии
     */
    public $oldClassName = 'purchase_Requests';
    
    
    /**
     * Абревиатура
     */
    public $abbr = 'Pur';
    
    
    /**
     * Кой има право да чете?
     */
    public $canRead = 'ceo, purchase';
    
    
    /**
     * Кой може да го активира?
     */
    public $canConto = 'ceo,purchase,acc';
    
    
    /**
	 * Кой може да го разглежда?
	 */
	public $canList = 'ceo, purchase';


	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	public $canSingle = 'ceo, purchase';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'ceo, purchase';
    
    
    /**
     * Кой има право да добавя?
     */
    public $canAdd = 'ceo, purchase';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = 'id, valior, folderId, currencyId=Валута, amountDeal, amountDelivered, amountPaid,dealerId,paymentState,createdOn, createdBy';


    /**
     * Детайла, на модела
     */
    public $details = 'purchase_PurchasesDetails';


    /**
     * Заглавие в единствено число
     */
    public $singleTitle = 'Покупка';


    /**
     * Лейаут на единичния изглед 
     */
    public $singleLayoutFile = 'purchase/tpl/SingleLayoutPurchase.shtml';
    
    
    /**
     * Документа покупка може да бъде само начало на нишка
     */
    public $onlyFirstInThread = TRUE;
    
    
    /**
     * Групиране на документите
     */
    public $newBtnGroup = "4.2|Логистика";
    
    
    /**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     */
    public $searchFields = 'deliveryTermId, deliveryLocationId, deliveryTime, shipmentStoreId, paymentMethodId,
    					 currencyId, bankAccountId, caseId, dealerId, folderId';
    
    
    /**
     * Полета свързани с цени
     */
    public $priceFields = 'amountDeal,amountDelivered,amountPaid,amountInvoiced,amountToPay,amountToDeliver,amountToInvoice';
    
    
    /**
     * Стратегии за дефолт стойностти
     */
    public static $defaultStrategies = array(
    
    	'deliveryTermId'     => 'clientCondition|lastDocUser|lastDoc',
    	'paymentMethodId'    => 'clientCondition|lastDocUser|lastDoc',
    	'currencyId'         => 'lastDocUser|lastDoc|CoverMethod',
    	'bankAccountId'      => 'lastDocUser|lastDoc',
    	'makeInvoice'        => 'lastDocUser|lastDoc',
    	'dealerId'           => 'lastDocUser|lastDoc|defMethod',
    	'deliveryLocationId' => 'lastDocUser|lastDoc',
    	'chargeVat'			 => 'lastDocUser|lastDoc',
    	'template' 			 => 'lastDocUser|lastDoc|LastDocSameCuntry',
    );
    
    
    /**
     * Опашка от записи за записване в on_Shutdown
     */
    protected $updated = array();
    
    
    /**
     * Поле в което се замества шаблона от doc_TplManager
     */
    public $templateFld = 'SINGLE_CONTENT';
    
    
    /**
     * Позволени операции на последващите платежни документи
     */
    public $allowedPaymentOperations = array(
    		'case2supplierAdvance' => array('title' => 'Авансово плащане към Доставчик', 'debit' => '402', 'credit' => '501'),
    		'bank2supplierAdvance' => array('title' => 'Авансово плащане към Доставчик', 'debit' => '402', 'credit' => '503'),
    		'case2supplier' => array('title' => 'Плащане към Доставчик', 'debit' => '401', 'credit' => '501'),
    		'bank2supplier' => array('title' => 'Плащане към Доставчик', 'debit' => '401', 'credit' => '503'),
    		'supplier2case' => array('title' => 'Връщане от Доставчик', 'debit' => '501', 'credit' => '401'),
    		'supplier2bank' => array('title' => 'Връщане от Доставчик', 'debit' => '503', 'credit' => '401'),
    		'supplierAdvance2case' => array('title' => 'Връщане на аванс от Доставчик', 'debit' => '501', 'credit' => '402'),
    		'supplierAdvance2bank' => array('title' => 'Връщане на аванс от Доставчик', 'debit' => '503', 'credit' => '402'),
    		'debitDeals'           => array('title' => 'Прихващане на вземания', 'debit' => '*', 'credit' => '401'),
    		'creditDeals'          => array('title' => 'Прихващане на задължение', 'debit' => '401', 'credit' => '*'),
    );
    
    
    /**
     * Описание на модела (таблицата)
     */
    function description()
    {
        $this->FLD('valior', 'date', 'caption=Дата, mandatory,oldFieldName=date');
        
        $this->FLD('amountDeal', 'double(decimals=2)', 'caption=Стойности->Поръчано,input=none,summary=amount'); // Сумата на договорената стока
        $this->FLD('amountDelivered', 'double(decimals=2)', 'caption=Стойности->Доставено,input=none,summary=amount'); // Сумата на доставената стока
        $this->FLD('amountPaid', 'double(decimals=2)', 'caption=Стойности->Платено,input=none,summary=amount'); // Сумата която е платена
        $this->FLD('amountInvoiced', 'double(decimals=2)', 'caption=Стойности->Фактурирано,input=none,summary=amount'); // Сумата която е фактурирана
        $this->FLD('amountVat', 'double(decimals=2)', 'input=none');
        $this->FLD('amountDiscount', 'double(decimals=2)', 'input=none');
        
        // Контрагент
        $this->FLD('contragentClassId', 'class(interface=crm_ContragentAccRegIntf)', 'input=hidden,caption=Доставчик');
        $this->FLD('contragentId', 'int', 'input=hidden');
        
        // Доставка
        $this->FLD('deliveryTermId', 'key(mvc=cond_DeliveryTerms,select=codeName,allowEmpty)', 'caption=Доставка->Условие,salecondSysId=deliveryTermPurchase');
        $this->FLD('deliveryLocationId', 'key(mvc=crm_Locations, select=title)', 'caption=Доставка->От обект,silent');
        $this->FLD('deliveryTime', 'datetime', 'caption=Доставка->Срок до');
        $this->FLD('shipmentStoreId', 'key(mvc=store_Stores,select=name,allowEmpty)', 'caption=Доставка->До склад,oldClassName=storeId');
        
        // Плащане
        $this->FLD('paymentMethodId', 'key(mvc=cond_PaymentMethods,select=description,allowEmpty)', 'caption=Плащане->Начин,salecondSysId=paymentMethodPurchase');
        $this->FLD('currencyId', 'customKey(mvc=currency_Currencies,key=code,select=code,allowEmpty)', 'caption=Плащане->Валута');
        $this->FLD('currencyRate', 'double(decimals=2)', 'caption=Плащане->Курс');
        $this->FLD('bankAccountId', 'iban_Type(64)', 'caption=Плащане->Към банк. сметка');
        $this->FLD('caseId', 'key(mvc=cash_Cases,select=name,allowEmpty)', 'caption=Плащане->От каса');
        
        // Наш персонал
        $this->FLD('dealerId', 'user(rolesForAll=purchase|ceo,allowEmpty,roles=ceo|purchase)', 'caption=Наш персонал->Закупчик');

        // Допълнително
        $this->FLD('note', 'text(rows=4)', 'caption=Допълнително->Бележки,width=100%', array('attr' => array('rows' => 3)));
    	$this->FLD('chargeVat', 'enum(yes=Включено, separate=Отделно, exempt=Oсвободено, no=Без начисляване)', 'caption=Допълнително->ДДС');
        $this->FLD('makeInvoice', 'enum(yes=Да,no=Не,monthend=Периодично)', 'caption=Допълнително->Фактуриране,maxRadio=3,columns=3');
        
    	$this->FLD('state', 
            'enum(draft=Чернова, active=Активиран, rejected=Оттеглен, closed=Затворена)', 
            'caption=Статус, input=none'
        );
        
        $this->FLD('paymentState', 'enum(pending=Чакащо,overdue=Просроченo,paid=Платенo)', 'caption=Плащане, input=none, notNull, default=pending');
    }
    
    
    /**
     * Преди показване на форма за добавяне/промяна
     */
    public static function on_AfterPrepareEditForm($mvc, &$data)
    {
        // Задаване на стойности на полетата на формата по подразбиране
        $form = &$data->form;
        $form->setDefault('valior', dt::now());
        
        $form->setDefault('contragentClassId', doc_Folders::fetchCoverClassId($form->rec->folderId));
        $form->setDefault('contragentId', doc_Folders::fetchCoverId($form->rec->folderId));
        $form->setSuggestions('bankAccountId', bank_Accounts::getContragentIbans($form->rec->contragentId, $form->rec->contragentClassId));
        $form->setDefault('caseId', cash_Cases::getCurrent('id', FALSE));
        $form->setDefault('shipmentStoreId', store_Stores::getCurrent('id', FALSE));
        
        
        if (empty($data->form->rec->makeInvoice)) {
            $form->setDefault('makeInvoice', 'yes');
        }
        
        // Поле за избор на локация - само локациите на контрагента по покупката
        $locations = array('' => '') + crm_Locations::getContragentOptions($form->rec->contragentClassId, $form->rec->contragentId);
        $form->setOptions('deliveryLocationId', $locations);
        
        // Начисляване на ДДС по подразбиране
        $contragentRef = new core_ObjectReference($form->rec->contragentClassId, $form->rec->contragentId);
        $form->setDefault('chargeVat', $contragentRef->shouldChargeVat() ? 'yes' : 'export');
        
        if ($form->rec->id) {
        	
        	// Не може да се сменя ДДС-то ако има вече детайли
        	if($mvc->purchase_PurchasesDetails->fetch("#requestId = {$form->rec->id}")){
        		foreach (array('chargeVat', 'currencyRate', 'currencyId', 'deliveryTermId') as $fld){
        			$form->setReadOnly($fld);
        		}
        	}
        }
        
        $form->setDefault('currencyId', acc_Periods::getBaseCurrencyCode($form->rec->valior));
        $form->addAttr('currencyId', array('onchange' => "document.forms['{$data->form->formAttr['id']}'].elements['currencyRate'].value ='';"));
    }

    
	/**
     * Извиква се след въвеждането на данните от Request във формата
     */
    public static function on_AfterInputEditForm(core_Mvc $mvc, core_Form $form)
    { 
    	if($form->isSubmitted()){
	    	if(empty($form->rec->currencyRate)){
				 $form->rec->currencyRate = round(currency_CurrencyRates::getRate($form->rec->date, $form->rec->currencyId, NULL), 4);
			} else {
				if($msg = currency_CurrencyRates::hasDeviation($form->rec->currencyRate, $form->rec->valior, $form->rec->currencyId, NULL)){
			    	$form->setWarning('currencyRate', $msg);
				}
			}
			
			$form->rec->paymentState = 'pending';
    	}
    }
    
    
    /**
     * След подготовка на тулбара на единичен изглед
     */
    static function on_AfterPrepareSingleToolbar($mvc, &$data)
    {
    	$rec = &$data->rec;
    	$diffAmount = $rec->amountPaid - $rec->amountDelivered;
    	if($rec->state == 'active'){
    		$closeArr = array('purchase_ClosedDeals', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE);
    		
    		if(purchase_ClosedDeals::haveRightFor('add', (object)array('threadId' => $rec->threadId))){
	    		$data->toolbar->addBtn('Приключване', $closeArr, "ef_icon=img/16/closeDeal.png,title=Приключване на покупката");
	    	} else {
	    		
	    		// Ако разликата е над допустимата но потребителя има права 'purchase', той вижда бутона но не може да го използва
	    		if(!purchase_ClosedDeals::isPurchaseDiffAllowed($rec) && haveRole('purchase')){
	    			$data->toolbar->addBtn('Приключване', $closeArr, "ef_icon=img/16/closeDeal.png,title=Приключване на покупката,error=Нямате право да приключите покупка с разлика над допустимото");
	    		}
	    	}
    		
	    	if (store_Receipts::haveRightFor('add', (object)array('threadId' => $rec->threadId))) {
	    		$receiptUrl = array('store_Receipts', 'add', 'originId' => $data->rec->containerId, 'ret_url' => true);
	            $data->toolbar->addBtn('Засклаждане', $receiptUrl, 'ef_icon = img/16/shipment.png,title=Засклаждане на артикулите в склада,order=9.21');
	        }
	    	
    		if(store_Receipts::haveRightFor('add', (object)array('threadId' => $rec->threadId))) {
    			$serviceUrl = array('purchase_Services', 'add', 'originId' => $data->rec->containerId, 'ret_url' => true);
	            $data->toolbar->addBtn('Приемане', $serviceUrl, 'ef_icon = img/16/shipment.png,title=Покупка на услуги,order=9.22');
	        }
	        
    		if(cash_Pko::haveRightFor('add')){
		    	$data->toolbar->addBtn("РКО", array('cash_Rko', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE), 'ef_icon=img/16/money_delete.png,title=Създаване на нов разходен касов ордер');
		    }
		    
    		if(bank_IncomeDocuments::haveRightFor('add')){
		    	$data->toolbar->addBtn("РБД", array('bank_SpendingDocuments', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE), 'ef_icon=img/16/bank_rem.png,title=Създаване на нов разходен банков документ');
		    }
		    
    		// Ако експедирането е на момента се добавя бутон за нова фактура
	        $actions = type_Set::toArray($rec->contoActions);
	    	
	        if(purchase_Invoices::haveRightFor('add', (object)array('threadId' => $rec->threadId))){
	    		$data->toolbar->addBtn("Вх. фактура", array('purchase_Invoices', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE), 'ef_icon=img/16/invoice.png,title=Създаване на входяща фактура,order=9.9993');
		    }
		}
    	
    	if(haveRole('debug')){
    		$data->toolbar->addBtn("Бизнес инфо", array($mvc, 'AggregateDealInfo', $data->rec->id), 'ef_icon=img/16/bug.png,title=Дебъг,row=2');
    	}
    }
    
    
	/**
     * Подготвя данните (в обекта $data) необходими за единичния изглед
     */
    public function prepareSingle_($data)
    {
    	parent::prepareSingle_($data);
    	
    	$rec = &$data->rec;
    	if(empty($data->noTotal)){
    		$data->summary = price_Helper::prepareSummary($rec->_total, $rec->valior, $rec->currencyRate, $rec->currencyId, $rec->chargeVat, FALSE, $rec->tplLang);
    		$data->row = (object)((array)$data->row + (array)$data->summary);
    	
    		if($rec->paymentMethodId) {
    			$total = $rec->_total->amount- $rec->_total->discount;
    			cond_PaymentMethods::preparePaymentPlan($data, $rec->paymentMethodId, $total, $rec->valior, $rec->currencyId);
    		}
    	}
    }
    
    
    /**
     * Връща разбираемо за човека заглавие, отговарящо на записа
     */
    static function getRecTitle($rec, $escaped = TRUE)
    {
         // Името на шаблона е и име на документа
        $templateId = static::getTemplate($rec);
        $templateName = doc_TplManager::getTitleById($templateId);
    	
    	return "{$templateName} №{$rec->id}";
    }
    
    
    /**
     * Помощен метод за определяне на закупчик по подразбиране.
     *
     * Правило за определяне: първия, който има права за създаване на покупки от списъка:
     *
     *  1/ Отговорника на папката на контрагента
     *  2/ Текущият потребител
     *
     *  Ако никой от тях няма права за създаване - резултатът е NULL
     *
     * @param stdClass $rec запис на модела purchase_Purchases
     * @return int|NULL user(roles=purchase)
     */
    public static function getDefaultDealerId($rec)
    {
        expect($rec->folderId);
    
        // Отговорника на папката на контрагента ...
        $inChargeUserId = doc_Folders::fetchField($rec->folderId, 'inCharge');
        if (self::haveRightFor('add', NULL, $inChargeUserId)) {
            // ... има право да създава покупки - той става закупчик по подразбиране.
            return $inChargeUserId;
        }
    
        // Текущия потребител ...
        $currentUserId = core_Users::getCurrent('id');
        if (self::haveRightFor('add', NULL, $currentUserId)) {
            // ... има право да създава покупки
            return $currentUserId;
        }
    
        return NULL;
    }
    

    /**
     * Може ли документ-покупка да се добави в посочената папка?
     * Документи-покупка могат да се добавят само в папки с корица контрагент.
     *
     * @param $folderId int ид на папката
     * @return boolean
     */
    public static function canAddToFolder($folderId)
    {
        $coverClass = doc_Folders::fetchCoverClassName($folderId);
    
        return cls::haveInterface('doc_ContragentDataIntf', $coverClass);
    }
    
    
	/**
     * Връща подзаглавието на документа във вида "Дост: ХХХ(ууу), Плат ХХХ(ууу), Факт: ХХХ(ууу)"
     * @param stdClass $rec - запис от модела
     * @return string $subTitle - подзаглавието
     */
    private function getSubTitle($rec)
    {
    	$fields = $this->selectFields();
    	$fields['-single'] = TRUE;
    	$row = $this->recToVerbal($rec, $fields);
    	
        $subTitle = "Дост: " . (($row->amountDelivered) ? $row->amountDelivered : 0) . "({$row->amountToDeliver})";
		$subTitle .= ", Плат: " . (($row->amountPaid) ? $row->amountPaid : 0) . "({$row->amountToPay})";
        if($rec->makeInvoice != 'no'){
        	$subTitle .= ", Факт: " . (($row->amountInvoiced) ? $row->amountInvoiced : 0) . "({$row->amountToInvoice})";
        }
        
        return $subTitle;
    }
    
    
    /**
     * @param int $id key(mvc=sales_Sales)
     * @see doc_DocumentIntf::getDocumentRow()
     */
    public function getDocumentRow($id)
    {
        expect($rec = $this->fetch($id));
        $title = $this->getRecTitle($rec);
        
        $row = (object)array(
            'title'    => $title,
        	'subTitle' => $this->getSubTitle($rec),
            'authorId' => $rec->createdBy,
            'author'   => $this->getVerbal($rec, 'createdBy'),
            'state'    => $rec->state,
            'recTitle' => $title,
        );
    
        return $row;
    }
    

    /**
     * След преобразуване на записа в четим за хора вид.
     *
     * @param core_Mvc $mvc
     * @param stdClass $row Това ще се покаже
     * @param stdClass $rec Това е записа в машинно представяне
     */
    public static function on_AfterRecToVerbal($mvc, &$row, $rec, $fields = array())
    {
    	$amountType = $mvc->getField('amountDeal')->type;
		$rec->amountToDeliver = $rec->amountDeal - $rec->amountDelivered;
		$rec->amountToPay = $rec->amountDelivered - $rec->amountPaid;
		$rec->amountToInvoice = $rec->amountDelivered - $rec->amountInvoiced;
		
    	foreach (array('Deal', 'Paid', 'Delivered', 'Invoiced', 'ToPay', 'ToDeliver', 'ToInvoice') as $amnt) {
            if ($rec->{"amount{$amnt}"} == 0) {
                $row->{"amount{$amnt}"} = '<span class="quiet">0,00</span>';
            } else {
            	$value = $rec->{"amount{$amnt}"} / $rec->currencyRate;
				$row->{"amount{$amnt}"} = $amountType->toVerbal($value);
            }
        }
        
    	foreach (array('ToPay', 'ToDeliver', 'ToInvoice') as $amnt){
        	$color = ($rec->{"amount{$amnt}"} < 0) ? 'red' : 'green';
        	$row->{"amount{$amnt}"} = "<span style='color:{$color}'>{$row->{"amount{$amnt}"}}</span>";
        }
        
    	if($rec->paymentState == 'overdue'){
        	$row->amountPaid = "<span style='color:red'>" . strip_tags($row->amountPaid) . "</span>";
        }
        
    	if($fields['-list']){
    		$row->folderId = doc_Folders::recToVerbal(doc_Folders::fetch($rec->folderId))->title;
	    	$row->paymentState = ($rec->paymentState == 'overdue') ? "<span style='color:red'>{$row->paymentState}</span>" : $row->paymentState;
    	}
	    
	    if($fields['-single']){
		    $row->header = $mvc->singleTitle . " #<b>{$mvc->abbr}{$row->id}</b> ({$row->state})";
		    
	    	$mvc->prepareHeaderInfo($row, $rec);
	    	
	    	if ($rec->currencyRate != 1) {
	            $row->currencyRateText = '(<span class="quiet">' . tr('курс') . "</span> {$row->currencyRate})";
	        }
	        
	    	if($rec->note){
	    		$notes = explode('<br>', $row->note);
				foreach ($notes as $note){
					$row->notes .= "<li>{$note}</li>";
				}
			}
			
			// Взависимост начислява ли се ддс-то се показва подходящия текст
			switch($rec->chargeVat){
				case 'yes':
					$fld = 'withVat';
					break;
				case 'separate':
					$fld = 'sepVat';
					break;
				default:
					$fld = 'noVat';
					break;
			}
			$row->$fld = ' ';
			
	    	if($rec->makeInvoice == 'no'){
				$row->amountToInvoice = "<span style='font-size:0.7em'>" . tr('без фактуриране') . "</span>";
			}
	    }
    }


    /**
     * Филтър на продажбите
     */
    static function on_AfterPrepareListFilter(core_Mvc $mvc, $data)
    {
    	if(!Request::get('Rejected', 'int')){
        	$data->listFilter->FNC('type', 'enum(active=Активни,closed=Приключени,draft=Чернови,all=Активни и приключени,paid=Платени,overdue=Просрочени,unpaid=Неплатени,delivered=Доставени,undelivered=Недоставени)', 'caption=Тип,width=13em');
	        $data->listFilter->setDefault('type', 'active');
			$data->listFilter->showFields .= ',type';
		}
		
		$data->listFilter->input();
		if($filter = $data->listFilter->rec) {
			if($filter->type) {
				switch($filter->type){
					case "all":
						break;
					case "draft":
						$data->query->where("#state = 'draft'");
						break;
					case "active":
						$data->query->where("#state = 'active'");
						break;
					case "closed":
						$data->query->where("#state = 'closed'");
						break;
					case 'paid':
						$data->query->where("#amountPaid = #amountDeal");
						$data->query->where("#state = 'active' || #state = 'closed'");
						break;
					case 'overdue':
						$data->query->where("#paymentState = 'overdue'");
						break;
					case 'delivered':
						$data->query->where("#amountDelivered = #amountDeal");
						$data->query->where("#state = 'active' || #state = 'closed'");
						break;
					case 'undelivered':
						$data->query->orWhere("#amountDelivered < #amountDeal");
						$data->query->where("#state = 'active' || #state = 'closed'");
						break;
					case 'unpaid':
						$data->query->where("#amountPaid < #amountDelivered");
						$data->query->where("#state = 'active' || #state = 'closed'");
						break;
				}
			}
		}
    }
    
    
	/**
     * Подготвя данните на хедъра на документа
     */
    private function prepareHeaderInfo(&$row, $rec)
    {
    	$ownCompanyData = crm_Companies::fetchOwnCompany();
        $Companies = cls::get('crm_Companies');
        $row->MyCompany = $Companies->getTitleById($ownCompanyData->companyId);
        $row->MyAddress = $Companies->getFullAdress($ownCompanyData->companyId);
        
        $uic = drdata_Vats::getUicByVatNo($ownCompanyData->vatNo);
        if($uic != $ownCompanyData->vatNo){
    		$row->MyCompanyVatNo = $ownCompanyData->vatNo;
    	}
    	$row->uicId = $uic;
    	
    	// Данните на клиента
        $ContragentClass = cls::get($rec->contragentClassId);
    	$row->contragentName = $ContragentClass->getTitleById($rec->contragentId);
        $row->contragentAddress = $ContragentClass->getFullAdress($rec->contragentId);
    }
    
    
    /**
     * След рендиране на единичния изглед
     */
    function on_AfterRenderSingle($mvc, $tpl, $data)
    {
    	if(Mode::is('printing') || Mode::is('text', 'xhtml')){
    		$tpl->removeBlock('header');
    		$tpl->removeBlock('STATISTIC_BAR');
    	}
    	
    	if($data->paymentPlan){
    		$tpl->placeObject($data->paymentPlan);
    	}
    }
    
    
	/**
     * Извиква се след успешен запис в модела
     */
    public static function on_AfterSave(core_Mvc $mvc, &$id, $rec)
    {
    	if($rec->state != 'draft'){
    		$state = $rec->state;
    		$rec = $mvc->fetch($id);
    		$rec->state = $state;
    		
    		// Записване на покупката като отворена сделка
    		acc_OpenDeals::saveRec($rec, $mvc);
    	}
    	
    	if($rec->state == 'draft'){
    		
	    	// Ако има въведена банкова сметка, която я няма в системата я вкарваме
	    	if($rec->bankAccountId && strlen($rec->bankAccountId)){
	    		if(!bank_Accounts::fetch(array("#iban = '[#1#]'", $rec->bankAccountId))){
	    			$newAcc = new stdClass();
	    			$newAcc->currencyId = currency_Currencies::getIdByCode($rec->currencyId);
	    			$newAcc->iban = $rec->bankAccountId;
	    			$newAcc->contragentCls = $rec->contragentClassId;
	    			$newAcc->contragentId = $rec->contragentId;
	    			bank_Accounts::save($newAcc);
	    			core_Statuses::newStatus('Успешно е добавена нова банкова сметка на контрагента');
	    		}
	    	}
    	}
    }
    
    
    /**
     * Преди запис на документ
     */
    public static function on_BeforeSave($mvc, $res, $rec)
    {
    	// Кои потребители ще се нотифицират
    	$rec->sharedUsers = '';
    	$actions = type_Set::toArray($rec->contoActions);
    	
    	// Ако има склад, се нотифицира отговорника му
    	if(empty($actions['ship']) && $rec->shipmentStoreId){
    		$toChiefs = store_Stores::fetchField($rec->shipmentStoreId, 'chiefs');
    		$rec->sharedUsers = keylist::merge($rec->sharedUsers, $toChiefs);
    	}
    		
    	// Ако има каса се нотифицира касиера
    	if(empty($actions['pay']) && $rec->caseId){
	    	$toCashiers = cash_Cases::fetchField($rec->caseId, 'cashiers');
	    	$rec->sharedUsers = keylist::merge($rec->sharedUsers, $toCashiers);
    	}
    		
    	// Текущия потребител се премахва от споделянето
    	$rec->sharedUsers = keylist::removeKey($rec->sharedUsers, core_Users::getCurrent());
    }
    
    
	/**
     * В кои корици може да се вкарва документа
     * @return array - интерфейси, които трябва да имат кориците
     */
    public static function getAllowedFolders()
    {
    	return array('doc_ContragentDataIntf');
    }
    
    
	/**
     * Имплементация на @link bgerp_DealIntf::getDealInfo()
     * 
     * @param int|object $id
     * @return bgerp_iface_DealResponse
     * @see bgerp_DealIntf::getDealInfo()
     */
    public function getDealInfo($id)
    {
    	$rec = new purchase_model_Purchase(self::fetchRec($id));
        $actions = type_Set::toArray($rec->contoActions);
        
        // Извличаме продуктите на покупката
        $detailRecs = $rec->getDetails('purchase_PurchasesDetails', 'purchase_model_PurchaseProduct');
                
        $result = new bgerp_iface_DealResponse();
        
        $result->dealType = bgerp_iface_DealResponse::TYPE_PURCHASE;
        
        $allowedPaymentOperations = $this->allowedPaymentOperations;
        
        // Ако платежния метод няма авансова част, авансовите операции 
        // не са позволени за платежните документи
        if($rec->paymentMethodId){
        	if(!cond_PaymentMethods::hasDownpayment($rec->paymentMethodId)){
        		unset($allowedPaymentOperations['case2supplierAdvance'], 
        			  $allowedPaymentOperations['bank2supplierAdvance'],
        		      $allowedPaymentOperations['supplierAdvance2case'],
        			  $allowedPaymentOperations['supplierAdvance2bank']);
        	} else {
        		// Колко е очакваното авансово плащане
        		$paymentRec = cond_PaymentMethods::fetch($rec->paymentMethodId);
        		$downPayment = round($paymentRec->downpayment * $rec->amountDeal, 4);
        	}
        }
       
        // Кои са позволените операции за последващите платежни документи
        $result->allowedPaymentOperations = $allowedPaymentOperations;
        $result->involvedContragents = array((object)array('classId' => $rec->contragentClassId, 'id' => $rec->contragentId));
        
        $result->agreed->amount                 = $rec->amountDeal;
        $result->agreed->downpayment            = ($downPayment) ? $downPayment : NULL;
        $result->agreed->currency               = $rec->currencyId;
        $result->agreed->rate                   = $rec->currencyRate;
        $result->agreed->vatType 				= $rec->chargeVat;
        $result->agreed->delivery->location     = $rec->deliveryLocationId;
        $result->agreed->delivery->term         = $rec->deliveryTermId;
        $result->agreed->delivery->storeId      = $rec->shipmentStoreId;
        $result->agreed->delivery->time         = $rec->deliveryTime;
        $result->agreed->payment->method        = $rec->paymentMethodId;
        $result->agreed->payment->bankAccountId = bank_Accounts::fetchField("#iban = '{$rec->bankAccountId}'", 'id');
        $result->agreed->payment->caseId        = $rec->caseId;
        
    	if (isset($actions['pay'])) {
            $result->paid->amount   			  = $rec->amountDeal;
            $result->agreed->downpayment          = ($downPayment) ? $downPayment : NULL;
            $result->paid->currency 			  = $rec->currencyId;
            $result->paid->rate                   = $rec->currencyRate;
            $result->paid->vatType 				  = $rec->chargeVat;
            $result->paid->payment->method        = $rec->paymentMethodId;
            $result->paid->payment->bankAccountId = bank_Accounts::fetchField("#iban = '{$rec->bankAccountId}'", 'id');
            $result->paid->payment->caseId        = $rec->caseId;
        }

        if (isset($actions['ship'])) {
            $result->shipped->amount             = $rec->amountDeal;
            $result->agreed->downpayment         = ($downPayment) ? $downPayment : NULL;
            $result->shipped->currency           = $rec->currencyId;
            $result->shipped->rate               = $rec->currencyRate;
            $result->shipped->vatType 			 = $rec->chargeVat;
            $result->shipped->delivery->location = $rec->deliveryLocationId;
            $result->shipped->delivery->storeId  = $rec->shipmentStoreId;
            $result->shipped->delivery->term     = $rec->deliveryTermId;
            $result->shipped->delivery->time     = $rec->deliveryTime;
        }
        
        /* @var $dRec purchase_model_PurchaseProduct */
        foreach ($detailRecs as $dRec) {
            $p = new bgerp_iface_DealProduct();
            
            $p->classId     = $dRec->classId;
            $p->productId   = $dRec->productId;
            $p->packagingId = $dRec->packagingId;
            $p->discount    = $dRec->discount;
            $p->quantity    = $dRec->quantity;
            $p->price       = $dRec->price;
            $p->uomId       = $dRec->uomId;
            
            $ProductMan = cls::get($p->classId);
            $p->weight  = $ProductMan->getWeight($p->productId, $p->packagingId);
            $p->volume  = $ProductMan->getVolume($p->productId, $p->packagingId);
            
            $result->agreed->products[] = $p;
            
        	if (isset($actions['ship'])) {
        		
        		if($rec->chargeVat == 'yes' || $rec->chargeVat == 'separate'){
            		
            		// Отбелязваме че има ддс за начисляване от експедирането съответно за видовете продукти
	            	$vat = $ProductMan->getVat($dRec->productId, $rec->valior);
	            	$vatAmount = $dRec->price * $dRec->quantity * $vat;
	            	$code = $dRec->classId . "|" . $dRec->productId . "|" . $dRec->packagingId;
	            	$result->invoiced->vatToCharge[$code] += $vatAmount;
            	}
            	
            	$result->shipped->products[] = clone $p;
            }
        }
        
        return $result;
    }
    
    
	/**
	 * Имплементация на @link bgerp_DealAggregatorIntf::getAggregateDealInfo()
     * Генерира агрегираната бизнес информация за тази покупка
     * 
     * Обикаля всички документи, имащи отношение към бизнес информацията и извлича от всеки един
     * неговата "порция" бизнес информация. Всяка порция се натрупва към общия резултат до 
     * момента.
     * 
     * Списъка с въпросните документи, имащи отношение към бизнес информацията за пробдажбата е
     * сечението на следните множества:
     * 
     *  * Документите, върнати от @link doc_DocumentIntf::getDescendants()
     *  * Документите, реализиращи интерфейса @link bgerp_DealIntf
     *  * Документите, в състояние различно от `draft` и `rejected`
     * 
     * @return bgerp_iface_DealResponse
     */
    public function getAggregateDealInfo($id)
    {
        $requestRec = new purchase_model_Purchase($id);
        
    	$requestDocuments = $this->getDescendants($requestRec->id);
        
        // Извличаме dealInfo от самата покупка
        /* @var $saleDealInfo bgerp_iface_DealResponse */
        $requestDealInfo = $this->getDealInfo($requestRec->id);
        
        // dealInfo-то на самата покупка е база, в/у която се натрупват някой от аспектите
        // на породените от нея документи (платежни, експедиционни, фактури)
        $aggregateInfo = clone $requestDealInfo;
        
        /* @var $d core_ObjectReference */
        foreach ($requestDocuments as $d) {
            $dState = $d->rec('state');
            if ($dState == 'draft' || $dState == 'rejected') {
                // Игнорираме черновите и оттеглените документи
                continue;
            }
        
            if ($d->haveInterface('bgerp_DealIntf')) {
                /* @var $dealInfo bgerp_iface_DealResponse */
                $dealInfo = $d->getDealInfo();
                
                $aggregateInfo->shipped->push($dealInfo->shipped);
                $aggregateInfo->paid->push($dealInfo->paid);
                $aggregateInfo->invoiced->push($dealInfo->invoiced);
            }
        }
        
        // Aко няма експедирани/фактурирани продукти, то се копират договорените
        // но с количество 0 за експедирани/фактурирани
    	foreach(array('shipped', 'invoiced') as $type){
    		$aggregateInfo->$type->currency = $aggregateInfo->agreed->currency;
        	$aggregateInfo->$type->rate     = $aggregateInfo->agreed->rate;
        	$aggregateInfo->$type->vatType  = $aggregateInfo->agreed->vatType;
        	
        	if(!count($aggregateInfo->$type->products)){
        		foreach ($aggregateInfo->agreed->products as $aProd){
        			$cloneProd = clone $aProd;
        			$cloneProd->quantity = 0;
        			$aggregateInfo->$type->products[] = $cloneProd;
        		}
        	}
        }
        
        return $aggregateInfo;
    }
    
    
	/**
     * Трасира веригата от документи, породени от дадена покупка. Извлича от тях експедираните 
     * количества и платените суми.
     * 
     * @param core_Mvc $mvc
     * @param core_ObjectReference $requestRef
     * @param core_ObjectReference $descendantRef кой породен документ е инициатор на трасирането
     */
    public static function on_DescendantChanged($mvc, $requestRef, $descendantRef = NULL)
    {
        $requestRec = new purchase_model_Purchase($requestRef->rec());
    	$aggregatedDealInfo = $mvc->getAggregateDealInfo($requestRef->that);
		
        $requestRec->updateAggregateDealInfo($aggregatedDealInfo);
    }
    
    
	/**
     * След промяна в детайлите на обект от този клас
     */
    public static function on_AfterUpdateDetail(core_Manager $mvc, $id, core_Manager $detailMvc)
    {
         // Запомняне кои документи трябва да се обновят
    	$mvc->updated[$id] = $id;
    }
    
    
    /**
     * Обновява информацията на документа
     * @param int $id - ид на документа
     */
    public function updateMaster($id)
    {
    	$rec = $this->fetchRec($id);
    	
    	$query = $this->purchase_PurchasesDetails->getQuery();
        $query->where("#requestId = '{$id}'");
        
        price_Helper::fillRecs($query->fetchAll(), $rec);
        
        // ДДС-то е отделно amountDeal  е сумата без ддс + ддс-то, иначе самата сума си е с включено ддс
        $amountDeal = ($rec->chargeVat == 'separate') ? $rec->_total->amount + $rec->_total->vat : $rec->_total->amount;
        $amountDeal -= $rec->_total->discount;
        $rec->amountDeal = $amountDeal * $rec->currencyRate;
        $rec->amountVat  = $rec->_total->vat * $rec->currencyRate;
        $rec->amountDiscount = $rec->_total->discount * $rec->currencyRate;
        
        $this->save($rec);
    }
    
    
    /**
     * След изпълнение на скрипта, обновява записите, които са за ъпдейт
     */
    public static function on_Shutdown($mvc)
    {
        if(count($mvc->updated)){
        	foreach ($mvc->updated as $id) {
	        	$mvc->updateMaster($id);
	        }
        }
    }
    
    
	/**
     * Дебъг екшън показващ агрегираните бизнес данни
     */
    function act_AggregateDealInfo()
    {
    	requireRole('debug');
    	expect($id = Request::get('id', 'int'));
    	$info = $this->getAggregateDealInfo($id);
    	bp($info);
    }
    
    
	/**
     * Изпълнява се след подготовката на ролите, които могат да изпълняват това действие.
     */
    public static function on_AfterGetRequiredRoles($mvc, &$res, $action, $rec = NULL, $userId = NULL)
    {
    	if($action == 'activate'){
    		if(isset($rec)){
    			if(!$mvc->purchase_PurchasesDetails->fetch("#requestId = {$rec->id}")){
    				$res = 'no_one';
    			}
    		} else {
    			$res = 'no_one';
    		}
    	}
    }
    
    
	/**
     * Помощна ф-я показваща дали в покупката има поне един складируем/нескладируем артикул
     * @param int $id - ид на покупката
     * @param boolean $storable - дали се търсят складируеми или нескладируеми артикули
     * @return boolean TRUE/FALSE - дали има поне един складируем/нескладируем артикул
     */
    public function hasStorableProducts($id, $storable = TRUE)
    {
    	$rec = new purchase_model_Purchase(self::fetchRec($id));
        $detailRecs = $rec->getDetails('purchase_PurchasesDetails', 'purchase_model_PurchaseProduct');
        
        foreach ($detailRecs as $d){
        	$info = cls::get($d->classId)->getProductInfo($d->productId);
        	if($storable){
        		
        		// Връща се TRUE ако има поне един складируем продукт
        		if(isset($info->meta['canStore'])) return TRUE;
        	} else {
        		
        		// Връща се TRUE ако има поне един НЕ складируем продукт
        		if(!isset($info->meta['canStore']))return TRUE;
        	}
        }
        
        return FALSE;
    }
    
    
    /**
     * Интерфейсен метод на doc_ContragentDataIntf
     * Връща тялото на имейл по подразбиране
     */
    static function getDefaultEmailBody($id)
    {
        $handle = static::getHandle($id);
        $tpl = new ET(tr("Моля запознайте се с нашата покупка") . ': #[#handle#]');
        $tpl->append($handle, 'handle');
        
        return $tpl->getContent();
    }
    
    
    /**
     * Извиква се след SetUp-а на таблицата за модела
     */
    static function on_AfterSetupMvc($mvc, &$res)
    {
    	$mvc->setCron($res);
    	$mvc->setTemplates($res);
    }
    
    
    /**
     * Нагласяне на крон да приключва продажби и да проверява дали са просрочени
     */
    private function setCron(&$res)
    {
    	// Крон метод за затваряне на остарели покупки
    	$rec = new stdClass();
        $rec->systemId = "Close purchases";
        $rec->description = "Затваря приключените покупки";
        $rec->controller = "purchase_Purchases";
        $rec->action = "CloseOldPurchases";
        $rec->period = 1440;
        $rec->offset = 0;
        $rec->delay = 0;
        $rec->timeLimit = 100;
        
        // Проверка по крон дали покупката е просрочена
        $rec2 = new stdClass();
        $rec2->systemId = "IsPurchaseOverdue";
        $rec2->description = "Проверява дали покупката е просрочена";
        $rec2->controller = "purchase_Purchases";
        $rec2->action = "CheckPurchasePayments";
        $rec2->period = 60;
        $rec2->offset = 0;
        $rec2->delay = 0;
        $rec2->timeLimit = 100;
        
        $Cron = cls::get('core_Cron');
    	if($Cron->addOnce($rec)) {
            $res .= "<li><font color='green'>Задаване на крон да приключва стари покупки.</font></li>";
        } else {
            $res .= "<li>Отпреди Cron е бил нагласен да приключва стари покупки.</li>";
        }
        
    	if($Cron->addOnce($rec2)) {
            $res .= "<li><font color='green'>Задаване на крон да проверява дали покупката е просрочена.</font></li>";
        } else {
            $res .= "<li>Отпреди Cron е бил нагласен да проверява дали покупката е просрочена.</li>";
        }
    }
    
    
    /**
     * Проверява дали покупки е просрочена или платени
     */
    function cron_CheckPurchasePayments()
    {
    	$conf = core_Packs::getConfig('purchase');
    	$overdueDelay = $conf->PURCHASE_OVERDUE_CHECK_DELAY;
    	
    	$CronHelper = cls::get('acc_CronDealsHelper', array('className' => $this->className));
    	$CronHelper->checkPayments($overdueDelay);
    }
    
    
    /**
     * Приключва всички приключени покупки
     */
    function cron_CloseOldPurchases()
    {
    	$conf = core_Packs::getConfig('purchase');
    	$tolerance = $conf->PURCHASE_CLOSE_TOLERANCE;
    	$olderThan = $conf->PURCHASE_CLOSE_OLDER_THAN;
    	$ClosedDeals = cls::get('purchase_ClosedDeals');
    	
    	$CronHelper = cls::get('acc_CronDealsHelper', array('className' => $this->className));
    	$CronHelper->closeOldDeals($olderThan, $tolerance, $ClosedDeals);
    }
    
    
    /**
     * Зарежда шаблоните на покупката в doc_TplManager
     */
    private function setTemplates(&$res)
    {
    	$tplArr[] = array('name' => 'Договор за покупка', 'content' => 'purchase/tpl/purchases/Purchase.shtml', 'lang' => 'bg');
    	$tplArr[] = array('name' => 'Договор за покупка на услуга', 'content' => 'purchase/tpl/purchases/Service.shtml', 'lang' => 'bg');
    	$tplArr[] = array('name' => 'Purchase contract', 'content' => 'purchase/tpl/purchases/PurchaseEN.shtml', 'lang' => 'en');
    	$tplArr[] = array('name' => 'Purchase of service contract', 'content' => 'purchase/tpl/purchases/ServiceEN.shtml', 'lang' => 'en', 'oldName' => 'Purchase of Service contract');
    	
    	$skipped = $added = $updated = 0;
    	foreach ($tplArr as $arr){
    		$arr['docClassId'] = $this->getClassId();
    		doc_TplManager::addOnce($arr, $added, $updated, $skipped);
    	}
    	
    	$res .= "<li><font color='green'>Добавени са {$added} шаблона за покупки, обновени са {$updated}, пропуснати са {$skipped}</font></li>";
    }
    
    
    /**
      * Добавя ключови думи за пълнотекстово търсене, това са името на
      * документа или папката
      */
     function on_AfterGetSearchKeywords($mvc, &$res, $rec)
     {
     	// Тук ще генерираме всички ключови думи
     	$detailsKeywords = '';

     	// заявка към детайлите
     	$query = purchase_PurchasesDetails::getQuery();
     	
     	// точно на тази фактура детайлите търсим
     	$query->where("#requestId = '{$rec->id}'");
     	
	        while ($recDetails = $query->fetch()){
	        	// взимаме заглавията на продуктите
	        	$productTitle = cls::get($recDetails->classId)->getTitleById($recDetails->productId);
	        	// и ги нормализираме
	        	$detailsKeywords .= " " . plg_Search::normalizeText($productTitle);
	        }
	        
    	// добавяме новите ключови думи към основните
    	$res = " " . $res . " " . $detailsKeywords;
     }
     
     
    /**
     * Функция, която прихваща след активирането на документа
     */
    public static function on_AfterActivation($mvc, &$rec)
    {
    	//Ако потребителя не е в група доставчици го включваме
    	$rec = $mvc->fetchRec($rec);
    	cls::get($rec->contragentClassId)->forceGroup($rec->contragentId, 'suppliers');
    }
}
