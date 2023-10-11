<?php


/**
 * Тип партидност за Номер + Параметър + Годен до
 *
 *
 * @category  bgerp
 * @package   batch
 *
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2023 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 * @title Номер + Параметър + Годен до
 */
class batch_definitions_StringParamDate extends batch_definitions_Varchar
{

    /**
     * За конвертиране на съществуващи MySQL таблици от предишни версии
     */
    public $oldClassName = 'batch_definitions_ManifactureString';


    /**
     * Позволени формати
     */
    protected $formatSuggestions = 'm/d/y,m.d.y,d.m.Y,m/d/Y,d/m/Y,Ymd,Ydm,Y-m-d,dmY,ymd,ydm,m.d.Y';


    /**
     * Добавя полетата на драйвера към Fieldset
     *
     * @param core_Fieldset $fieldset
     */
    public function addFields(core_Fieldset &$fieldset)
    {
        $fieldset->FLD('delimiter', 'enum(&#x20;=Интервал,.=Точка,&#44;=Запетая,&#47;=Наклонена,&#45;=Тире)', 'caption=Разделител');
        $fieldset->FLD('format', 'varchar(20)', 'caption=Формат,mandatory');
        $fieldset->setOptions('format', array('' => '') + arr::make($this->formatSuggestions, true));
        $fieldset->FLD('time', 'time(suggestions=1 ден|2 дена|1 седмица|1 месец)', 'caption=Срок по подразбиране,unit=след текущата дата');
        $fieldset->FLD('manParamId', 'key(mvc=cat_Params,select=nameExt)', 'caption=Параметър за производител,mandatory');

        $paramOptions = cat_Params::getOptionsByDriverClass('cond_type_Enum,cond_type_Varchar', 'typeExt');
        $fieldset->setOptions('manParamId', array('' => '') + $paramOptions);
    }


    /**
     * Проверява дали стойността е невалидна
     *
     * @return core_Type - инстанция на тип
     */
    public function getBatchClassType()
    {
        $Type = core_Type::getByName("batch_type_StringParamDate(productId={$this->rec->productId},format={$this->rec->format},defaultTime={$this->rec->time},manifactureParamId={$this->rec->manParamId},delimiter={$this->rec->delimiter})");

        return $Type;
    }


    /**
     * Проверява дали стойността е невалидна
     *
     * @param string $value    - стойноста, която ще проверяваме
     * @param float  $quantity - количеството
     * @param string &$msg     -текста на грешката ако има
     *
     * @return bool - валиден ли е кода на партидата според дефиницията или не
     */
    public function isValid($value, $quantity, &$msg)
    {
        // Ако артикула вече има партида за този артикул с тази стойност, се приема че е валидна
        if (batch_Items::fetchField(array("#productId = {$this->rec->productId} AND #batch = '[#1#]'", $value))) return true;

        $Type = $this->getBatchClassType();
        $Type->fromVerbal($value);

        if(!empty($Type->error)){
            $msg = $Type->error;
            return false;
        }

        return true;
    }


    /**
     * Кой може да избере драйвера
     */
    public function toVerbal($value)
    {
        $Type = $this->getBatchClassType();
        $verbal = $Type->toVerbal($value);
        $delimiter = html_entity_decode($this->rec->delimiter, ENT_COMPAT, 'UTF-8');
        list($string, $manifacture, $date) = explode($delimiter, $verbal);

        $expiryTime = cat_Products::getParams($this->rec->productId, 'expiryTime');
        $expiryTime = !empty($expiryTime) ? $expiryTime : $this->rec->time;
        $date = batch_definitions_ExpirationDate::displayExpiryDate($date, $this->rec->format, $expiryTime);

        $string = core_Type::getByName('varchar')->toVerbal($string);
        $value = implode($delimiter, array($string, $manifacture, $date));
        if(!Mode::is('text', 'plain') && $value != strip_tags($value)) {
            $value = "<span>{$value}</span>";
        }

        return $value;
    }


    /**
     * След рендиране на единичния изглед
     *
     * @param batch_BatchTypeIntf $Driver
     * @param embed_Manager     $Embedder
     * @param core_Form         $form
     */
    protected static function on_AfterInputEditForm($Driver, $Embedder, &$form)
    {
        $exampleDate = dt::mysql2verbal(null, $form->rec->format);
        $delimiter = html_entity_decode($form->rec->delimiter, ENT_COMPAT, 'UTF-8');

        if(strpos($exampleDate, $delimiter) !== false){
            $form->setError('format,delimiter', "Разделителят се съдържа във формата на датата");
        }
    }


    /**
     * Какви са свойствата на партидата
     *
     * @param string $value - номер на партидара
     * @return array - свойства на партидата
     *               масив с ключ ид на партидна дефиниция и стойност свойството
     */
    public function getFeatures($value)
    {
        list($string, $manufacturer, $date) = explode('|', $value);

        $varcharClassId = batch_definitions_Varchar::getClassId();
        $dateClassId = batch_definitions_ExpirationDate::getClassId();
        $date = dt::getMysqlFromMask($date, $this->rec->format);

        $res = array();
        $res[] = (object) array('name' => 'Номер', 'classId' => $varcharClassId, 'value' => $string);
        $res[] = (object) array('name' => 'Производител', 'classId' => $varcharClassId, 'value' => $manufacturer);
        $res[] = (object) array('name' => 'Срок на годност', 'classId' => $dateClassId, 'value' => $date);

        return $res;
    }


    /**
     * Нормализира стойноста на партидата в удобен за съхранение вид
     *
     * @param string $value
     * @return string $value
     */
    public function normalize($value)
    {
        $delimiter = html_entity_decode($this->rec->delimiter, ENT_COMPAT, 'UTF-8');

        return str_replace($delimiter, '|', $value);
    }


    /**
     * Връща масив с опции за лист филтъра на партидите
     *
     * @return array - масив с опции
     *               [ключ_на_филтъра] => [име_на_филтъра]
     */
    public function getListFilterOptions()
    {
        return array('expiration' => 'Срок на годност');
    }


    /**
     * Подрежда подадените партиди
     *
     * @param array         $batches - наличните партиди
     *                               ['batch_name'] => ['quantity']
     * @param datetime|NULL $date
     *                               return void
     */
    public function orderBatchesInStore(&$batches, $storeId, $date = null)
    {
        $dates = array_keys($batches);

        usort($dates, function ($a, $b) {
            list( , , $aDate) = explode('|', $a);
            list( , , $bDate) = explode('|', $b);
            $aTime = strtotime(dt::getMysqlFromMask($aDate, $this->rec->format));
            $bTime = strtotime(dt::getMysqlFromMask($bDate, $this->rec->format));

            return ($aTime < $bTime) ? -1 : 1;
        });

        $sorted = array();
        foreach ($dates as $date) {
            $sorted[$date] = $batches[$date];
        }

        $batches = $sorted;
    }
}