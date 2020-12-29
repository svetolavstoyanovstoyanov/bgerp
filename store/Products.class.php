<?php


/**
 * Клас 'store_Products' за наличните в склада артикули
 * Данните постоянно се опресняват от баланса
 *
 *
 * @category  bgerp
 * @package   store
 *
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2018 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 */
class store_Products extends core_Detail
{
    
    
    /**
     * Каква да е максималната дължина на стринга за пълнотекстово търсене
     *
     * @see plg_Search
     */
    public $maxSearchKeywordLen = 13;
    
    
    /**
     * Ключ с който да се заключи ъпдейта на таблицата
     */
    const SYNC_LOCK_KEY = 'syncStoreProducts';
    
    
    /**
     * Заглавие
     */
    public $title = 'Наличности';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'plg_Created, store_Wrapper, plg_StyleNumbers, plg_Sorting, plg_AlignDecimals2, plg_State';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'no_one';
    
    
    /**
     * Кой може да го разглежда?
     */
    public $canList = 'ceo,sales,storeWorker';
    
    
    /**
     * Кой има право да добавя?
     */
    public $canAdd = 'no_one';
    
    
    /**
     * Кой може да го изтрие?
     */
    public $canDelete = 'no_one';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = 'code=Код,productId=Артикул,measureId=Мярка,quantity,reservedQuantity,expectedQuantity,freeQuantity,expectedQuantityTotal,storeId';
    
    
    /**
     * Име на поле от модела, външен ключ към мастър записа
     */
    public $masterKey = 'storeId';
    
    
    /**
     * Задължително филтър по склад
     */
    protected $mandatoryStoreFilter = false;
    
    
    /**
     * Флаг за обновяване на наличностите на шътдаун
     */
    public $updateOnShutdown = false;
    
    
    /**
     * Описание на модела (таблицата)
     */
    public function description()
    {
        $this->FLD('productId', 'key(mvc=cat_Products,select=name)', 'caption=Артикул,tdClass=leftAlign');
        $this->FLD('storeId', 'key(mvc=store_Stores,select=name,allowEmpty)', 'caption=Склад,tdClass=storeCol leftAlign');
        $this->FLD('quantity', 'double(maxDecimals=3)', 'caption=Налично');
        $this->FLD('reservedQuantity', 'double(maxDecimals=3)', 'caption=Запазено (днес)');
        $this->FLD('reservedQuantity2', 'double(maxDecimals=3)', 'caption=Запазено 2');
        $this->FLD('reservedQuantity3', 'double(maxDecimals=3)', 'caption=Запазено 3');
        $this->FLD('expectedQuantity', 'double(maxDecimals=3)', 'caption=Очаквано (днес)');
        $this->FLD('expectedQuantity2', 'double(maxDecimals=3)', 'caption=Очаквано 2');
        $this->FLD('expectedQuantityTotal', 'double(maxDecimals=3)', 'caption=Очаквано 3,tdClass=notBolded');
        $this->FNC('freeQuantity', 'double(maxDecimals=3)', 'caption=Разполагаемо');
        $this->FLD('state', 'enum(active=Активирано,closed=Изчерпано)', 'caption=Състояние,input=none');
        
        $this->setDbUnique('productId, storeId');
        $this->setDbIndex('productId');
        $this->setDbIndex('storeId');
    }
    
    
    /**
     * Преди подготовката на записите
     */
    protected static function on_BeforePrepareListPager($mvc, &$res, $data)
    {
        $mvc->listItemsPerPage = (isset($data->masterMvc)) ? 70 : 20;
    }
    
    
    /**
     * След преобразуване на записа в четим за хора вид.
     */
    protected static function on_AfterPrepareListRows($mvc, $data)
    {
        // Ако няма никакви записи - нищо не правим
        if (!countR($data->recs)) {
            
            return;
        }
        $isDetail = isset($data->masterMvc);
        
        foreach ($data->rows as $id => &$row) {
            $rec = &$data->recs[$id];
            $row->productId = cat_Products::getVerbal($rec->productId, 'name');
            $icon = cls::get('cat_Products')->getIcon($rec->productId);
            $row->productId = ht::createLink($row->productId, cat_Products::getSingleUrlArray($rec->productId), false, "ef_icon={$icon}");
            $pRec = cat_Products::fetch($rec->productId, 'code,isPublic,createdOn');
            $row->code = cat_Products::getVerbal($pRec, 'code');
            
            if ($isDetail) {
                   
                // Показване на запазеното количество
                $basePack = key(cat_Products::getPacks($rec->productId));
                if ($pRec = cat_products_Packagings::getPack($rec->productId, $basePack)) {
                    $rec->quantity /= $pRec->quantity;
                    $row->quantity = $mvc->getFieldType('quantity')->toVerbal($rec->quantity);
                    if (isset($rec->reservedQuantity)) {
                        $rec->reservedQuantity /= $pRec->quantity;
                    }
                }
                $rec->measureId = $basePack;
                
                // Линк към хронологията
                if (acc_BalanceDetails::haveRightFor('history')) {
                    $to = dt::today();
                    $from = dt::mysql2verbal($to, 'Y-m-1', null, false);
                    $histUrl = array('acc_BalanceHistory', 'History', 'fromDate' => $from, 'toDate' => $to, 'accNum' => 321);
                    $histUrl['ent1Id'] = acc_Items::fetchItem('store_Stores', $rec->storeId)->id;
                    $histUrl['ent2Id'] = acc_Items::fetchItem('cat_Products', $rec->productId)->id;
                    $histUrl['ent3Id'] = null;
                    $row->history = ht::createLink('', $histUrl, null, 'title=Хронологична справка,ef_icon=img/16/clock_history.png');
                }
            } else {
                $rec->measureId = cat_Products::fetchField($rec->productId, 'measureId');
            }
            
            $row->storeId = store_Stores::getHyperlink($rec->storeId, true);
            $rec->freeQuantity = $rec->quantity - $rec->reservedQuantity + $rec->expectedQuantity;
            $row->freeQuantity = $mvc->getFieldType('freeQuantity')->toVerbal($rec->freeQuantity);
            $row->measureId = cat_UoM::getTitleById($rec->measureId);
        }
    }
    
    
    /**
     * След подготовка на филтъра
     *
     * @param core_Mvc $mvc
     * @param stdClass $data
     */
    protected static function on_AfterPrepareListFilter($mvc, $data)
    {
        if($data->masterMvc instanceof cat_Products){
            $data->query->EXT('storeName', 'store_Stores', 'externalName=name,externalKey=storeId');
            
            if($data->masterData->rec->generic == 'yes'){
                $equivalent = planning_GenericMapper::getEquivalentProducts($data->masterId);
                if(countR($equivalent) > 1){
                    $data->query->in('productId', array_keys($equivalent), false, true);
                    $data->query->orderBy('productId', 'ASC');
                }
            } else {
                $data->query->orderBy('storeName', 'ASC');
            }
            
            return;
        }
        
        // Подготвяме формата
        cat_Products::expandFilter($data->listFilter);
        $orderOptions = arr::make('all=Всички,active=Активни,standard=Стандартни,private=Нестандартни,last=Последно добавени,eproduct=Артикул в Е-маг,closed=Изчерпани,reserved=Запазени,free=Разполагаеми');
        if(!core_Packs::isInstalled('eshop')){
            unset($orderOptions['eproduct']);
        }
        
        $data->listFilter->setOptions('order', $orderOptions);
        $data->listFilter->FNC('horizon', 'date', 'placeholder=Хоризонт,caption=Хоризонт,input,recently');
        $data->listFilter->FNC('search', 'varchar', 'placeholder=Търсене,caption=Търсене,input,silent,recently');
        
        $stores = cls::get('store_Stores')->makeArray4Select('name', "#state != 'rejected'");
        $data->listFilter->setOptions('storeId', array('' => '') + $stores);
        $data->listFilter->setField('storeId', 'autoFilter');
        
        if ($mvc->mandatoryStoreFilter === true) {
            $storeId = store_Stores::getCurrent();
            $data->listFilter->setDefault('storeId', $storeId);
            $data->listFilter->setField('storeId', 'input=hidden');
        } else {
            if (countR($stores) == 1) {
                $data->listFilter->setDefault('storeId', key($stores));
            }
            
            if ($storeId = store_Stores::getCurrent('id', false)) {
                $data->listFilter->setDefault('storeId', $storeId);
            }
        }
        
        // Подготвяме в заявката да може да се търси по полета от друга таблица
        $data->query->EXT('keywords', 'cat_Products', 'externalName=searchKeywords,externalKey=productId');
        $data->query->EXT('isPublic', 'cat_Products', 'externalName=isPublic,externalKey=productId');
        $data->query->EXT('code', 'cat_Products', 'externalName=code,externalKey=productId');
        $data->query->EXT('groups', 'cat_Products', 'externalName=groups,externalKey=productId');
        $data->query->EXT('name', 'cat_Products', 'externalName=name,externalKey=productId');
        $data->query->EXT('productCreatedOn', 'cat_Products', 'externalName=createdOn,externalKey=productId');

        if (isset($data->masterMvc)) {
            $data->listFilter->setDefault('order', 'all');
            $data->listFilter->showFields = 'horizon,search,groupId';
        } else {
            $data->listFilter->layout = new ET(tr('|*' . getFileContent('acc/plg/tpl/FilterForm.shtml')));
            $data->listFilter->setDefault('order', 'active');
            $data->listFilter->showFields = 'search,storeId,order,groupId,horizon';
            unset($data->listFilter->view);
        }
        
        $data->listFilter->input('horizon,storeId,order,groupId,search', 'silent');

        // Ако има филтър
        if ($rec = $data->listFilter->rec) {
            
            // И е избран склад, търсим склад
            if (!isset($data->masterMvc)) {
                if (isset($rec->storeId)) {
                    $selectedStoreName = store_Stores::getHyperlink($rec->storeId, true);
                    $data->title = "|Наличности в склад|* <b style='color:green'>{$selectedStoreName}</b>";
                    $data->query->where("#storeId = {$rec->storeId}");
                } elseif (countR($stores)) {
                    // Под всички складове се разбира само наличните за избор от потребителя
                    $data->query->in('storeId', array_keys($stores));
                } else {
                    // Ако няма налични складове за избор не вижда нищо
                    $data->query->where('1 = 2');
                }
            }
            
            // Ако се търси по ключови думи, търсим по тези от външното поле
            if (isset($rec->search)) {
                plg_Search::applySearch($rec->search, $data->query, 'keywords');
                
                // Ако ключовата дума е число, търсим и по ид
                if (type_Int::isInt($rec->search)) {
                    $data->query->orWhere("#productId = {$rec->search}");
                }
            }
            
            // Подредба
            if (isset($rec->order)) {
                switch ($data->listFilter->rec->order) {
                    case 'all':
                        break;
                    case 'private':
                        $data->query->where("#isPublic = 'no'");
                        break;
                    case 'last':
                          $data->query->orderBy('#createdOn=DESC');
                        break;
                    case 'closed':
                        $data->query->where("#state = 'closed'");
                        break;
                    case 'active':
                        $data->query->where("#state != 'closed'");
                        break;
                    case 'reserved':
                        $data->query->where("#reservedQuantity IS NOT NULL");
                        break;
                    case 'eproduct':
                        $eProductArr = eshop_Products::getProductsInEshop();
                        if(countR($eProductArr)){
                            $data->query->in("productId", $eProductArr);
                        } else {
                            $data->query->where("1=2");
                        }
                        break;
                    case 'free':
                        $data->query->XPR('free', 'double', 'ROUND(COALESCE(#quantity, 0) - COALESCE(#reservedQuantity, 0), 2)');
                        $data->query->orderBy('free', 'ASC');
                        break;
                    default:
                        $data->query->where("#isPublic = 'yes'");
                        break;
                }

                if(!empty($rec->horizon)){
                    $data->horizon = $rec->horizon;
                    $horizonVerbal = dt::mysql2verbal($rec->horizon, 'd.m.Y');

                    // Добавяне в лист изгледа
                    $after = ($data->masterMvc) ? 'expectedQuantityTotal' : 'storeId';
                    arr::placeInAssocArray($data->listFields, array('reservedOut' => "|*{$horizonVerbal}->|Запазено|*"), null, $after);
                    arr::placeInAssocArray($data->listFields, array('expectedIn' => "|*{$horizonVerbal}->|Очаквано|*"), null, 'reservedOut');

                    $mvc->FNC('reservedOut', 'double');
                    $mvc->FNC('expectedIn', 'double');
                }
            }
            
            $data->query->orderBy('#state,#code');
            
            // Филтър по групи на артикула
            if (!empty($rec->groupId)) {
                $data->query->where("LOCATE('|{$rec->groupId}|', #groups)");
            }
        }
    }


