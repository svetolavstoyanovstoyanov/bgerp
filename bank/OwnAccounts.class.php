<?php



/**
 * Банкови сметки на фирмата
 *
 *
 * @category  bgerp
 * @package   bank
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class bank_OwnAccounts extends core_Manager {
    
    
    /**
     * Интерфейси, поддържани от този мениджър
     */
    var $interfaces = 'acc_RegisterIntf, bank_OwnAccRegIntf';
    
    
    /**
     * Плъгини за зареждане
     */
    var $loadList = 'plg_Created, plg_RowTools, bank_Wrapper, acc_plg_Registry,
                     plg_Sorting, plg_Current, plg_LastUsedKeys';
    
    
    /**
     * Кои ключове да се тракват, кога за последно са използвани
     */
    var $lastUsedKeys = 'bankAccountId';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    var $listFields = 'title, bankAccountId, tools=Пулт';
    
    
    /**
     * Полето в което автоматично се показват иконките за редакция и изтриване на реда от таблицата
     */
    var $rowToolsField = 'tools';
    
    
    /**
     * Кой има право да чете?
     */
    var $canRead = 'bank, ceo';
    
    
    /**
     * Кой може да пише?
     */
    var $canWrite = 'bank, ceo';
    
    
    /**
     * Заглавие
     */
    var $title = 'Банкови сметки на фирмата';
    
    
    /**
     * Заглавие в единствено число
     */
    var $singleTitle = 'Банкова сметка на фирмата';
    
    
    /**
     * Всички записи на този мениджър автоматично стават пера в номенклатурата със системно име
     * $autoList.
     * 
     * @see acc_plg_Registry
     * @var string
     */
    var $autoList = 'bankAcc';
    
    
    /**
     * Описание на модела (таблицата)
     */
    function description()
    {
        $this->FLD('bankAccountId', 'key(mvc=bank_Accounts,select=iban)', 'caption=Сметка,mandatory');
        $this->FLD('title', 'varchar(128)', 'caption=Наименование,mandatory');
        $this->FLD('titulars', 'keylist(mvc=crm_Persons, select=name)', 'caption=Титуляри->Име,mandatory');
        $this->FLD('together', 'enum(together=Заедно,separate=Поотделно)', 'caption=Титуляри->Представляват');
        $this->FLD('operators', 'keylist(mvc=core_Users, select=names)', 'caption=Оператори,mandatory');
    }
    
    
    /**
     * Обработка по формата
     * @param core_Mvc $mvc
     * @param stdClass $res
     * @param stdClass $data
     */
    static function on_AfterPrepareEditForm($mvc, &$res, $data)
    {
    	$optionAccounts = $mvc->getPossibleBankAccounts();
    	$operators = $mvc->getOperators();
    	$titulars = $mvc->getTitulars();
        
        $data->form->setOptions('bankAccountId', $optionAccounts);
        $data->form->setSuggestions('operators', $operators);
        $data->form->setSuggestions('titulars', $titulars);
    	
        // Номера на сметката неможе да се променя ако редактираме, за смяна на
        // сметката да се прави от bank_accounts
        if($data->form->rec->id) {
        	$data->form->setReadOnly('bankAccountId');
        }
    }
    
    
    /**
     * Връща всички Всички лица, които могат да бъдат титуляри на сметка
     * тези включени в група "Управители"
     */
    function getTitulars()
    {
    	$options = array();
    	$groupId = crm_Groups::fetchField("#name = 'Управители'", 'id');
    	$personQuery = crm_Persons::getQuery();
    	$personQuery->where("#groupList LIKE '%|{$groupId}|%'");
    	while($personRec = $personQuery->fetch()) {
    		$options[$personRec->id] = $personRec->name;
    	}   	
    	
    	if(count($options) == 0) {
    		return Redirect(array('crm_Persons', 'list'), NULL, 'Няма лица в група "Управители" за титуляри на "нашите сметки". Моля добавете !');
    	}
    	return $options;
    }
    
    
    /**
     * Подготовка на списъка от банкови сметки, между които можем да избираме
     * @return array $options - масив от потребители
     */
    function getPossibleBankAccounts()
    {
    	$conf = core_Packs::getConfig('crm');
    	$bankAccounts = cls::get('bank_Accounts');
    	
    	// Извличаме само онези сметки, които са на нашата фирма и не са
        // записани в bank_OwnAccounts
        $queryBankAccounts = $bankAccounts->getQuery();
        $queryBankAccounts->where("#contragentId = {$conf->BGERP_OWN_COMPANY_ID}");
        $queryBankAccounts->where("#contragentCls = " . core_Classes::getId('crm_Companies'));
        $options = array();
        
        while($rec = $queryBankAccounts->fetch()) {
           if (!static::fetchField("#bankAccountId = " . $rec->id , 'id')) {
               $options[$rec->id] = $bankAccounts->getVerbal($rec, 'iban');
           }
        }
       
        return $options;
    }
    
    
    /**
     * Извличаме само потребителите с роля bank
     * @return array $suggestions - масив от потребители
     */
    function getOperators()
    {
    	$suggestions = array();
    	$query = core_Users::getQuery();
    	while($rec = $query->fetch()) {
    		if(core_Users::haveRole('bank', $rec->id)) {
    			$row = core_Users::recToVerbal($rec);
    			$suggestions[$rec->id] = $row->names;
    		}
    	}
    	
    	if(count($suggestions) == 0) {
    		return Redirect(array('core_Users', 'list'), NULL, 'Няма потребители с права "bank"');
    	}
    	
    	return $suggestions;
    }    
    
    
    /**
     * Проверка дали може да се добавя банкова сметка в ownAccounts(Ако броя
     * на собствените сметки отговаря на броя на сметките на Моята компания в
     * bank_Accounts то неможем да добавяме нова сметка от този мениджър
     * @return boolean TRUE/FALSE - можем ли да добавяме нова сметка
     */
    function canAddOwnAccount()
    {
    	$conf = core_Packs::getConfig('crm');
    	$accountsQuery = bank_Accounts::getQuery();
    	$accountsQuery->where("#contragentId = {$conf->BGERP_OWN_COMPANY_ID}");
        $accountsQuery->where("#contragentCls = " . core_Classes::getId('crm_Companies'));
        $accountsNumber = $accountsQuery->count();
    	$ownAccountsQuery = $this->getQuery();
    	$ownAccountsNumber = $ownAccountsQuery->count();
    	if($ownAccountsNumber == $accountsNumber) {
    		return FALSE;
    	}
    	
    	return TRUE;
    }
    
    
    /**
     * Изчличане на цялата информация за сметката която е активна
     * @return bank_Accounts $acc - записа отговарящ на текущата ни сметка
     */
    static function getOwnAccountInfo($id = NULL)
    {
    	if($id) {
    		$ownAcc = static::fetch($id);
    	} else {
    		$ownAcc = static::fetch(static::getCurrent());
    	}
    	
    	$acc = bank_Accounts::fetch($ownAcc->bankAccountId);
    	if(!$acc->bank) {
    		$acc->bank = drdata_Banks::getBankName($acc->iban);
    	}
    	if(!$acc->bic) {
    		$acc->bic = drdata_Banks::getBankBic($acc->iban);
    	}
    	
    	return $acc;
    }
    
    
    /**
     * Обработка на ролите 
     */
    function on_AfterGetRequiredRoles($mvc, &$res, $action)
    {
     	if($action == 'add') {
     		if(!$mvc->canAddOwnAccount()) {
     			$res = 'no_one';
     		}
     	}
    }
    
    
    /*******************************************************************************************
     * 
     * ИМПЛЕМЕНТАЦИЯ на интерфейса @see crm_ContragentAccRegIntf
     * 
     ******************************************************************************************/
    
    
    /**
     * @see crm_ContragentAccRegIntf::getItemRec
     * @param int $objectId
     */
    static function getItemRec($objectId)
    {
        $result = NULL;
        if ($rec = static::fetch($objectId)) {
        	$account = bank_Accounts::fetch($rec->bankAccountId);
        	$cCode = currency_Currencies::getCodeById($account->currencyId);
            $result = (object)array(
                'num' => $rec->id,
				'title' => $cCode . " - " . $account->iban,
                'features' => 'foobar' // @todo!
            );
        }
        
        return $result;
    }
    
    
    /**
     * @see crm_ContragentAccRegIntf::itemInUse
     * @param int $objectId
     */
    static function itemInUse($objectId)
    {
        // @todo!
    }
    
    /**
     * КРАЙ НА интерфейса @see acc_RegisterIntf
     */

}
