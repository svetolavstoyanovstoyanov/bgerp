<?php



/**
 * Клас 'trans_Cmrs'
 *
 * Документ за Транспортни линии
 *
 *
 * @category  bgerp
 * @package   trans
 * @author    Ivelin Dimov <ivelin_pdimov@abv.com>
 * @copyright 2006 - 2017 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class trans_Cmrs extends core_Master
{
	
    /**
     * Заглавие
     */
    public $title = 'Товарителници';


    /**
     * Абревиатура
     */
    public $abbr = 'CMR';
    
    
    /**
     * Поддържани интерфейси
     */
    public $interfaces = 'doc_DocumentIntf';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'plg_RowTools2, trans_Wrapper, plg_Printing, plg_Clone,doc_DocumentPlg, plg_Search, doc_ActivatePlg, doc_EmailCreatePlg';

    
    /**
	 * Кой може да го разглежда?
	 */
	public $canList = 'ceo, trans';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'ceo, trans';
    
    
    /**
     * Кой има право да добавя?
     */
    public $canAdd = 'ceo, trans';


    /**
     * Кой има право да пише?
     */
    public $canWrite = 'ceo, trans';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    //public $listFields = 'id, handler=Документ, title, start, folderId, createdOn, createdBy';
    
    
    /**
     * Заглавие в единствено число
     */
    public $singleTitle = 'Товарителница';
    
    
    /**
     * Файл за единичния изглед
     */
    public $singleLayoutFile = 'trans/tpl/SingleLayoutCMR.shtml';
    		
    		
    /**
     * Икона за единичния изглед
     */
    public $singleIcon = 'img/16/lorry_go.png';
    
   
    /**
     * Групиране на документите
     */
    public $newBtnGroup = "4.7|Логистика";
    
    
    /**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     */
    public $searchFields = 'consigneeData,deliveryPlace,loadingDate,natureOfGoods1,statNum1,natureOfGoods2,statNum2,natureOfGoods3,statNum3,natureOfGoods4,statNum4,cariersData,vehicleReg';
    
    
    /**
     * Дали в листовия изглед да се показва бутона за добавяне
     */
    public $listAddBtn = FALSE;
    
    
    /**
     * Описание на модела (таблицата)
     */
    public function description()
    {
    	$this->FLD('senderData', 'text(rows=2)', 'caption=1. Изпращач');
    	$this->FLD('consigneeData', 'text(rows=2)', 'caption=2. Получател');
    	
    	$this->FLD('deliveryPlace', 'text(rows=2)', 'caption=3. Разтоварен пункт');
    	$this->FLD('loadingPlace', 'text(rows=2)', 'caption=4. Товарен пункт');
    	$this->FLD('loadingDate', 'datetime', 'caption=4. Дата на товарене');
    	$this->FLD('documentsAttached', 'varchar', 'caption=5. Приложени документи');
    	
    	$this->FLD('mark1', 'varchar', 'caption=1. Информация за стоката->6. Знаци и Номера');
    	$this->FLD('numOfPacks1', 'varchar', 'caption=1. Информация за стоката->7. Брой колети');
    	$this->FLD('methodOfPacking1', 'varchar', 'caption=1. Информация за стоката->8. Вид опаковка');
    	$this->FLD('natureOfGoods1', 'varchar', 'caption=1. Информация за стоката->9. Вид стока');
    	$this->FLD('statNum1', 'varchar', 'caption=1. Информация за стоката->10. Статистически №');
    	$this->FLD('grossWeight1', 'varchar', 'caption=1. Информация за стоката->11. Тегло Бруто');
    	$this->FLD('volume1', 'varchar', 'caption=1. Информация за стоката->12. Обем');
    	
    	$this->FLD('mark2', 'varchar', 'caption=2. Информация за стоката->6. Знаци и Номера,autohide');
    	$this->FLD('numOfPacks2', 'varchar', 'caption=2. Информация за стоката->7. Брой колети,autohide');
    	$this->FLD('methodOfPacking2', 'varchar', 'caption=2. Информация за стоката->8. Вид опаковка,autohide');
    	$this->FLD('natureOfGoods2', 'varchar', 'caption=2. Информация за стоката->9. Вид стока,autohide');
    	$this->FLD('statNum2', 'varchar', 'caption=2. Информация за стоката->10. Статистически №,autohide');
    	$this->FLD('grossWeight2', 'varchar', 'caption=2. Информация за стоката->11. Тегло Бруто,autohide');
    	$this->FLD('volume2', 'varchar', 'caption=2. Информация за стоката->12. Обем,autohide');
    	
    	$this->FLD('mark3', 'varchar', 'caption=3. Информация за стоката->6. Знаци и Номера,autohide');
    	$this->FLD('numOfPacks3', 'varchar', 'caption=3. Информация за стоката->7. Брой колети,autohide');
    	$this->FLD('methodOfPacking3', 'varchar', 'caption=3. Информация за стоката->8. Вид опаковка,autohide');
    	$this->FLD('natureOfGoods3', 'varchar', 'caption=3. Информация за стоката->9. Вид стока,autohide');
    	$this->FLD('statNum3', 'varchar', 'caption=3. Информация за стоката->10. Статистически №,autohide');
    	$this->FLD('grossWeight3', 'varchar', 'caption=3. Информация за стоката->11. Тегло Бруто,autohide');
    	$this->FLD('volume3', 'varchar', 'caption=3. Информация за стоката->12. Обем,autohide');
    	
    	$this->FLD('mark4', 'varchar', 'caption=4. Информация за стоката->6. Знаци и Номера,autohide');
    	$this->FLD('numOfPacks4', 'varchar', 'caption=4. Информация за стоката->7. Брой колети,autohide');
    	$this->FLD('methodOfPacking4', 'varchar', 'caption=4. Информация за стоката->8. Вид опаковка,autohide');
    	$this->FLD('natureOfGoods4', 'varchar', 'caption=4. Информация за стоката->9. Вид стока,autohide');
    	$this->FLD('statNum4', 'varchar', 'caption=4. Информация за стоката->10. Стат. №,autohide');
    	$this->FLD('grossWeight4', 'varchar', 'caption=4. Информация за стоката->11. Тегло Бруто,autohide');
    	$this->FLD('volume4', 'varchar', 'caption=4. Информация за стоката->12. Обем,autohide');
    	
    	$this->FLD('class', 'varchar(12)', 'caption=ADR->Клас');
    	$this->FLD('number', 'int', 'caption=ADR->Цифра');
    	$this->FLD('letter', 'varchar(12)', 'caption=ADR->Буква');
    	
    	$this->FLD('senderInstructions', 'text(rows=2)', 'caption=Допълнително->13. Указания на изпращача');
    	$this->FLD('instructionsPayment', 'text(rows=2)', 'caption=Допълнително->14. Предп. плащане навло');
    	
    	$this->FLD('cashOnDelivery', 'varchar', 'caption=Допълнително->15. Наложен платеж');
    	$this->FLD('cariersData', 'text(rows=2)', 'caption=Допълнително->16. Превозвач');
    	$this->FLD('vehicleReg', 'varchar', 'caption=МПС регистрационен №');
    	$this->FLD('successiveCarriers', 'text(rows=2)', 'caption=Допълнително->17. Посл. превозвачи');
    	$this->FLD('specialagreements', 'text(rows=2)', 'caption=Допълнително->19. Спец. споразумения');
    
    	$this->FLD('establishedPlace', 'text(rows=2)', 'caption=21. Изготвена в');
    	$this->FLD('establishedDate', 'datetime', 'caption=21. Изготвена на');
    }
    
    
    /**
     * Преди показване на форма за добавяне/промяна
     */
    protected static function on_AfterPrepareEditForm($mvc, &$data)
    {
    	$form = &$data->form;
    	$rec  = &$form->rec;
    	
    	expect($origin = doc_Containers::getDocument($rec->originId));
    	$sRec = $origin->fetch();
    	$lData = $origin->getLogisticData();
    	
    	core_Lg::push('en');
    	
    	$senderData = $mvc->getDefaultSenderData($sRec);
    	$consigneeData = $mvc->getDefaultContragentData($sRec->contragentClassId, $sRec->contragentId);
    	$loadingPlace = $lData['fromPCode'] . " " .  transliterate($lData['fromPlace']) . ", " . $lData['fromCountry'];
    	$deliveryPlace = $lData['toPCode'] . " " .  transliterate($lData['toPlace']) . ", " . $lData['toCountry'];
    	
    	$weight = ($sRec->weightInput) ? $sRec->weightInput : $sRec->weight;
    	if(!empty($weight)){
    		$weight = core_Type::getByName('cat_type_Weight')->toVerbal($weight);
    		$form->setDefault('grossWeight1', $weight);
    	}
    	
    	$volume = ($sRec->weightInput) ? $sRec->volumeInput : $sRec->volume;
    	if(!empty($weight)){
    		$volume = core_Type::getByName('cat_type_Volume')->toVerbal($volume);
    		$form->setDefault('volume1', $volume);
    	}
    	
    	core_Lg::pop();
    	
    	
    	
    	
    	$form->setDefault('senderData', $senderData);
    	$form->setDefault('consigneeData', $consigneeData);
    	$form->setDefault('deliveryPlace', $deliveryPlace);
    	$form->setDefault('loadingPlace', $loadingPlace);
    	$form->setDefault('loadingDate', $lData['loadingTime']);
    	
    	if(isset($sRec->lineId)){
    		$lineRec = trans_Lines::fetch($sRec->lineId);
    		if(isset($lineRec->forwarderId)){
    			$carrierData = $mvc->getDefaultContragentData('crm_Companies', $lineRec->forwarderId);
    			$form->setDefault('cariersData', $carrierData);
    		}
    	}
    	
    	if(!empty($sRec->palletCountInput)){
    		$collets = core_Type::getByName('int')->toVerbal($sRec->palletCountInput);
    		$collets .= " PALLETS";
    		$form->setDefault('numOfPacks1', $collets);
    	}
    	
    }
    
    private function getDefaultSenderData($sRec)
    {
    	$ownData = crm_Companies::fetchOwnCompany();
    	$ownCompanyName = cls::get('type_Varchar')->toVerbal($ownData->company);
    	$ownCompanyName = transliterate(tr($ownCompanyName));
    	 
    	$ownAddress = cls::get('crm_Companies')->getFullAdress($ownData->companyId, TRUE, FALSE)->getContent();
    	$ownAddress = str_replace('<br>', ', ', $ownAddress);
    	$country = crm_Companies::getVerbal($ownData->companyId, 'country');
    	$senderData = "{$ownCompanyName},{$ownAddress}, {$country}";
    	
    	return $senderData;
    }
    
    
    private function getDefaultContragentData($contragentClassId, $contragentId)
    {
    	$Contragent = cls::get($contragentClassId);
    	$contragentAddress = $Contragent->getFullAdress($contragentId, TRUE, FALSE)->getContent();
    	$contragenAddress = str_replace('<br>', ', ', $contragenAddress);
    	$contragenCountry = $Contragent->getVerbal($contragentId, 'country');
    	
    	$contragenName = $Contragent->getVerbal($contragentId, 'name');
    	$contragenData = "{$contragenName},{$contragenAddress}, {$contragenCountry}";
    	
    	return $contragenData;
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
    	if(!empty($rec->loadingDate)){
    		$row->loadingDate = dt::mysql2verbal($rec->loadingDate, 'd.m.y');
    	}
    }
    
    
    /**
     * Документа не може да бъде начало на нишка; може да се създава само в съществуващи нишки
     */
    public static function canAddToFolder($folderId)
    {
    	return FALSE;
    }
    
    
    /**
     * Проверка дали нов документ може да бъде добавен в посочената нишка
     */
    public static function canAddToThread($threadId)
    {
    	$originId = Request::get('originId', 'int');
    	if(empty($originId)) return FALSE;
    	
    	$origin = doc_Containers::getDocument($originId);
    	$state = $origin->rec()->state;
    	
    	if(in_array($state, array('draft','active', 'pending')) && $origin->isInstanceOf('store_ShipmentOrders')) return TRUE;
    	
    	return FALSE;
    }
    
    
    /**
     * @see doc_DocumentIntf::getDocumentRow()
     */
    public function getDocumentRow($id)
    {
    	expect($rec = $this->fetch($id));
    	$title = $this->getRecTitle($rec);
    
    	$row = (object)array(
    			'title'    => $title,
    			'authorId' => $rec->createdBy,
    			'author'   => $this->getVerbal($rec, 'createdBy'),
    			'state'    => $rec->state,
    			'recTitle' => $title
    	);
    
    	return $row;
    }
}