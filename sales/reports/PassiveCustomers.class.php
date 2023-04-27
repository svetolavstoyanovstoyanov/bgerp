<?php


/**
 * Мениджър на отчети за пасивни клиенти
 *
 *
 * @category  bgerp
 * @package   sales
 *
 * @author    Angel Trifonov angel.trifonoff@gmail.com
 * @copyright 2006 - 2023 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 * @title     Продажби » Пасивни клиенти
 */
class sales_reports_PassiveCustomers extends frame2_driver_TableData
{
    /**
     * Кой може да избира драйвъра
     */
    public $canSelectDriver = 'ceo, admin, debug';


    /**
     * Полета за хеширане на таговете
     *
     * @see uiext_Labels
     *
     * @var string
     */
    protected $hashField;


    /**
     * Коя комбинация от полета от $data->recs да се следи, ако има промяна в последната версия
     *
     * @var string
     */
    protected $newFieldsToCheck;


    /**
     * По-кое поле да се групират листовите данни
     */
    protected $groupByField;


    /**
     * Кои полета може да се променят от потребител споделен към справката, но нямащ права за нея
     */
    protected $changeableFields;


    /**
     * Добавя полетата на драйвера към Fieldset
     *
     * @param core_Fieldset $fieldset
     */
    public function addFields(core_Fieldset &$fieldset)
    {
        $fieldset->FLD('periodPassive', 'time(suggestions=|1 седмица|1 месец|3 месеца|6 месеца)', 'caption=Период->Пасивен, after=title,mandatory,single=none,removeAndRefreshForm');
        $fieldset->FLD('periodActive', 'time(suggestions=1 месец|3 месеца|6 месеца|1 година|2 години)', 'caption=Период->Активен, after=periodPassive,mandatory,single=none,removeAndRefreshForm');

        $fieldset->FLD('dealers', 'users(rolesForAll=ceo|repAllGlobal, rolesForTeams=ceo|manager|repAll|repAllGlobal)', 'caption=Търговци->Търговци,placeholder=Всички,single=none,mandatory,after=periodActive');
        $fieldset->FLD('crmGroup', 'keylist(mvc=crm_Groups,select=name)', 'caption=Групи->Група контрагенти,placeholder=Всички,after=dealers,single=none');
        $fieldset->FLD('minShipment', 'double', 'caption=Мин. наличност, after=crmGroup,single=none, unit= лв.');

    }


    /**
     * След рендиране на единичния изглед
     *
     * @param cat_ProductDriver $Driver
     * @param embed_Manager $Embedder
     * @param core_Form $form
     * @param stdClass $data
     */
    protected static function on_AfterInputEditForm(frame2_driver_Proto $Driver, embed_Manager $Embedder, &$form)
    {

    }


    /**
     * Преди показване на форма за добавяне/промяна.
     *
     * @param frame2_driver_Proto $Driver
     * @param embed_Manager $Embedder
     * @param stdClass $data
     */
    protected static function on_AfterPrepareEditForm(frame2_driver_Proto $Driver, embed_Manager $Embedder, &$data)
    {
        $suggestions = array();
        $form = $data->form;
        $rec = $form->rec;

        $form->setDefault('periodPassive', '6 месеца');

        $form->setDefault('periodActive', '2 години');

        $form->setDefault('minShipment', 1000);


    }


