<?php


/**
 * Базов драйвер за партиден клас 'дата на годност'
 *
 *
 * @category  bgerp
 * @package   batch
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @title Дата на годност
 */
class batch_definitions_ExpirationDate extends batch_definitions_Proto
{
	
	
	/**
	 * Позволени формати
	 */
	private $formatSuggestions = 'm/d/y,m.d.y,d.m.Y,m/d/Y,d/m/Y,Ymd,Ydm,Y-m-d,dmY,ymd,ydm';
	
	
	/**
	 * Добавя полетата на драйвера към Fieldset
	 *
	 * @param core_Fieldset $fieldset
	 */
	public function addFields(core_Fieldset &$fieldset)
	{
		$fieldset->FLD('format', 'varchar(20)', 'caption=Формат,mandatory');
		$fieldset->FLD('time', 'time(suggestions=1 ден|2 дена|1 седмица|1 месец)', 'caption=Колко дни след текущата дата');
		
		$fieldset->setOptions('format', array('' => '') + arr::make($this->formatSuggestions, TRUE));
	}
	
	
	/**
	 * Връща автоматичния партиден номер според класа
	 *
	 * @param mixed $documentClass - класа за който ще връщаме партидата
	 * @param int $id - ид на документа за който ще връщаме партидата
	 * @return mixed $value - автоматичния партиден номер, ако може да се генерира
	 */
	public function getAutoValue($documentClass, $id)
	{
		$date = dt::today();
		if(isset($this->rec->time)){
			$date = dt::addSecs($this->rec->time, $date);
			$date = dt::verbal2mysql($date, FALSE);
		}
		$date = dt::mysql2verbal($date, $this->rec->format);
		
		return $date;
	}
	
	
	/**
	 * Проверява дали стойността е невалидна
	 *
	 * @param string $value - стойноста, която ще проверяваме
	 * @param quantity $quantity - количеството
	 * @param string &$msg - текста на грешката ако има
	 * @return boolean - валиден ли е кода на партидата според дефиницията или не
	 */
	public function isValid($value, $quantity, &$msg)
	{
		// Карта
		$map = array();
		$map['m'] = "(?'month'[0-9]{2})";
		$map['d'] = "(?'day'[0-9]{2})";
		$map['y'] = "(?'yearShort'[0-9]{2})";
		$map['Y'] = "(?'year'[0-9]{4})";
		
		// Генерираме регулярен израз спрямо картата
		$expr = $this->rec->format;
		$expr = preg_quote($expr, '/');
		$expr = strtr($expr, $map);
		
		// Проверяваме дали датата отговаря на формата
		if(!preg_match("/^{$expr}$/", $value, $matches)){
			$msg = "|Партидата трябва да е във формат за дата|* <b>{$this->rec->format}</b>";
			return FALSE;
		}
		
		if(isset($matches['yearShort'])){
			$matches['year'] = "20{$matches['yearShort']}";
		}
		
		// Проверяваме дали датата е възможна
		if(!checkdate($matches['month'], $matches['day'], $matches['year'])){
			$msg = "|Партидата трябва да е във формат за дата|* <b>{$this->rec->format}</b>";
			return FALSE;
		}
		
		// Връщаме истина, ако не са се получили грешки
		return TRUE;
	}
}