    /**
     * След извличане на записите
     */
    protected static function on_AfterPrepareListRecs($mvc, &$res, $data)
    {
        if(empty($data->horizon) || !countR($data->recs)) return;
        $productIds = arr::extractValuesFromArray($data->recs, 'productId');
        $storeIds = arr::extractValuesFromArray($data->recs, 'storeId');

        $reserved = store_StockPlanning::getPlannedQuantities($data->horizon, $productIds, $storeIds);
        if(!countR($reserved)) return;

        foreach ($data->recs as &$rec){
            if(isset($reserved[$rec->storeId][$rec->productId])){
                $rec->reservedOut = $reserved[$rec->storeId][$rec->productId]->reserved;
                $rec->expectedIn = $reserved[$rec->storeId][$rec->productId]->expected;
            }
        }
    }


    /**
     * Синхронизиране на запис от счетоводството с модела, Вика се от крон-а
     * (@see acc_Balances::cron_Recalc)
     *
     * @param array $all - масив идващ от баланса във вида:
     *                   array('store_id|class_id|product_Id' => 'quantity')
     */
    public static function sync($all)
    {
        $query = self::getQuery();
        $query->show('productId,storeId,quantity,state');
        $oldRecs = $query->fetchAll();
        $self = cls::get(get_called_class());
        
        $arrRes = arr::syncArrays($all, $oldRecs, 'productId,storeId', 'quantity');
        
        if (!core_Locks::get(self::SYNC_LOCK_KEY, 60, 1)) {
            self::logWarning('Синхронизирането на складовите наличности е заключено от друг процес');
            
            return;
        }
        
        $self->saveArray($arrRes['insert']);
        $self->saveArray($arrRes['update'], 'id,quantity');
        
        // Ъпдейт на к-та на продуктите, имащи запис но липсващи в счетоводството
        self::updateMissingProducts($arrRes['delete']);
        
        // Поправка ако случайно е останал някой артикул с к-во в затворено състояние
        $fixQuery = self::getQuery();
        $fixQuery->where("#quantity != 0 AND #state = 'closed'");
        $fixQuery->show('id,state');
        while ($fRec = $fixQuery->fetch()) {
            $fRec->state = 'active';
            self::save($fRec, 'state');
        }
        
        core_Locks::release(self::SYNC_LOCK_KEY);
    }
    
    
    /**
     * Ф-я която ъпдейтва всички записи, които присъстват в модела,
     * но липсват в баланса
     *
     * @param array $array - масив с данни за наличните артикул
     */
    private static function updateMissingProducts($array)
    {
        // Всички записи, които са останали но не идват от баланса
        $query = static::getQuery();
        $query->show('productId,storeId,quantity,state,reservedQuantity');
        
        // Зануляваме к-та само на тези продукти, които още не са занулени
        $query->where("#state = 'active'");
        if (countR($array)) {
            
            // Маркираме като затворени, всички които не са дошли от баланса или имат количества 0
            $query->in('id', $array);
            $query->orWhere('#quantity = 0');
        }
        
        if (!countR($array)) {
            
            return;
        }
        
        // За всеки запис
        while ($rec = $query->fetch()) {
            
            // К-то им се занулява и състоянието се затваря
            if (empty($rec->reservedQuantity)) {
                $rec->state = 'closed';
            }
            
            $rec->quantity = 0;
            
            // Обновяване на записа
            static::save($rec, 'state,quantity');
        }
    }
    
    
    /**
     * Колко е количеството на артикула в складовете
     *
     * @param int      $productId    - ид на артикул
     * @param int|NULL $storeId      - конкретен склад, NULL ако е във всички
     * @param bool     $freeQuantity - FALSE за общото количество, TRUE само за разполагаемото (общо - запазено)
     *
     * @return float $sum          - сумата на количеството, общо или разполагаемо
     */
    public static function getQuantity($productId, $storeId = null, $freeQuantity = false)
    {
        $query = self::getQuery();
        $query->where("#productId = {$productId}");
        $query->show('sum');
        
        if (isset($storeId)) {
            $query->where("#storeId = {$storeId}");
        }
        
        if ($freeQuantity === true) {
            $query->XPR('sum', 'double', 'SUM(#quantity - COALESCE(#reservedQuantity, 0) + COALESCE(#expectedQuantity, 0))');
        } else {
            $query->XPR('sum', 'double', 'SUM(#quantity)');
        }
        
        $calcedSum = $query->fetch()->sum;
        $sum = (!empty($calcedSum)) ? $calcedSum : 0;
        
        return $sum;
    }
    
    
    /**
     * След подготовка на тулбара на списъчния изглед
     *
     * @param core_Mvc $mvc
     * @param stdClass $data
     */
    protected static function on_AfterPrepareListToolbar($mvc, &$data)
    {
        if (haveRole('debug')) {
            if (isset($data->masterMvc)) {
                
                return;
            }
            $data->toolbar->addBtn('Изчистване', array($mvc, 'truncate'), 'warning=Искате ли да изчистите таблицата, ef_icon=img/16/sport_shuttlecock.png, title=Изтриване на таблицата с продукти');
        }
    }
    
    
    /**
     * Преди подготовката на полетата за листовия изглед
     */
    protected static function on_AfterPrepareListFields($mvc, &$res, &$data)
    {
        $data->listFields['reservedQuantity'] = "|Запазено|*<span class='small notBolded'> |*днес|*</span>";
        $data->listFields['expectedQuantity'] = "|Очаквано|*<span class='small notBolded'> |*днес|*</span>";
        $data->listFields['expectedQuantityTotal'] = "<span class='notBolded'>|Очаквано|*";
        $historyBefore = 'code';
        
        if (isset($data->masterMvc)) {
            if($data->masterMvc instanceof cat_Products){
                arr::placeInAssocArray($data->listFields, array('storeId' => 'Склад|*'), null, 'code');
                
               
                if($data->masterData->rec->generic == 'yes'){
                    $data->listFields = array('code' => 'Код', 'productId' => 'Артикул') + $data->listFields;
                } else {
                    unset($data->listFields['code']);
                    unset($data->listFields['productId']);
                    $historyBefore = 'storeId';
                }
            } else {
                unset($data->listFields['storeId']);
            }
            
            if (acc_BalanceDetails::haveRightFor('history')) {
                arr::placeInAssocArray($data->listFields, array('history' => ' '), $historyBefore);
            }
        }
    }
    
    
    /**
     * Изчиства записите в склада
     */
    public function act_Truncate()
    {
        requireRole('debug');
        
        // Изчистваме записите от моделите
        store_Products::truncate();
        
        return new Redirect(array($this, 'list'));
    }
    
    
    /**
     * Проверяваме дали колонката с инструментите не е празна, и ако е така я махаме
     */
    protected static function on_BeforeRenderListTable($mvc, &$res, $data)
    {
        $data->listTableMvc->FLD('code', 'varchar', 'tdClass=small-field');
        $data->listTableMvc->FLD('measureId', 'varchar', 'tdClass=centered');
        $data->listTableMvc->setField('expectedQuantityTotal', 'tdClass=expectedTotalCol');
        
        if (!countR($data->rows)) {
            
            return;
        }

        $today = dt::today();
        $horizon2 = store_Setup::get('STOCK_HORIZON_2');
        $date2 = dt::addSecs($horizon2, $today, false);
        $horizon3 = store_Setup::get('STOCK_HORIZON_3');
        $date3 = dt::addSecs($horizon3, $today, false);

        foreach ($data->rows as $id => &$row) {
            $rec = $data->recs[$id];

            foreach (array('reservedQuantity', 'reservedQuantity2', 'reservedQuantity3', 'expectedQuantity', 'expectedQuantity2', 'expectedQuantityTotal', 'reservedOut', 'expectedIn') as $type){
                if (!empty($rec->{$type})) {
                    $title = 'От кои документи е сформирано количеството';
                    $date = in_array($type, array('reservedQuantity', 'expectedQuantity')) ? $today : (in_array($type, array('reservedQuantity2', 'expectedQuantity2')) ? $date2 : (in_array($type, array('reservedQuantity3', 'expectedQuantityTotal')) ? $date3 : $data->horizon));

                    $tooltipUrl = toUrl(array('store_Products', 'ShowReservedDocs', 'id' => $rec->id, 'field' => $type, 'date' => $date), 'local');
                    $arrowImg = ht::createElement('img', array('src' => sbf('img/16/info-gray.png', '')));
                    $arrow = ht::createElement('span', array('class' => 'anchor-arrow tooltip-arrow-link', 'data-url' => $tooltipUrl, 'title' => $title), $arrowImg, true);
                    $arrow = "<span class='additionalInfo-holder'><span class='additionalInfo' id='{$type}{$rec->id}'></span>{$arrow}</span>";
                    $row->{$type} = "<span class='fleft'>{$arrow} </span>". $row->{$type};
                }
            }
        }
    }
    
    
    /**
     * Преди подготовката на ключовете за избор
     */
    protected static function on_BeforePrepareKeyOptions($mvc, &$options, $typeKey, $where = '')
    {
        $storeId = store_Stores::getCurrent();
        $query = self::getQuery();
        if ($where) {
            $query->where($where);
        }
        while ($rec = $query->fetch("#storeId = {$storeId}  AND #state = 'active'")) {
            $options[$rec->id] = cat_Products::getTitleById($rec->productId, false);
        }
        
        if (!countR($options)) {
            $options[''] = '';
        }
    }