    /**
     * Кои записи ще се показват в таблицата
     *
     * @param stdClass $rec
     * @param stdClass $data
     *
     * @return array
     */
    protected function prepareRecs($rec, &$data = null)
    {

        $recs = $shipmentActivContragents = $shipmentPassActivContragents = array();

        $passivePeriodStart = dt::addSecs(-$rec->periodPassive, dt::today(), false);
        $activePeriodStart = dt::addSecs(-$rec->periodActive, $passivePeriodStart, false);

        //Определяме контрагентите  с експедиции в периода на активност и периода на пасивност
        $shQuery = store_ShipmentOrders::getQuery();
        $shQuery->in('state', array('rejected', 'draft'), true);
        $shQuery->where("#valior >= '$activePeriodStart'");

        while ($shRec = $shQuery->fetch()) {

            $id = $shRec->folderId;

            //филтър по група на контрагента на експедицията
            if ($rec->crmGroup) {
                $checkContragentsGroups = keylist::toArray($rec->crmGroup);
                $contragentsGroups = keylist::toArray(doc_Folders::getContragentData($shRec->folderId)->groupList);

                if (countR(array_intersect($checkContragentsGroups, $contragentsGroups)) == 0) continue;

            }

            //отделяме експедициите с вальор преди началото на пасивния период и записваме
            // $shipmentActivContragents масив активни клиенти(които имат експедиции в активния период)
            if ($shRec->valior < $passivePeriodStart) {
                if (!array_key_exists($id, $shipmentActivContragents)) {
                    $shipmentActivContragents[$id] = (object)array(
                        'folderId' => $shRec->folderId,
                        'amountDelivered' => $shRec->amountDelivered,
                        'numberOfSales' => 1,
                    );
                } else {
                    $obj = &$shipmentActivContragents[$id];
                    $obj->amountDelivered += $shRec->amountDelivered;
                    $obj->numberOfSales++;
                }
            }

            //отделяме експедициите с вальор след началото на пасивния период и записваме
            // $shipmentPassActivContragents масив клиенти, които имат експедиции в пасивния период
            if ($shRec->valior >= $passivePeriodStart && $shRec->amountDelivered > 0) {

                $shipmentPassActivContragents[$shRec->folderId] = $shRec->folderId;

            }
        }

        //Добавяне на експедициите от БЪРЗИ ПРОДАЖБИ
        $salQuery = sales_Sales::getQuery();
        $salQuery->in('state', array('rejected', 'draft'), true);
        $salQuery->like('contoActions', 'ship');
        $salQuery->where("#valior >= '$activePeriodStart'");

        while ($salRec = $salQuery->fetch()) {

            $id = $salRec->folderId;

            //филтър по група на контрагента на бързата продажба
            if ($rec->crmGroup) {

                $checkContragentsGroups = keylist::toArray($rec->crmGroup);
                $contragentsGroups = keylist::toArray(doc_Folders::getContragentData($salRec->folderId)->groupList);

                if (countR(array_intersect($checkContragentsGroups, $contragentsGroups)) == 0) continue;

            }

            //отделяме бързите продажби с вальор преди началото на пасивния период и записваме
            // $shipmentActivContragents масив активни клиенти(които имат бързи продажби в активния период)
            if ($salRec->valior < $passivePeriodStart) {
                if (!array_key_exists($id, $shipmentActivContragents)) {
                    $shipmentActivContragents[$id] = (object)array(
                        'folderId' => $salRec->folderId,
                        'amountDelivered' => $salRec->amountDelivered,
                        'numberOfSales' => 1,
                    );
                } else {
                    $obj = &$shipmentActivContragents[$id];
                    $obj->amountDelivered += $salRec->amountDelivered;
                    $obj->numberOfSales++;
                }
            }

            //отделяме бързите продажби с вальор след началото на пасивния период и записваме в
            // $shipmentPassActivContragents масив клиенти, които имат бързи продажби в пасивния период
            if ($shRec->valior >= $passivePeriodStart && $salRec->amountDelivered > 0) {

                $shipmentPassActivContragents[$salRec->folderId] = $salRec->folderId;

            }
        }


        //Ako избрания праг за стойност на експедициите през активния период не е нула
        //От  масива $shipmentActivContragents, изключваме онези с продажби под определения праг
        if ($rec->minShipment != 0 && (countR($shipmentActivContragents) > 0)) {

            foreach ($shipmentActivContragents as $val) {

                if ($val->amountDelivered < $rec->minShipment) {
                    unset($shipmentActivContragents[$val->folderId]);
                }
            }
        }

        //Определяне на контрагентите с нулеви предажби през пасивния период и
        //влизащи в масива на активните клиенти
        foreach ($shipmentActivContragents as $key => $val) {

            if (!in_array($key, $shipmentPassActivContragents)) {

                $recs[$key] = $val;
            }
        }

        return $recs;
    }


