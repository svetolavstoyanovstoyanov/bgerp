<?php 

/**
 * Детайл за безналични методи на плащане към ПКО
 *
 *
 * @category  bgerp
 * @package   cash
 *
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2024 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 */
class cash_NonCashPaymentDetails extends core_Manager
{
    /**
     * Кой може да го разглежда?
     */
    public $canList = 'no_one';
    
    
    /**
     * Кой може да създава?
     */
    public $canAdd = 'no_one';
    
    
    /**
     * Кой може да редактира?
     */
    public $canEdit = 'no_one';
    
    
    /**
     * Кой може да изтрива?
     */
    public $canDelete = 'no_one';
    
    
    /**
     * Кой може да изтрива?
     */
    public $canModify = 'cash, ceo, purchase, sales';
    
    
    /**
     * Неща, подлежащи на начално зареждане
     */
    public $loadList = 'cash_Wrapper';
    
    
    /**
     * Заглавие
     */
    public $title = 'Начин на плащане';
    
    
    /**
     * Описание на модела
     */
    public function description()
    {
        $this->FLD('documentId', 'key(mvc=cash_Pko)', 'input=hidden,mandatory,silent');
        $this->FLD('paymentId', 'key(mvc=cond_Payments, select=title)', 'caption=Метод');
        $this->FLD('amount', 'double(decimals=2)', 'caption=Сума,mandatory');
        $this->FLD('param', 'varchar', 'caption=Параметър,input=none');

        $this->setDbIndex('documentId');
        $this->setDbUnique('documentId,paymentId');
    }
    
    
    /**
     * Подготовка на детайла
     *
     * @param stdClass $data
     */
    public function prepareDetail_($data)
    {
        $query = $this->getQuery();
        $query->where("#documentId = {$data->masterId}");
        $restAmount = $data->masterData->rec->amount;
        $toCurrencyCode = currency_Currencies::getCodeById($data->masterData->rec->currencyId);
        $canSeePrices = doc_plg_HidePrices::canSeePriceFields($data->masterMvc, $data->masterData->rec);

        // Извличане на записите
        $data->recs = $data->rows = array();
        while ($rec = $query->fetch()) {
            $data->recs[$rec->id] = $rec;
            $data->rows[$rec->id] = $this->recToVerbal($rec);
            if(!$canSeePrices) {
                $data->rows[$rec->id]->amount = doc_plg_HidePrices::getBuriedElement();
            }

            $amount = cond_Payments::toBaseCurrency($rec->paymentId, $rec->amount, $data->masterData->rec->valior, $toCurrencyCode);
            $restAmount -= $amount;
        }
        
        if ($restAmount > 0 && countR($data->recs)) {
            $r = (object) array('documentId' => $data->masterId, 'amount' => $restAmount, 'paymentId' => -1);
            $data->recs[] = $r;
            $row = $this->recToVerbal($r);
            $row->paymentId .= ", {$toCurrencyCode}";
            if(!$canSeePrices) {
                $row->amount = doc_plg_HidePrices::getBuriedElement();
            }
            $data->rows[] = $row;
        }
        
        $data->masterMvc->invoke('AfterPrepareNonCashPayments', array(&$data));
        
        return $data;
    }
    
    
    /**
     * След преобразуване на записа в четим за хора вид.
     *
     * @param core_Mvc $mvc
     * @param stdClass $row Това ще се покаже
     * @param stdClass $rec Това е записа в машинно представяне
     */
    protected static function on_AfterRecToVerbal($mvc, &$row, $rec, $fields = array())
    {
        if ($rec->paymentId == -1) {
            $row->paymentId = tr('В брой');
        }
        
        // Ако е избрано безналично плащане към активно ПКО
        $pkoRec = cash_Pko::fetch($rec->documentId);
        
        if ($pkoRec->state == 'active' && $rec->paymentId != -1) {
            $cashFolderId = cash_Cases::fetchField($pkoRec->peroCase, "folderId");
           
            // И потребителя може да прави вътрешнокасов трансфер
            if(cash_InternalMoneyTransfer::haveRightFor("add", (object)array('folderId' => $cashFolderId))){
                $currencyCode = cond_Payments::fetchField($rec->paymentId, 'currencyCode');
                $currencyId = !empty($currencyCode) ? currency_Currencies::getIdByCode($currencyCode) : acc_Periods::getBaseCurrencyId(); 
                
                $url = array('cash_InternalMoneyTransfer', 'add', 'folderId' => $cashFolderId, 'operationSysId' => 'nonecash2case', 'amount' => $rec->amount, 'creditCase' => $pkoRec->peroCase, 'paymentId' => $rec->paymentId, 'currencyId' => $currencyId, 'sourceId' => $pkoRec->containerId, 'foreignId' => $pkoRec->containerId, 'ret_url' => true);
                $toolbar = new core_RowToolbar();
                $toolbar->addLink('Инкасиране(Каса)', $url, "ef_icon = img/16/safe-icon.png,title=Създаване на вътрешно касов трансфер  за инкасиране на безналично плащане по каса");
                
                $url['operationSysId'] = 'nonecash2bank';
                $toolbar->addLink('Инкасиране(Банка)', $url, "ef_icon = img/16/own-bank.png,title=Създаване на вътрешно касов трансфер  за инкасиране на безналично плащане по банка");
                $row->buttons = $toolbar->renderHtml(2);
             }
        }

        $cardPaymentId = pos_Setup::get('CARD_PAYMENT_METHOD_ID');
        if($rec->paymentId == $cardPaymentId){
            if(!empty($rec->param) && !Mode::isReadOnly()){
                $paramString = ($rec->param == 'card') ? "<span style='color:blue;'>" . tr('потвърдено') . "</span>" : "<span style='color:red;'>" . tr('ръчно') . "</span>";
                $row->paymentId .= " ({$paramString})";
            }
        }

    }
    
    
    /**
     * Рендиране на детайла
     *
     * @param stdClass $data
     *
     * @return core_ET $tpl
     */
    public function renderDetail_($data)
    {
        $tpl = new core_ET('');
        $block = getTplFromFile('cash/tpl/NonCashPayments.shtml');
        
        if (countR($data->rows)) {
            foreach ($data->rows as $row) {
                $clone = clone $block;
                $clone->placeObject($row);
                $tpl->append($clone);
            }
        }
        
        return $tpl;
    }
    
    
    /**
     * Връща разрешените методи за плащане
     *
     * @param core_ObjectReference $document
     *
     * @return array $res
     */
    public static function getPaymentsTableArr($documentId, $documentClassId)
    {
        $res = array();
        
        // Взимане на методите за плащане към самия документ
        $query = self::getQuery();
        if(isset($documentId)){
            $query->where("#documentId = {$documentId}");
            while ($rec = $query->fetch()) {
                $res['paymentId'][] = $rec->paymentId;
                $res['amount'][] = $rec->amount;
                $res['id'][] = $rec->id;
            }
        }
        
        return $res;
    }
    
    
    /**
     * Валидира таблицата с плащания
     * 
     * @param mixed $tableData
     * @param core_Type $Type
     * @return void|string|array
     */
    public static function validatePayments($tableData, $Type)
    {
        $tableData = (array) $tableData;
        if (empty($tableData)) {
            
            return;
        }
        
        $res = $payments = $error = $errorFields = array();
        
        foreach ($tableData['paymentId'] as $key => $paymentId) {
            if (!empty($paymentId) && empty($tableData['amount'][$key])) {
                $error[] = 'Липсва сума при избран метод';
                $errorFields['amount'][$key] = 'Липсва сума при избран метод';
            }
            
            if (array_key_exists($paymentId, $payments)) {
                $error[] = 'Повтарящ се метод';
                $errorFields['zone'][$key] = 'Повтаряща се метод';
            } else {
                $payments[$paymentId] = $paymentId;
            }
        }
        
        foreach ($tableData['amount'] as $key => $quantity) {
            if (!empty($quantity) && empty($tableData['paymentId'][$key])) {
                $error[] = 'Зададено количество без зона';
                $errorFields['amount'][$key] = 'Зададено количество без зона';
            }
            
            if (empty($quantity)) {
                $error[] = 'Количеството не може да е 0';
                $errorFields['amount'][$key] = 'Количеството не може да е 0';
            }
            
            $Double = core_Type::getByName('double');
            $q2 = $Double->fromVerbal($quantity);
            if (!$q2) {
                $error[] = 'Невалидно количество';
                $errorFields['amount'][$key] = 'Невалидно количество';
            }
        }
        
        if (countR($error)) {
            $error = implode('|*<li>|', $error);
            $res['error'] = $error;
        }
        
        if (countR($errorFields)) {
            $res['errorFields'] = $errorFields;
        }
        
        return $res;
    }
}
