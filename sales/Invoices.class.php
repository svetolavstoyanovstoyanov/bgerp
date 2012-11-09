<?php



/**
 * Фактури
 *
 *
 * @category  bgerp
 * @package   sales
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class sales_Invoices extends core_Master
{
    
    
    /**
     * Поддържани интерфейси
     */
    var $interfaces = 'doc_DocumentIntf, email_DocumentIntf, doc_ContragentDataIntf';
    
    
    /**
     * Абревиатура
     */
    var $abbr = 'Inv';
    
    
    /**
     * Заглавие
     */
    var $title = 'Фактури за продажби';
    
    
    /**
     * @todo Чака за документация...
     */
    var $singleTitle = 'Фактура за продажба';
    
    
    /**
     * Плъгини за зареждане
     */
    var $loadList = 'plg_RowTools, sales_Wrapper, plg_Sorting, doc_DocumentPlg, plg_ExportCsv,
					doc_EmailCreatePlg, doc_ActivatePlg, bgerp_plg_Blank, plg_Printing,
                    doc_SequencerPlg';
    
    
    /**
     * Дали може да бъде само в началото на нишка
     */
    var $onlyFirstInThread = TRUE;
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    var $listFields = 'number, vatDate, account ';
    
    
     
    /**
     * Детайла, на модела
     */
    var $details = 'sales_InvoiceDetails' ;
    
    
    /**
     * Кой има право да чете?
     */
    var $canRead = 'admin, sales';
    
    
    /**
     * Кой има право да променя?
     */
    var $canEdit = 'admin, sales';
    
    
    /**
     * Кой има право да добавя?
     */
    var $canAdd = 'admin, sales';
    
    
    /**
     * Кой може да го изтрие?
     */
    var $canDelete = 'admin, sales';
    
    /**
     * Нов темплейт за показване
     */
    var $singleLayoutFile = 'sales/tpl/SingleLayoutInvoice.shtml';
    
    /**
     * Поле за търсене
     */
    var $searchFields = 'number, date, contragentName';
    
    /**
     * Име на полето съдържащо номер на фактурата
     * 
     * @var int
     * @see doc_SequencerPlg
     */
    var $sequencerField = 'number';
    
    
    /**
     * Описание на модела
     */
    function description()
    {
        // Дата на фактурата
        $this->FLD('date', 'date(format=d.m.Y)', 'caption=Дата,  notNull, mandatory');
        
        // Номер на фактурата
        $this->FLD('number', 'int', 'caption=Номер, export=Csv');
        
//         $this->FLD('contragentId', 'int', 'notNull, input=hidden');
//         $this->FLD('contragentClassId', 'key(mvc=core_Classes)', 'notNull, input=hidden');
        
        $this->FLD('contragentName', 'varchar(255)', 'caption=Контрагент->Име, mandatory');
        $this->FLD('contragentCountryId', 'key(mvc=drdata_Countries,select=commonName)', 'caption=Контрагент->Държава,mandatory');
        $this->FLD('contragentAddress', 'text', 'caption=Контрагент->Адрес, mandatory',
            array(
                'attr' => array(
                    'rows' => 4,
                    'style' => 'width: 400px;',
                )
            )
        );

        // ДДС номер на контрагента
        $this->FLD('contragentVatNo', 'varchar(255)', 'caption=Контрагент->ДДС №, mandatory');
        
        // TODO да се мине през функцията за канонизиране от drdata_Vats 
        $this->FLD('vatCanonized', 'varchar(255)', 'caption=Vat Canonized, input=none');
        $this->FLD('dealPlace', 'varchar(255)', 'caption=Място на сделката, mandatory');
        $this->FLD('dealValue', 'double(decimals=2)', 'caption=Стойност, input=none');
        $this->FLD('vatRate', 'percent', 'caption=ДДС');
        $this->FLD('vatReason', 'varchar(255)', 'caption=Данъчно основание'); // TODO plg_Recently
        
        $this->FLD('creatorName', 'varchar(255)', 'caption=Съставил, input=none');
        
        // Дата на данъчното събитие. Ако не се въведе е датата на фактурата.
        $this->FLD('vatDate', 'date', 'caption=Дата на ДС');
        $this->FLD('currencyId', 'key(mvc=currency_Currencies, select=code, allowEmpty)', 'caption=Валута');
        $this->FLD('paymentMethodId', 'key(mvc=bank_PaymentMethods, select=name)', 'caption=Начин на плащане');
        $this->FLD('deliveryId', 'key(mvc=trans_DeliveryTerms, select=name, allowEmpty)', 'caption=Доставка');

        // Наша банкова сметка (при начин на плащане по банков път)
        $this->FLD('accountId', 'key(mvc=bank_Accounts, select=iban)', 'caption=Банкова сметка, export=Csv', 
            array(
                'attr' => array(
                    'style' => 'width: 400px',
                )
            )
        );
        
        /* $this->FLD('factoringAccount', 'varchar(255)', 'caption=Сметка за фактуриране'); */
        
        $this->FLD('additionalInfo', 'text', 'caption=Допълнителна информация');
        
        $this->FLD('state', 
            'enum(draft=Чернова, active=Контиран, rejected=Сторнирана)', 
            'caption=Статус, input=none'
        );
        
        // $this->FLD("type", "enum(invoice=Чернова, credit_note=Кредитно известие, debit_note=Дебитно известие)" );
        $this->FLD('type', 
            'enum(invoice=Фактура, credit_note=Кредитно известие, debit_note=Дебитно известие)', 
            'caption=Вид, input=none'
        );
        
        // $this->FLD("saleId", "key(mvc=Sales)");
        /* ? */// $this->FLD('saleId', 'key(mvc=sales_Sales,select=title)', 'caption=Продажба');
        // $this->FLD("paid", "int");
        
        /* $this->FLD('paid', 'int', 'caption=Платено'); */
        // $this->FLD("paidAmount", "number");
        /* $this->FLD('paidAmount', 'double(decimals=2)', 'caption=Сума'); */
        
        $this->setDbUnique('number');
    }
    
    
    public function on_AfterPrepareEditForm($mvc, $data)
    {
        /* @var $form core_Form */
        $form = $data->form;
        
        if (!$form->rec->id) {
            /*
             * При създаване на нова ф-ра зареждаме полетата на формата с разумни стойности по 
             * подразбиране.
             */
            $mvc::setFormDefaults($form);
        }
        
        $mvc::populateContragentData($form);
    }
    
    
    /**
     * Зарежда разумни начални стойности на полетата на форма за фактура.
     * 
     * @param core_Form $form
     */
    public static function setFormDefaults(core_Form $form)
    {
        // Днешна дата в полето `date`
        if (empty($form->rec->date)) {
            $form->rec->date = dt::now();
        }
        
        // ДДС % по-подразбиране - от периода към датата на ф-рата
        $periodRec = acc_Periods::fetchByDate($form->rec->date);
        if ($periodRec) {
            $form->rec->vatRate = $periodRec->params->vatPercent;
        }

        // Данни за контрагент
        static::populateContragentData($form);
    }
    
    
    /**
     * Изчислява данните на контрагента и ги зарежда във форма за създаване на нова ф-ра
     * 
     * По дефиниция, данните за контрагента се вземат от:
     * 
     *  * най-новата активна ф-ра в папката, в която се създава новата
     *  * ако няма такава - от корицата на тази папка
     * 
     * @param core_Form $form форма, в чиито полета да се заредят данните за контрагента
     */
    protected static function populateContragentData(core_Form $form)
    {
        $rec = $form->rec;
        
        if ($rec->id) {
            // Редактираме запис - не зареждаме нищо
            return;
        }
        
        // Задължително условие е папката, в която се създава новата ф-ра да е известна
        expect($folderId = $rec->folderId);
        
        // Извличаме данните на контрагент по подразбиране
        $contragentData = static::getDefaultContragentData($folderId);
        
        /*
         * Разглеждаме четири случая според данните в $contragentData
         * 
         *  1. Има данни за фирма и данни за лице
         *  2. Има само данни за фирма
         *  3. Има само данни за лице
         *  4. Нито едно от горните не е вярно
         */
        
        if (empty($contragentData->company) && empty($contragentData->name)) {
            // Случай 4: нито фирма, нито лице
            // TODO доколко допустимо е да се стигне до тук?
            expect(FALSE, 'Проблем с данните за контрагент по подразбиране');
            return;
        }
        
        $rec->contragentCountryId = $contragentData->countryId;
        
        if (!empty($contragentData->company)) {
            // Случай 1 или 2: има данни за фирма
            $rec->contragentName    = $contragentData->company;
            $rec->contragentAddress = trim(
                sprintf("%s %s\n%s", 
                    $contragentData->place,
                    $contragentData->pCode,
                    $contragentData->address
                )
            );
            $rec->contragentVatNo = $contragentData->vatNo;
            
            if (!empty($contragentData->name)) {
                // Случай 1: данни за фирма + данни за лице
                
                // TODO за сега не правим нищо допълнително
            }
        } elseif (!empty($contragentData->name)) {
            // Случай 3: само данни за физическо лице
            $rec->contragentName    = $contragentData->name;
            $rec->contragentAddress = $contragentData->pAddress;
        }
        
        if (!empty($rec->contragentCountryId)) {
            $currencyCode    = drdata_Countries::fetchField($rec->contragentCountryId, 'currencyCode');
            $rec->currencyId = currency_Currencies::fetchField("#code = '{$currencyCode}'", 'id');
            
            if ($rec->currencyId) {
                // Задаване на избор за банкова сметка.
                $ownBankAccounts = bank_Accounts::makeArray4Select('iban',
                    "#contragentCls = " . crm_Companies::getClassId() . " AND " .
                    "#contragentId  = " . BGERP_OWN_COMPANY_ID
                );
                
                $form->getField('accountId')->type->options = $ownBankAccounts;
            }
        }
    }


    /**
     * Данни за контрагент подразбиране при създаване на нова фактура.
     *
     * По дефиниция, данните за контрагента се вземат от:
     *
     *  * най-новата активна (т.е. контирана) ф-ра в папката, в която се създава новата
     *  * ако няма такава - от корицата на тази папка; класът на тази корица задължително трябва
     *                      да поддържа интерфейса doc_ContragentDataIntf
     *
     * @param int $folderId key(mvc=doc_Folders)
     * @return stdClass @see doc_ContragentDataIntf::getContragentData()
     */
    protected static function getDefaultContragentData($folderId)
    {
        if ($lastInvoiceRec = static::getLastActiveInvoice($folderId)) {
            $sourceClass    = __CLASS__;
            $sourceObjectId = $lastInvoiceRec->id;
        } else {
            $sourceClass    = doc_Folders::fetchCoverClassName($folderId);
            $sourceObjectId = doc_Folders::fetchCoverId($folderId);
        }
    
        if (!cls::haveInterface('doc_ContragentDataIntf', $sourceClass)) {
            // Намерения клас-източник на данни за контрагент не поддържа doc_ContragentDataIntf
            return;
        }
    
        $contragentData = $sourceClass::getContragentData($sourceObjectId);
    
        return $contragentData;
    }
    
    
    /**
     * Данните на най-новата активна (т.е. контирана) ф-ра в зададена папка
     *
     * @param int $folderId key(mvc=doc_Folders)
     * @return stdClass обект-данни на модела sales_Invoices; NULL ако няма такава ф-ра
     */
    protected static function getLastActiveInvoice($folderId)
    {
        /* @var $query core_Query */
        $query = static::getQuery();
        $query->where("#folderId = {$folderId}");
        $query->where("#state <> 'rejected'");
        $query->orderBy('createdOn', 'DESC');
        $query->limit(1);
    
        $invoiceRec = $query->fetch();
    
        return !empty($invoiceRec) ? $invoiceRec : NULL;
    }
    
    
    /**
     * Преди извличане на записите филтър по number
     *
     * @param core_Mvc $mvc
     * @param StdClass $res
     * @param StdClass $data
     */
    static function on_BeforePrepareListRecs($mvc, &$res, $data)
    {
        $data->query->orderBy('#number', 'DESC');
    }


    /**
     * Данните на контрагент, записани в съществуваща фактура.
     * 
     * Интерфейсен метод на @see doc_ContragentDataIntf.
     * 
     * @param int $id key(mvc=sales_Invoices)
     * @return stdClass @see doc_ContragentDataIntf::getContragentData()
     *  
     */
    public static function getContragentData($id)
    {
        $rec = sales_Invoices::fetch($id);
        
        $contrData = new stdClass();
        $contrData->company   = $rec->contragentName;
        $contrData->countryId = $rec->contragentCountryId;
        $contrData->country   = static::getVerbal($rec, 'contragentCountryId');
        $contrData->vatNo     = $rec->contragentVatNo;
        $contrData->address   = $rec->contragentAddress;
        
        return $contrData;
    }
    
    
    /**
     * Интерфейсен метод на doc_ContragentDataIntf
     * Връща тялото наимей по подразбиране
     */
    static function getDefaultEmailBody($id)
    {
        $handle = sales_Invoices::getHandle($id);
        
        //Създаваме шаблона
        $tpl = new ET(tr("Моля запознайте се с приложената фактура:") . "\n[#handle#]");
        
        //Заместваме датата в шаблона
        $tpl->append($handle, 'handle');
        
        return $tpl->getContent();
    }


    /*
     * Реализация на интерфейса doc_DocumentIntf
     */
    
    
    /**
     * Проверка дали нов документ може да бъде добавен в
     * посочената папка като начало на нишка
     *
     * @param $folderId int ид на папката
     * @param $firstClass string класът на корицата на папката
     */
    public static function canAddToFolder($folderId, $folderClass)
    {
        if (empty($folderClass)) {
            $folderClass = doc_Folders::fetchCoverClassName($folderId);
        }
    
        return $folderClass == 'crm_Companies' || $folderClass == 'crm_Persons';
    }
    
    
    /**
     * @todo Чака за документация...
     */
    function getDocumentRow($id)
    {
        $rec = $this->fetch($id);
        
		$row = new stdClass();

        $row->title = $this->getHandle($rec->id);   //TODO може да се премени
        //        $row->title = $this->getVerbal($rec, 'contragentId');
        
        $row->author = $this->getVerbal($rec, 'createdBy');
        
        $row->authorId = $rec->createdBy;
        
        $row->state = $rec->state;
        
        return $row;
    }
}