    /**
     * Обновяване на резервираните наличности по крон
     */
    public function cron_CalcReservedQuantity()
    {
        $queue = array();
        $date1 = dt::today();
        $reserved1 = store_StockPlanning::getPlannedQuantities($date1);
        unset($reserved1[null]);
        $queue[] = (object)array('quantities' => $reserved1, 'fieldReserved' => 'reservedQuantity', 'fieldExpected' => 'expectedQuantity');

        $horizon2 = store_Setup::get('STOCK_HORIZON_2');
        $date2 = dt::addSecs($horizon2, $date1, false);
        $reserved2 = store_StockPlanning::getPlannedQuantities($date2);
        unset($reserved2[null]);
        $queue[] = (object)array('quantities' => $reserved2, 'fieldReserved' => 'reservedQuantity2', 'fieldExpected' => 'expectedQuantity2');

        $horizon3 = store_Setup::get('STOCK_HORIZON_3');
        $date3 = dt::addSecs($horizon3, $date1, false);
        $reserved3 = store_StockPlanning::getPlannedQuantities($date3);
        unset($reserved3[null]);
        $queue[] = (object)array('quantities' => $reserved3, 'fieldReserved' => 'reservedQuantity3', 'fieldExpected' => 'expectedQuantityTotal');

        $result = array();
        foreach ($queue as $object) {
            foreach ($object->quantities as $arr) {
                foreach ($arr as $o) {
                    $key = "{$o->storeId}|{$o->productId}";
                    if (!array_key_exists($key, $result)) {
                        $result[$key] = (object) array('storeId' => $o->storeId, 'productId' => $o->productId, 'state' => 'active');
                    }

                    $result[$key]->{$object->fieldReserved} = ($o->reserved) ? $o->reserved : null;
                    $result[$key]->{$object->fieldExpected} = ($o->expected) ? $o->expected : null;
                }
            }
        }

        $storeQuery = static::getQuery();
        $oldRecs = $storeQuery->fetchAll();

        // Синхронизират се новите със старите записи
        $res = arr::syncArrays($result, $oldRecs, 'storeId,productId', 'reservedQuantity,reservedQuantity2,reservedQuantity3,expectedQuantity,expectedQuantity2,expectedQuantityTotal');

        // Заклюване на процеса
        if (!core_Locks::get(self::SYNC_LOCK_KEY, 60, 1)) {
            $this->logWarning('Синхронизирането на складовите наличности е заключено от друг процес');
            
            return;
        }
        
        // Добавяне и ъпдейт на резервираното количество на новите
        $this->saveArray($res['insert']);
        $this->saveArray($res['update'], 'id,reservedQuantity,reservedQuantity2,reservedQuantity3,expectedQuantity,expectedQuantity2,expectedQuantityTotal');

        // Намиране на тези записи, от старите които са имали резервирано к-во, но вече нямат
        $unsetArr = array_filter($oldRecs, function (&$r) use ($result) {
            if (!isset($r->reservedQuantity) && !isset($rec->reservedQuantity2) && !isset($rec->reservedQuantity3) && !isset($r->expectedQuantity) && !isset($r->expectedQuantity2) && !isset($r->expectedQuantityTotal)) {

                return false;
            }
            if (array_key_exists("{$r->storeId}|{$r->productId}", $result)) {
                
                return false;
            }

            foreach (arr::make('reservedQuantity,reservedQuantity2,reservedQuantity3,expectedQuantity,expectedQuantity2,expectedQuantityTotal', true) as $fld){
                if(isset($r->{$fld})){
                    $r->{$fld} = null;
                }
            }
            
            return true;
        });

        // Техните резервирани количества се изтриват
        if (countR($unsetArr)) {
            $this->saveArray($unsetArr, 'id,reservedQuantity,reservedQuantity2,reservedQuantity3,expectedQuantity,expectedQuantity2,expectedQuantityTotal');
        }
        
        // Освобождаване на процеса
        core_Locks::release(self::SYNC_LOCK_KEY);
    }
    
    
    /**
     * Показва информация за резервираните количества
     */
    public function act_ShowReservedDocs()
    {
        requireRole('powerUser');
        $id = Request::get('id', 'int');
        $field = Request::get('field', 'varchar');
        $horizon = Request::get('date', 'date');
        expect($rec = self::fetch($id));

        $start = "{$horizon} 00:00:00";
        $end = "{$horizon} 23:59:59";
        $query = store_StockPlanning::getQuery();
        $query->where("#productId = {$rec->productId} AND #storeId = {$rec->storeId} AND #date BETWEEN '{$start}' AND '{$end}'");

        $quantityField = (strpos($field, 'reserved') !== false) ? 'quantityOut' : 'quantityIn';
        $query->where("#{$quantityField} IS NOT NULL");
        $query->show('sourceClassId,sourceId,date');

        $links = '';
        while($dRec = $query->fetch()){
            $Source = cls::get($dRec->sourceClassId);
            $row = (object)array('date' => dt::mysql2verbal($dRec->date));

            // Ако източника е документ - показват се данните му
            if($Source->hasPlugin('doc_DocumentPlg')){
                $row->link = $Source->getLink($dRec->sourceId, 0);
                $docRec = $Source->fetch($dRec->sourceId, 'createdBy,folderId');
                $row->createdBy = crm_Profiles::createLink($docRec->createdBy);
                $folderId = doc_Folders::recToVerbal(doc_Folders::fetch($docRec->folderId))->title;
                $row->createdBy .= " | {$folderId}";
            } else {

                // Ако източника не е документ
                $row->link = $Source->getHyperlink($dRec->sourceId, true);
                $createdBy = $Source->fetchField($dRec->sourceId, 'createdBy');
                $row->createdBy = crm_Profiles::createLink($createdBy);
            }

            // Подготвяне на реда с информация
            $link = new core_ET("<div style='float:left'>[#link#] | [#createdBy#]<!--ET_BEGIN date--> | [#date#]<!--ET_END date--></div>");
            $link->placeObject($row);
            $links .= $link->getContent();
        }

        $tpl = new core_ET($links);

        if (Request::get('ajax_mode')) {
            $resObj = new stdClass();
            $resObj->func = 'html';
            $resObj->arg = array('id' => "{$field}{$id}", 'html' => $tpl->getContent(), 'replace' => true);
            
            return array($resObj);
        }
        
        return $tpl;
    }
    
    
    /**
     * Изчисляване на готовноста на складовите документи на заявка 
     */
    public function cron_UpdateShipmentDocumentReadiness()
    {
        // За всички ЕН и МСТ
        foreach (array('store_ShipmentOrders' => 'store_ShipmentOrderDetails', 'store_Transfers' => 'store_TransfersDetails') as $Master => $Detail){
            $Master = cls::get($Master);
            $Detail = cls::get($Detail);
            $storeField = ($Master instanceof store_ShipmentOrders) ? 'storeId' : 'fromStore';
            
            // Тези които са на заявка
            $query = $Master->getQuery();
            $query->where("#state = 'pending'");
            $query->show("id,storeReadiness,{$storeField}");
            
            $toSave = array();
            while($rec = $query->fetch()){
                $products = $quantities = array();
                $isTransfer = ($Master instanceof store_Transfers);
                $totalValue = 0;
                
                // Сумира се какво е общото к-во и сумата му
                $dQuery = $Detail->getQuery();
                $dQuery->where("#{$Detail->masterKey} = {$rec->id}");
                $dRecs = $dQuery->fetchAll();
                
                if(countR($dRecs)){
                    array_walk($dRecs, function($a) use (&$products, $Detail, &$totalValue, $isTransfer){
                        if(!array_key_exists($a->{$Detail->productFld}, $products)){
                            $products[$a->{$Detail->productFld}] = new stdClass();
                        }
                        
                        $products[$a->{$Detail->productFld}]->quantity += $a->quantity;
                        $value = ($isTransfer) ? ($a->quantity) : ($a->quantity * $a->price);
                        $products[$a->{$Detail->productFld}]->amount += $value;
                        $totalValue += $value;
                    });
                    
                    // Колко е налично в склад от артикулите на документа
                    $storeQuery = store_Products::getQuery();
                    $storeQuery->where("#storeId = {$rec->{$storeField}}");

                    $storeQuery->in('productId', array_keys($products));
                    $storeQuery->show('productId,quantity');
                    $sRecs = $storeQuery->fetchAll();
                    array_walk($sRecs, function($a) use (&$quantities){
                        $quantities[$a->productId] += $a->quantity;
                    });
                    
                    // Колко е готовноста
                    $missingAmount = 0;
                    foreach ($products as $productId => $object){
                        $singlePrice = (!empty(round($object->quantity, 4))) ? round($object->amount / $object->quantity, 6) : 0;
                        $inStore = $quantities[$productId];
                        $inStore = (empty($inStore) || $inStore < 0) ? 0 : $inStore;
                        
                        // Каква е сумата на липсващото к-во. (За МСТ си е само количеството)
                        $missingQuantity = $object->quantity - $inStore;
                        $missingQuantity = ($missingQuantity <= 0) ? 0 : $missingQuantity;
                        $missingAmount += $missingQuantity * $singlePrice;
                    }
                    
                    // Колко е готовността, тя е 1 - сумата на липсващото к-во/ общата сума на ЕН-то (За МСТ е от липсващото общо к-во)
                    $missingAmount = round($missingAmount, 6);
                    $totalValue = round($totalValue, 6);
                    $storeReadiness = !empty($totalValue) ? (1 - round($missingAmount / $totalValue, 2)) : 0;
                    $storeReadiness = ($storeReadiness < 0) ? 0 : $storeReadiness;
                    $storeReadiness = ($storeReadiness > 1) ? 1 : $storeReadiness;
                    $rec->storeReadiness = round($storeReadiness, 2);
                } else {
                    $rec->storeReadiness = null;
                }
                
                $toSave[] = $rec;
                
                if(countR($toSave)){
                    $Master->saveArray($toSave, 'id,storeReadiness');
                }
            }
        }
    }
    
    
    /**
     * Подготовка на Детайлите
     */
    public function prepareDetail_($data)
    {
        if($data->masterMvc instanceof cat_Products){
            $data->masterKey = 'productId';
           
            $data->render = true;
            $tabParam = $data->masterData->tabTopParam;
            $prepareTab = Request::get($tabParam);
            
            if($data->masterData->rec->canStore != 'yes' || !store_Products::haveRightFor('list') || $prepareTab != 'store_Products'){
                $data->render = false;
            }
            
            if($data->masterData->rec->canStore != 'yes' || !store_Products::haveRightFor('list')){
                
                return;
            }
            
            $data->TabCaption = 'Наличности';
            $data->Tab = 'top';
        }
        
        parent::prepareDetail_($data);

        if(countR($data->recs)){
            $totalField = ($data->masterData->rec->generic == 'yes') ? 'code' : 'storeId';
            $data->rows['total'] = (object)array($totalField => "<div style='float:left'>" .  tr('Сумарно') . "</div>");
            $data->rows['total']->ROW_ATTR['style'] = 'background-color:#eee;font-weight:bold';
            
            foreach (array('quantity', 'reservedQuantity', 'expectedQuantity', 'expectedQuantityTotal', 'freeQuantity', 'reservedOut', 'expectedOut') as $fld){
                ${$fld} = arr::sumValuesArray($data->recs, $fld, true);
                $data->rows['total']->{$fld} = core_Type::getByName('double(decimals=2)')->toVerbal(${$fld});
            }
        }
    }
    
    
    /**
    * Рендиране на детайла
    */
    public function renderDetail_($data)
    {
        // Не се рендира детайла, ако има само една версия или режима е само за показване
        if ($data->render === false) {
           
            return new core_ET('');
        }
        
        $tpl = getTplFromFile('crm/tpl/ContragentDetail.shtml');
        if($data->masterData->rec->generic == 'yes'){
            $infoBlock = tr("Показани са наличностите на артикулите, които заместват|* <b class='green'>") . cat_Products::getTitleById($data->masterId) . "</b>";
            $infoBlock = "<div style='margin-bottom:5px'>{$infoBlock}</div>";
            $tpl->append($infoBlock, 'content');
        }
        
        $tpl->append(parent::renderDetail_($data), 'content');
        
        return $tpl;
    }
}
