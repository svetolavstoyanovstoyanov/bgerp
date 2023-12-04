<?php


/**
 * Клас 'bgfisc_plg_Receipts' - за добавяне на функционалност от наредба 18 към ПОС бележките
 *
 *
 * @category  bgerp
 * @package   bgfisc
 *
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2023 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 */
class bgfisc_plg_Receipts extends core_Plugin
{
    /**
     * За конвертиране на съществуващи MySQL таблици от предишни версии
     */
    public $oldClassName = 'n18_plg_Receipts';


    /**
     * След дефиниране на полетата на модела
     *
     * @param core_Mvc $mvc
     */
    public static function on_AfterDescription(core_Mvc $mvc)
    {
        setIfNot($mvc->canPrintfiscreceipt, 'pos, ceo');
        
        $mvc->FLD('stornoReason', 'text', 'caption=Сторно основание, input=none');
    }
    
    
    /**
     * Допълнителни бутони към таба за плащанията в бележката
     */
    public static function on_BeforeGetPaymentTabBtns($mvc, &$buttons, $rec)
    {
        unset($buttons['close']);
        
        $url = ($mvc->haveRightFor('printFiscReceipt', $rec)) ? array('pos_Receipts', 'printfiscreceipt', $rec->id) : array();
        $attr = array('class' => "printReceiptBtn posBtns", 'title' => 'Отпечаване на фискален бон');
        $warning = 'Наистина ли желаете ли да разпечатате фискален бон|*?';
        if(!count($url)){
            $attr['class'] .= " disabledBtn";
            $attr['disabled'] = 'disabled';
            $warning = '';
        } else {
            $attr['class'] .= " navigable";
        }
        $caseId = pos_Points::fetchField($rec->pointId, 'caseId');
        
        $attr['title'] = 'Печат на фискален бон';
        $attr['data-url'] = toUrl($url, 'local');
        $closeBtn = ht::createFnBtn('Фискален бон', '', $warning, $attr);
        $buttons["close"] = (object)array('body' => $closeBtn, 'placeholder' => 'CLOSE_BTNS');
        
        $deviceRec = bgfisc_Register::getFiscDevice($caseId);
        
        if (is_object($deviceRec)) {
            
            // Добавяне на бутони за зареждане на средства и генериране на отчети от ФУ
            $fiscDriver = peripheral_Devices::getDriver($deviceRec);
            
            if (isset($rec->revertId)) {
                unset($buttons["close"]);
                
                $reasons = $fiscDriver->getStornoReasons($rec);
                foreach ($reasons as $reason){
                    $url['stornoReason'] = urlencode($reason);
                    $attr['data-url'] = toUrl($url, 'local');
                    
                    $closeBtn = ht::createFnBtn("ФБ|*: {$reason}", '', $warning, $attr);
                    $buttons["close{$reason}"] = (object)array('body' => $closeBtn, 'placeholder' => 'CLOSE_BTNS');
                }
            }
            
            if (haveRole($fiscDriver->canPrintDuplicate)) {
                $rQuery = bgfisc_PrintedReceipts::getQuery();
                $rQuery->EXT("cashRegNum", 'bgfisc_Register', 'externalName=cashRegNum,externalKey=urnId');
                $rQuery->where("#string IS NOT NULL");
                $rQuery->orderBy('id', 'DESC');
                $lastReceipt = $rQuery->fetch();
                
                if($lastReceipt->cashRegNum == $lastReceipt->cashRegNum && $lastReceipt->classId == $mvc->getClassId() && $lastReceipt->objectId == $rec->id){
                    $closeBtn = ht::createBtn("Дубликат", array($fiscDriver, 'printduplicate', $deviceRec->id, 'ret_url' => true, 'rand' => str::getRand()), false, false, "class=printReceiptBtn posBtns navigable,title=Отпечатване на дубликат");
                    $buttons["dublicate"] = (object)array('body' => $closeBtn, 'placeholder' => 'CLOSE_BTNS');
                }
            }
            
            if (haveRole($fiscDriver->canMakeReport)) {
                $reportBtn = ht::createBtn("Отчет", array($fiscDriver, 'Reports', 'pId' => $deviceRec->id, 'ret_url' => true, 'rand' => str::getRand()), false, false, "class=printReceiptBtn posBtns navigable,title=Отпечатване на отчети");
                $buttons["report"] = (object)array('body' => $reportBtn, 'placeholder' => 'CLOSE_BTNS');
            }
            
            if (haveRole($fiscDriver->canCashReceived) || haveRole($fiscDriver->canCashPaidOut)) {
                $reportBtn = ht::createBtn("Средства", array($fiscDriver, 'CashReceivedOrPaidOut', 'pId' => $deviceRec->id, 'ret_url' => true, 'rand' => str::getRand()), false, false, "class=printReceiptBtn posBtns navigable,title=Вкарване или изкарване на пари от касата");
                $buttons["payments"] = (object)array('body' => $reportBtn, 'placeholder' => 'CLOSE_BTNS');
            }
        }
        
        return false;
    }
    
    
    /**
     * След изтриване на запис
     */
    public static function on_AfterDelete($mvc, &$numDelRows, $query, $cond)
    {
        // Ако изтриваме етап, изтриваме всичките редове от този етап
        foreach ($query->getDeletedRecs() as $rec) {
            if ($regRec = bgfisc_Register::getRec($mvc, $rec)) {
                bgfisc_Register::delete($regRec->id);
            }
        }
    }
    
    
    /**
     * Модификация на ролите
     */
    public static function on_AfterGetRequiredRoles($mvc, &$res, $action, $rec = null, $userId = null)
    {
        if ($action == 'close' && isset($rec)) {
            $hash = Request::get('hash', 'varchar');
            if (empty($hash)) {
                $res = 'no_one';
            }
        }
        
        if ($action == 'restore' && isset($rec)) {
            $res = 'no_one';
        }
        
        if (in_array($action, array('delete', 'reject'))  && isset($rec)) {
            if (bgfisc_PrintedReceipts::get($mvc, $rec->id)) {
                $res = 'no_one';
            }
        }
        
        if ($action == 'printfiscreceipt' && isset($rec)) {
            $caseId = pos_Points::fetchField($rec->pointId, 'caseId');
            
            if(bgfisc_PrintedReceipts::getQrCode($mvc, $rec->id)){
                $res = 'no_one';
            } elseif (!bgfisc_Register::getFiscDevice($caseId)) {
                $res = 'no_one';
            } elseif (!$mvc->haveRightFor('terminal', $rec)) {
                $res = 'no_one';
            } elseif (isset($rec->revertId)) {
                if (abs(round($rec->paid, 2)) != abs(round($rec->total, 2)) || empty(round($rec->total, 2))) {
                    $res = 'no_one';
                }
            } elseif (($rec->total == 0 || round($rec->paid, 2) < round($rec->total, 2))) {
                $res = 'no_one';
            }
        }
    }
    
    
    /**
     * Информация за артикулите от бележката
     *
     * @param stdClass $rec
     *
     * @return array $res
     */
    private static function getReceiptItems($rec)
    {
        $res = array();
        
        $vatClasses = array('A' => 0, 'B' => 1, 'V' => 2, 'G' => 3);
        $query = pos_ReceiptDetails::getQuery();
        $query->where("#receiptId = '{$rec->id}'");
        $query->where("#action LIKE '%sale%'");
        
        while ($dRec = $query->fetch()) {
            $name = cat_Products::getVerbal($dRec->productId, 'name');
            $price = $dRec->price * (1 + $dRec->param);
            $dRec->quantity = abs($dRec->quantity);
            $amount = $price * $dRec->quantity;
            
            $arr = array('PLU_NAME' => $name, 'QTY' => 1, 'PRICE' => round($amount, 2));
            if (!empty($dRec->discountPercent)) {
                $arr['DISC_ADD_V'] = -1 * round($dRec->discountPercent * $amount, 2);
            }
            
            $vatSysId = cat_products_VatGroups::getCurrentGroup($dRec->productId)->sysId;
            $arr['VAT_CLASS'] = (!empty($vatSysId)) ? $vatClasses[$vatSysId] : $vatClasses['B'];
            
            $price = round($amount / $dRec->quantity, bgfisc_Setup::get('N18_PRICE_FU_ROUND', true));
            $arr['BEFORE_PLU_TEXT'] = "{$dRec->quantity}x{$price}лв";
            
            $res[] = $arr;
        }
        
        return $res;
    }
    
    
    /**
     * Информация за плащанията в бележката
     *
     * @param stdClass $rec
     *
     * @return array $res
     */
    private static function getReceiptPayments($rec, $Driver, $driverRec)
    {
        $res = array();
        
        $query = pos_ReceiptDetails::getQuery();
        $query->where("#receiptId = '{$rec->id}'");
        $query->where("#action LIKE '%payment%'");
        $query->show('action,amount');
        $errors = array();
        
        while ($dRec = $query->fetch()) {
            list(, $paymentType) = explode('|', $dRec->action);
            if ($paymentType != -1) {
                $paymentCode = $Driver->getPaymentCode($driverRec, $paymentType);
                if(isset($paymentCode)){
                    if (!array_key_exists($paymentType, $res)) {
                        $res[$paymentCode] = array('PAYMENT_TYPE' => $paymentCode, 'PAYMENT_AMOUNT' => 0);
                    }
                    $res[$paymentCode]['PAYMENT_AMOUNT'] += abs($dRec->amount);
                } else {
                    $errors[] = cond_Payments::getTitleById($paymentType);
                }
            }
        }
        
        if (count($errors)) {
            $msg = 'Следните плащания нямат код във ФУ|*: ' . implode(',', $errors);
            throw new core_exception_Expect($msg, 'Несъответствие');
        }
        
        
        return $res;
    }
    
    
    /**
     * След рендиране на хедъра на терминала
     */
    public static function on_AfterRenderTerminalHeader($mvc, &$tpl, $rec)
    {
        try{
            $urn = self::getReceiptUrn($rec);
            $tpl->replace($urn, 'SUB_TITLE');
        } catch(core_exception_Expect $e){
            $regRec = bgfisc_Register::createUrn($mvc, $rec->id, false);
            redirect(getCurrentUrl(), false, "Създаване УНП на стара бележка|* <b>{$regRec->urn}<b>");
        }
    }
    
    
    /**
     * След взимане на файловете за пушване към терминала
     *
     * @param core_Mvc $mvc
     * @param mixed    $res
     * @param core_ET  $tpl
     */
    public static function on_AfterPushTerminalFiles($mvc, &$tpl, $rec)
    {
        // Добавяне на файл с допълнителен скрипт
        if (!Mode::is('printing')) {
            $tpl->push('bgfisc/js/Receipt.js', 'JS');
            jquery_Jquery::run($tpl, 'fiscActions();');
        }
    }
    
    
    /**
     * Извиква се преди изпълняването на екшън
     */
    public static function on_BeforeAction($mvc, &$res, $action)
    {
        // Ако ще се отпечатва фискален бон
        if ($action == 'printfiscreceipt') {
            
            try {
                
                if (!$mvc->requireRightFor('printfiscreceipt')) {
                    throw new core_exception_Expect('Нямате права', 'Несъответствие');
                }
                
                if (!$id = Request::get('id', 'int')) {
                    throw new core_exception_Expect('Няма такава бележка', 'Несъответствие');
                }
                
                if (!$rec = pos_Receipts::fetch($id)) {
                    throw new core_exception_Expect('Няма такава бележка', 'Несъответствие');
                }
                
                $error = null;
                if(isset($rec->revertId) && !pos_Receipts::canCloseRevertReceipt($rec, $error)){
                    throw new core_exception_Expect($error, 'Несъответствие');
                }
                
                if (!$mvc->requireRightFor('printfiscreceipt', $rec)) {
                    throw new core_exception_Expect('Нямате права', 'Несъответствие');
                }

                bgfisc_PrintedReceipts::logPrinted($mvc, $rec->id);
                core_Locks::get("lock_{$mvc->className}_{$rec->id}", 90, 5, false);
                
                // Кое фискално устройство е свързано към компютъра
                $caseId = pos_Points::fetchField($rec->pointId, 'caseId');
                if (!$lRec = bgfisc_Register::getFiscDevice($caseId)) {
                    throw new core_exception_Expect('Няма закачено фискално устройство', 'Несъответствие');
                }
                
                $interface = core_Cls::getInterface('peripheral_FiscPrinterIntf', $lRec->driverClass);
                $Driver = cls::get($lRec->driverClass);
                
                // Какви са артикулите в бележката
                $products = self::getReceiptItems($rec);
                $payments = self::getReceiptPayments($rec, $interface, $lRec);
                
                $fiscalArr = array('products' => $products, 'IS_PRINT_VAT' => 1, 'payments' => $payments);
                
                if (isset($rec->revertId)) {
                    $revertId = ($rec->revertId == pos_Receipts::DEFAULT_REVERT_RECEIPT) ? $rec->id : $rec->revertId;
                    $receiptNumber = bgfisc_Register::getSaleNumber($mvc, $revertId);
                   
                    $fiscalArr['IS_STORNO'] = true;
                    
                    $stornoReason = Request::get('stornoReason', 'varchar');
                    
                    $reasonCode = $Driver->getStornoReasonCode($lRec, $stornoReason);
                    if (!isset($reasonCode)) {
                        throw new core_exception_Expect('Липсва код на основанието за сторниране във ФУ', 'Несъответствие');
                    }
                    $rec->stornoReason = $stornoReason;
                    $mvc->save_($rec, 'stornoReason');
                    
                    $fiscalArr['STORNO_REASON'] = $reasonCode;
                    $fiscalArr['RELATED_TO_URN'] = $receiptNumber;
                    $fiscalArr['QR_CODE_DATA'] = bgfisc_PrintedReceipts::getQrCode($mvc, $rec->revertId);
                    //$fiscalArr['QR_CODE_DATA'] = '99999999*999999*2019-12-14*11:35:00*8.18';
                    
                    if (empty($fiscalArr['QR_CODE_DATA'])) {
                        throw new core_exception_Expect('Към оригиналната бележка няма фискален бон', 'Несъответствие');
                    }
                } else {
                    $receiptNumber = bgfisc_Register::getSaleNumber($mvc, $rec->id);
                    $fiscalArr['RCP_NUM'] = $receiptNumber;
                }
                
                if (empty($receiptNumber)) {
                    throw new core_exception_Expect('Не може да се генерира УНП', 'Несъответствие');
                }
                
                // Отпечатване на фискален бон
                Request::setProtected('hash');
                $hash = str::addHash('fiscreceipt', 4);
                $successUrl = toUrl(array($mvc, 'close', $rec->id, 'hash' => $hash));
                Request::removeProtected('hash');
                
                $fiscalArr['SERIAL_NUMBER'] = $lRec->serialNumber;
                $cu = core_Users::getCurrent();
                $fiscalArr['BEGIN_TEXT'] = 'Касиер: ' . core_Users::getVerbal($cu, 'names');
                
                if (cls::haveInterface('peripheral_FiscPrinterWeb', $Driver)) {
                    $interface = core_Cls::getInterface('peripheral_FiscPrinterWeb', $lRec->driverClass);
                    
                    $js = $interface->getJS($lRec, $fiscalArr);
                    $js .= 'blurScreen();
                    function fpOnSuccess(res)
                        {
                            $(".printReceiptBtn").removeClass( "disabledBtn");
        		            $(".printReceiptBtn").prop("disabled", false);
                                
                            document.location = " ' . $successUrl . '&res=" + res;
                        }
                                
                        function fpOnError(err) {
                            removeBlurScreen();
                            removeDisabledBtn();
                                
                            render_showToast({timeOut: 800, text: err, isSticky: true, stayTime: 8000, type: "error"});
                        }
                                
                        function pad(num, size, v) {
                            var s = num+"";
                            while (s.length < size) s = "0" + s;
                            return s;
                        }';
                    
                    if (Request::get('ajax_mode')) {
                        $resObj = new stdClass();
                        $resObj->func = 'js';
                        $resObj->arg = array('js' => $js);
                        $res = array($resObj);
                        
                        return false;
                    }
                } else {
                    
                    // Ако принтера е електронен опит за печат на е.бележка
                    $interface = core_Cls::getInterface('peripheral_FiscPrinterIp', $lRec->driverClass);
                    $result = $interface->printReceipt($lRec, $fiscalArr);
                    
                    $res = (object) array('arr' => $fiscalArr);
                    if ($result) {
                        $redirectUrl = $successUrl . "&res={$result}";
                        
                        if ($lRec->isElectronic == 'yes' && empty($rec->revertId)) {
                            list(, $receiptNum) = explode('*', $result);
                            try {
                                usleep(1000000);
                                $fh = $interface->saveReceiptToFile($lRec, $receiptNum);
                                if ($fh !== false) {
                                    $redirectUrl .= "&fh={$fh}";
                                }
                            } catch (core_exception_Expect $e) {
                                core_Statuses::newStatus('Проблем при генериране на файла на бележката', 'warning');
                                $mvc->logErr($e->getMessage(), $rec->id);
                                reportException($e);
                            }
                        }
                        
                        // Ако бележката е отпечатана редирект
                        if (Request::get('ajax_mode')) {
                            $resObj = new stdClass();
                            $resObj->func = 'redirect';
                            $resObj->arg = array('url' => $redirectUrl);
                            $res = array($resObj);
                            
                            return false;
                        }
                    } else {
                        expect(false, 'Проблем при отпечатването на уеб бележка');
                    }
                }
                
                $res = new Redirect(array('pos_Terminal', 'open', "receiptId" => $rec->id));
                
                return false;
            } catch (core_exception_Expect $e) {
                reportException($e);
                
                // Ако е по AJAX премахва се блъра и се показват статусите с грешката
                if (Request::get('ajax_mode')) {
                    $errorMsg = $e->getMessage();
                    core_Statuses::newStatus($errorMsg, 'error');
                    
                    // Првмахване на замъгляването
                    $resObj = new stdClass();
                    $resObj->func = 'removeBlurScreen';
                    
                    $resObj2 = new stdClass();
                    $resObj2->func = 'removeDisabledBtn';
                    
                    // Показваме веднага и чакащите статуси
                    $hitTime = Request::get('hitTime', 'int');
                    $idleTime = Request::get('idleTime', 'int');
                    $statusData = status_Messages::getStatusesData($hitTime, $idleTime);
                    $res = array_merge(array($resObj, $resObj2), (array) $statusData);
                    
                    return false;
                }
                expect(false, $errorMsg);
            }
        }
        
        // Ако няма закачено ФУ, показва се съобщение
        if (in_array($action, array('new', 'terminal'))) {
            if ($pointId = pos_Points::getCurrent('id', false)) {
                $caseId = pos_Points::fetchField($pointId, 'caseId');
                if (!bgfisc_Register::getFiscDevice($caseId)) {
                    $res = new Redirect(array('pos_Points', 'list'), 'Няма закачено фискално устройство|*!', 'error');
                    
                    return false;
                }
            }
        }
    }
    
    
    /**
     * Извиква се след изпълняването на екшън
     */
    public function on_AfterAction(&$mvc, &$tpl, $act)
    {
        if (strtolower($act) == 'close') {
            Request::setProtected('hash');
            if ($hash = Request::get('hash', 'varchar')) {
                $id = Request::get('id', 'int');
                $res = Request::get('res', 'varchar');
                $fh = Request::get('fh', 'varchar');
                
                $url = array('bgfisc_PrintedReceipts', 'log', 'docClassId' => $mvc->getClassId(), 'docId' => $id, 'hash' => $hash, 'res' => $res, 'fh' => $fh, 'ret_url' => array($mvc, 'new'));
                
                redirect($url);
            }
        }
    }
    
    
    /**
     * Изпълнява се след създаване на нов запис
     */
    public static function on_AfterCreate($mvc, $rec)
    {
        if (empty($rec->revertId)){
            $regRec = bgfisc_Register::createUrn($mvc, $rec->id, true);
            core_Statuses::newStatus("Създаване на бележка с УНП|* <b>{$regRec->urn}<b>");
        } elseif(isset($rec->revertId) && $rec->revertId == pos_Receipts::DEFAULT_REVERT_RECEIPT){
            $regRec = bgfisc_Register::createUrn($mvc, $rec->id, false);
            core_Statuses::newStatus("Създаване УНП на стара бележка|* <b>{$regRec->urn}<b>");
        }
    }
    
    
    /**
     * Опит за намиране на ПОС бележка по даден стринг
     */
    public function on_AfterFindReceiptByNumber($mvc, &$res, $string, $forRevert = false)
    {
        $res = array();
        $registerRec = bgfisc_Register::getRecByUrn($string);
        
        if (is_object($registerRec) && cls::load($registerRec->classId, 'true')) {
            $RegisterClass = cls::get($registerRec->classId);
            if (!($RegisterClass instanceof pos_Receipts)) {
                $res['rec'] = false;
                $res['notFoundError'] = 'УНП-то не е на POS бележка';
            } elseif (empty(bgfisc_PrintedReceipts::getQrCode($registerRec->classId, $registerRec->objectId)) && $forRevert === true) {
                $res['rec'] = false;
                $res['notFoundError'] = 'Бележката за сторниране е БЕЗ издаден фискален бон';
            } else {
                $res['rec'] = cls::get($registerRec->classId)->fetch($registerRec->objectId);
            }
        }
        
        // Ако няма нищо няма да се намира никаква бележка
        if (!count($res)) {
            $res['rec'] = false;
            $res['notFoundError'] = 'Няма бележка с такова УНП|*!';
        }
    }
    
    
    /**
     * Обработване на цената
     */
    public function on_AfterGetDisplayPrice($mvc, &$res, $priceWithoutVat, $vat, $discountPercent, $pointId, $quantity)
    {
        $caseId = pos_Points::fetchField($pointId, 'caseId');
        if ($deviceRec = bgfisc_Register::getFiscDevice($caseId)) {
            $Driver = peripheral_Devices::getDriver($deviceRec);
            $price = $Driver->getDisplayPrice($priceWithoutVat, $vat, $discountPercent, $quantity ? $quantity : 1);
            if (!empty($price)) {
                $res = $price;
            }
        }
    }
    
    
    /**
     * След преобразуване на записа в четим за хора вид.
     */
    public static function on_AfterRecToVerbal($mvc, &$row, $rec, $fields = array())
    {
        try{
            $urn = self::getReceiptUrn($rec);
        } catch(core_exception_Expect $e){
            $regRec = bgfisc_Register::createUrn($mvc, $rec->id, false);
            redirect(getCurrentUrl(), false, "Създаване УНП на стара бележка|* <b>{$regRec->urn}<b>");
        }
        
        if(!empty($urn)){
            $row->urn = bgfisc_Register::getUrlLink($urn);
        } else {
            $row->urn = ht::createHint('Прехвърлено', 'УНП-то е прехвърлено на продажбата');
        }
    }
    
    
    /**
     * Преди подготовката на полетата за листовия изглед
     */
    public static function on_AfterPrepareListFields($mvc, &$res, &$data)
    {
        arr::placeInAssocArray($data->listFields, array('urn' => 'УНП'), 'contragentName');
    }
    
    
    /**
     * Какво е УНП-то на бележката
     * 
     * @param stdClass $rec
     * @return string|null 
     */
    private static function getReceiptUrn($rec)
    {
        $rec = pos_Receipts::fetchRec($rec);
        $cashReg = bgfisc_Register::getRec('pos_Receipts', $rec->id);
        if(empty($cashReg)){
            $cashReg = bgfisc_Register::getRec('pos_Receipts', $rec->revertId);
        }
        
        return $cashReg->urn;
    }
    
    
    /**
     * След преобразуване на записа в четим за хора вид.
     */
    public static function on_AfterGetReceipt($mvc, &$tpl, $id)
    {
        $urn = self::getReceiptUrn($id);
        $tpl->append("<b>{$urn}</b>", 'SUB_TITLE');
    }
    
    
    /**
     * След рендиране на таба за плащанията
     */
    public static function on_AfterRenderPaymentTab($mvc, &$tpl, $id)
    {
        $rec = $mvc->fetchRec($id);
        $caseId = pos_Points::fetchField($rec->pointId, 'caseId');
        if ($deviceRec = bgfisc_Register::getFiscDevice($caseId)) {
            if (is_object($deviceRec)) {
                
                // Добавяне на бутони за зареждане на средства и генериране на отчети от ФУ
                $fiscDriver = peripheral_Devices::getDriver($deviceRec);
                
                if (haveRole($fiscDriver->canMakeReport)) {
                    $tpl->append(ht::createBtn('Отчети ФУ', array($fiscDriver, 'Reports', 'pId' => $deviceRec->id, 'ret_url' => true, 'rand' => str::getRand()), false, false, 'title=Отпечатване на отчети от фискалното устройство'), 'CLOSE_BTNS');
                }
                
                if (haveRole($fiscDriver->canCashReceived) || haveRole($fiscDriver->canCashPaidOut)) {
                    $tpl->append(ht::createBtn('Средства ФУ', array($fiscDriver, 'CashReceivedOrPaidOut', 'pId' => $deviceRec->id, 'ret_url' => true, 'rand' => str::getRand()), false, false, 'title=Вкарване или изкарване на пари към фискалното устройство'), 'CLOSE_BTNS');
                }
            }
        }
    }
    
    
    /**
     * Извиква се преди рендирането на 'опаковката'
     */
    public static function on_AfterRenderSingleLayout($mvc, &$tpl, $data)
    {
        foreach (array('urn' => 'УНП', 'stornoReason' => 'Сторно основание') as $fld => $caption){
            if(!empty($data->row->{$fld})){
                $block = clone $tpl->getBlock('ADDITIONAL_BLOCK');
                $block->append(tr($caption), 'ADDITIONAL_CAPTION');
                $block->append($data->row->{$fld}, 'ADDITIONAL_VALUE');
                $block->removeBlocksAndPlaces();
                $tpl->append($block, 'ADDITIONAL_BLOCK');
            }
        }
    }
    
    
    /**
     * Добавя ключови думи за пълнотекстово търсене
     */
    public static function on_AfterGetSearchKeywords($mvc, &$res, $rec)
    {
        // Добавяне на използваните платежни методи към ключовите думи
        if(isset($rec->id)){
            $cashReg = ($rec->revertId) ? bgfisc_Register::getRec($mvc, $rec->revertId) : bgfisc_Register::getRec($mvc, $rec->id);
            
            $res = ' ' . $res . ' ' . plg_Search::normalizeText($cashReg->urn);
        }
    }
}
