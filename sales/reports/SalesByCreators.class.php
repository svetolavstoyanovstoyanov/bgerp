<?php


/**
 * Мениджър на отчети за продажби по създател
 *
 *
 * @category  bgerp
 * @package   sales
 *
 * @author    Angel Trifonov angel.trifonoff@gmail.com
 * @copyright 2006 - 2022 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 * @title     Продажби » Продажби по създател
 */
class sales_reports_SalesByCreators extends frame2_driver_TableData
{
    /**
     * Кой може да избира драйвъра
     */
    public $canSelectDriver = 'ceo,debug, hrMaster';


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
    protected $changeableFields = 'from,to,creator';


    /**
     * Добавя полетата на драйвера към Fieldset
     *
     * @param core_Fieldset $fieldset
     */
    public function addFields(core_Fieldset &$fieldset)
    {
        $fieldset->FLD('from', 'date', 'caption=От,after=compare,single=none,mandatory');
        $fieldset->FLD('to', 'date', 'caption=До,after=from,single=none,mandatory');
        $fieldset->FLD('creator', 'user(rolesForAll=ceo|repAllGlobal, rolesForTeams=ceo|manager|repAll|repAllGlobal)', 'caption=Създател,single=none,mandatory,after=to');
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

        if ($form->isSubmitted()) {

            // Проверка на периоди
            if (isset($form->rec->from, $form->rec->to) && ($form->rec->from > $form->rec->to)) {
                $form->setError('from,to', 'Началната дата на периода не може да бъде по-голяма от крайната.');
            }

        }
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
        if (is_null($rec->crmGroup) && is_null($rec->contragent)) {
            $this->groupByField = '';
        }

        $recs = array();
        $salesWithShipArr = $salesArr = array();

        $id = $rec->creator;

        $contragentsId = array();

        //Договори за продажба създадени през периода от избрания служител
        $query = sales_Sales::getQuery();

        $query->where("#state != 'rejected'");

        //$query->where("#valior >= '{$rec->from}' AND #valior <= '{$rec->to}'.' 23:59:59'");
        $query->where(array("#valior>= '[#1#]' AND #valior <= '[#2#]'",$rec->from. ' 00:00:00',$rec->to . ' 23:59:59'));

        if (isset($rec->creator)) {
            $query->where("#createdBy = $rec->creator");
        }

        while ($sRec = $query->fetch()) {

            if (!array_key_exists($id, $recs)) {
                $recs[$id] = (object)array(

                    'creator' => $rec->creator,
                    'salesAmount' => $sRec->amountDeal - $sRec->amountVat,
                    'salesCount' => 1,
                    'delta' => 0,
                    'detailsCount' => 0,
                    'detailsAmount' => 0,
                );
            } else {
                $obj = &$recs[$id];
                $obj->salesAmount += $sRec->amountDeal;
                $obj->salesCount++;
            }
        }


        //Делти за периода

        $primeQuery = sales_PrimeCostByDocument::getQuery();

        $primeQuery->where("#state != 'rejected'");

        $primeQuery->where("#valior >= '{$rec->from}' AND #valior <= '{$rec->to}'");

        while ($pRec = $primeQuery->fetch()) {

            //Продажбата в която се формират делтите
            $firstDoc = doc_Threads::getFirstDocument($pRec->threadId);

            if ($firstDoc->className != 'sales_Sales') continue;

            $fDocRec = sales_Sales::fetch($firstDoc->that);

            if ($fDocRec->createdBy != $rec->creator || !$pRec->delta) continue;

            if (!empty($recs)) {
                $recs[$id]->delta += $pRec->delta;
                $recs[$id]->detailsAmount += $pRec->sellCost * $pRec->quantity;
                $recs[$id]->detailsCount++;
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

            $fld->FLD('creator', 'varchar', 'caption=Създател');
            $fld->FLD('salesAmount', 'double(decimals=2)', 'smartCenter,caption=Продажби->Стойност->без ДДС');
            $fld->FLD('salesCount', 'int', "smartCenter,caption=Продажби->Брой");
            $fld->FLD('detailsAmount', 'double(decimals=2)', 'smartCenter,caption=Редове-> Стойност-> без ДДС');
            $fld->FLD('detailsCount', 'double(int)', 'smartCenter,caption=Редове->Брой');
            $fld->FLD('delta', 'double(decimals=2)', 'smartCenter,caption=Редове->Делта');

        } else {

            $fld->FLD('creator', 'varchar', 'caption=Създател');
            $fld->FLD('salesAmount', 'double(decimals=2)', 'smartCenter,caption=Продажби->Стойност');
            $fld->FLD('salesCount', 'int', "smartCenter,caption=Продажби->Брой");
            $fld->FLD('detailsAmount', 'double(decimals=2)', 'smartCenter,caption=Редове->Стойност');
            $fld->FLD('detailsCount', 'double(decimals=2)', 'smartCenter,caption=Редове->Брой');
            $fld->FLD('delta', 'double(decimals=2)', 'smartCenter,caption=Редове->Делта');
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

        $personId = (crm_Profiles::fetch("#userId = $dRec->creator")->personId);
        $row->creator = crm_Persons::getHyperlink($personId);

        $row->salesCount = '<b>' . core_Type::getByName('int')->toVerbal($dRec->salesCount) . '</b>';
        $row->salesCount = ht::styleNumber($row->salesCount, $dRec->salesCount);

        $row->salesAmount = '<b>' . core_Type::getByName('double(decimals=2)')->toVerbal($dRec->salesAmount) . '</b>';
        $row->salesAmount = ht::styleNumber($row->salesAmount, $dRec->salesAmount);

        $row->detailsAmount = '<b>' . core_Type::getByName('double(decimals=2)')->toVerbal($dRec->detailsAmount) . '</b>';
        $row->detailsAmount = ht::styleNumber($row->detailsAmount, $dRec->detailsAmount);

        $row->detailsCount = '<b>' . core_Type::getByName('int')->toVerbal($dRec->detailsCount) . '</b>';
        $row->detailsCount = ht::styleNumber($row->detailsCount, $dRec->detailsCount);

        $row->delta = '<b>' . core_Type::getByName('double(decimals=2)')->toVerbal($dRec->delta) . '</b>';
        $row->delta = ht::styleNumber($row->delta, $dRec->delta);

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
                                        <!--ET_BEGIN from--><div>|От|*: [#from#]</div><!--ET_END from-->
                                        <!--ET_BEGIN to--><div>|До|*: [#to#]</div><!--ET_END to-->
                                    </div>
                                </fieldset><!--ET_END BLOCK-->"));


        if (isset($data->rec->from)) {
            $fieldTpl->append('<b>' . $data->rec->from . '</b>', 'from');
        }

        if (isset($data->rec->to)) {
            $fieldTpl->append('<b>' . $data->rec->to . '</b>', 'to');
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
        $personId = (crm_Profiles::fetch("#userId = $dRec->creator")->personId);
        $res->creator = crm_Persons::getHyperlink($personId);

    }
}