    /**
     * Връща фийлдсета на таблицата, която ще се рендира
     *
     * @param stdClass $rec
     *                         - записа
     * @param bool $export
     *                         - таблицата за експорт ли е
     *
     * @return core_FieldSet - полетата
     */
    protected function getTableFieldSet($rec, $export = false)
    {
        $fld = cls::get('core_FieldSet');


        if ($export === false) {
            $fld->FLD('contragentId', 'key(mvc=doc_Folders,select=name)', 'caption=Контрагент');
            $fld->FLD('activSalesNumber', 'int', 'caption=Активен продажби->Брой');
            $fld->FLD('activSalesAmount', 'double(decimals=2)', 'caption=Активен продажби->Стойност');
            $fld->FLD('passivMailsIn', 'varchar', 'caption=Пасивен Писма->Входящи');
            $fld->FLD('passivMailsOut', 'varchar', 'caption=Пасивен Писма->Изходяши');

        } else {

            $fld->FLD('contragentId', 'varchar', 'caption=Контрагент');

        }

        return $fld;
    }


    /**
     * Вербализиране на редовете, които ще се показват на текущата страница в отчета
     *
     * @param stdClass $rec
     *                       - записа
     * @param stdClass $dRec
     *                       - чистия запис
     *
     * @return stdClass $row - вербалния запис
     */
    protected function detailRecToVerbal($rec, &$dRec)
    {
        $Int = cls::get('type_Int');
        $Date = cls::get('type_Date');
        $Double = cls::get('type_Double');
        $Double->params['decimals'] = 2;

        $row = new stdClass();

        $row->contragentId = doc_Folders::getHyperlink($dRec->folderId);

        $row->activSalesNumber = $Int->toVerbal($dRec->numberOfSales);

        $row->activSalesAmount = $Double->toVerbal($dRec->amountDelivered);


        return $row;
    }


    /**
     * След рендиране на единичния изглед
     *
     * @param frame2_driver_Proto $Driver
     * @param embed_Manager $Embedder
     * @param core_ET $tpl
     * @param stdClass $data
     */
    protected static function on_AfterRecToVerbal(frame2_driver_Proto $Driver, embed_Manager $Embedder, $row, $rec, $fields = array())
    {

    }


    /**
     * След рендиране на единичния изглед
     *
     * @param cat_ProductDriver $Driver
     * @param embed_Manager $Embedder
     * @param core_ET $tpl
     * @param stdClass $data
     */
    protected static function on_AfterRenderSingle(frame2_driver_Proto $Driver, embed_Manager $Embedder, &$tpl, $data)
    {


        $fieldTpl = new core_ET(tr("|*<!--ET_BEGIN BLOCK-->[#BLOCK#]
								<fieldset class='detail-info'><legend class='groupTitle'><small><b>|Филтър|*</b></small></legend>
                                    <div class='small'>
                                        <!--ET_BEGIN from--><div>|Пасивен период|*: [#from#]</div><!--ET_END from-->
                                        <!--ET_BEGIN to--><div>|Активен период|*: [#to#]</div><!--ET_END to-->
                                        <!--ET_BEGIN to--><div>|Мин. продажби|*: [#to#]</div><!--ET_END to-->
                                    </div>
                                </fieldset><!--ET_END BLOCK-->"));


        if (isset($data->rec->from)) {
            $fieldTpl->append('<b>' . $data->row->from . '</b>', 'from');
        }

        if (isset($data->rec->to)) {
            $fieldTpl->append('<b>' . $data->row->to . '</b>', 'to');
        }


        $tpl->append($fieldTpl, 'DRIVER_FIELDS');
    }


    /**
     * След подготовка на реда за експорт
     *
     * @param frame2_driver_Proto $Driver
     * @param stdClass $res
     * @param stdClass $rec
     * @param stdClass $dRec
     */
    protected static function on_AfterGetExportRec(frame2_driver_Proto $Driver, &$res, $rec, $dRec, $ExportClass)
    {

        $res->contragentId = self::getContragent($dRec, false, $rec);


    }
}
