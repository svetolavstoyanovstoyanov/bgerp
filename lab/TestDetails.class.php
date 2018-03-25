<?php

/**
 * Мениджира детайлите на тестовете (Details)
 *
 *
 * @category  bgerp
 * @package   lab
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class lab_TestDetails extends core_Detail {
	
	/**
	 * Заглавие
	 */
	var $title = "Детайли на тест";
	
	/**
	 * Плъгини за зареждане
	 */
	var $loadList = 'plg_Created, plg_RowTools2, plg_RowNumbering,
                          plg_Printing, lab_Wrapper, plg_Sorting, 
                          Tests=lab_Tests, Params=lab_Parameters, Methods=lab_Methods,plg_PrevAndNext, plg_SaveAndNew';
	
	/**
	 * Име на поле от модела, външен ключ към мастър записа
	 */
	var $masterKey = 'testId';
	
	/**
	 * Полета, които ще се показват в листов изглед
	 */
	var $listFields = 'methodId,paramName,value';
	
	/**
	 * Кой има право да добавя?
	 *
	 * @var string|array
	 */
	public $canAdd = 'ceo,lab,masterLab';
	
	/**
	 * Кой има право да променя?
	 *
	 * @var string|array
	 */
	public $canEdit = 'ceo,lab,masterLab';
	
	/**
	 * Кой може да го прави документа чакащ/чернова?
	 */
	public $canPending = 'ceo,lab,masterLab';
	
	/**
	 * Активния таб в случай, че wrapper-а е таб контрол.
	 */
	var $tabName = "lab_Tests";
	
	/**
	 * Роли, които могат да записват
	 */
	var $canWrite = 'lab,ceo,masterLab';
	
	/**
	 * Преди подготовката на полетата за листовия изглед
	 */
	public static function on_AfterPrepareListFields($mvc, &$res, &$data) {
		if (Request::get ( 'Rejected', 'int' )) {
			$data->listFields ['state'] = 'Състояние';
		}
	}
	
	/**
	 * Описание на модела
	 */
	function description() {
		$this->FLD ( 'testId', 'key(mvc=lab_Tests, select=title)', 'caption=Тест, input=hidden, silent,mandatory,smartCenter' );
		$this->FLD ( 'paramName', 'key(mvc=lab_Parameters, select=name)', 'caption=Параметър, notSorting,smartCenter,silent' );
		$this->FLD ( 'methodId', 'key(mvc=lab_Methods, select=name)', 'caption=Метод, notSorting,mandatory,smartCenter' );
		$this->FLD ( 'value', 'varchar(64)', 'caption=Стойност, notSorting, input=none,smartCenter' );
		$this->FLD ( 'refValue', 'varchar(64)', 'caption=Реф.Стойност, notSorting, input=none,smartCenter' );
		$this->FLD ( 'error', 'percent(decimals=2)', 'caption=Отклонение, notSorting,input=none,smartCenter' );
		$this->FLD ( 'comment', 'varchar', 'caption=Коментари, notSorting,after=results, column=none,class=" w50, rows= 1"' );
		
		$this->FLD ( 'better', 'enum(up=по-големия,down=по-малкия)', 'caption=По-добрия е,unit= резултат,after=title' );
		
		$this->FLD ( 'results', 'table(columns=value,captions=Стойност,widths=8em)', "caption=Измервания||Additional,autohide,advanced,after=title,single=none" );
		
		$this->setDbUnique ( 'testId, methodId' );
	}
	
	/**
	 * Променя заглавието и добавя стойност по default в селекта за избор на тест
	 *
	 * @param core_Mvc $mvc        	
	 * @param stdClass $res        	
	 * @param stdClass $data        	
	 */
	static function on_AfterPrepareEditForm($mvc, &$res, $data) {
		$paramsIdSelectArr = array (
				$data->form->rec->paramName => lab_Parameters::getTitleById ( $data->form->rec->paramName ) 
		);
		// bp($paramsIdSelectArr);
		// $data->form->setOptions('paramName', $paramsIdSelectArr);
		
		// allMethodsArr
		$Methods = cls::get ( 'lab_Methods' );
		$queryAllMethods = $Methods->getQuery ();
		
		$allMethodsArr = array ();
		
		while ( $mRec = $queryAllMethods->fetch ( "1=1" ) ) {
			
			if ($mRec->paramId == $data->form->rec->paramName) {
				$allMethodsArr [$mRec->id] = $mRec->name;
			}
		}
		$data->allMethodsArr = $allMethodsArr;
		
		// $methodIdSelectArr
		foreach ( $allMethodsArr as $k => $v ) {
			if (! $mvc->fetchField ( "#testId = {$data->form->rec->testId} AND #methodId = {$k}", 'id' )) {
				$methodIdSelectArr [$k] = $v;
			}
		}
		
		// Ако сме в режим 'добави' избираме метод, който не е използван
		// за текущия тест. Ако сме в режим 'редактирай' полето за избор на метод е скрито.
		
		if ($data->form->rec->id) {
			// Prepare array for methodId
			$data->form->setField ( 'methodId', 'input=none' );
		} else {
			$data->form->setOptions ( 'methodId', $methodIdSelectArr );
		}
	}
	
	/**
	 * След подготовката на заглавието на формата
	 */
	public static function on_AfterPrepareEditTitle($mvc, &$res, &$data) {
		
		// Заглавие
		$testHandler = lab_Tests::getHandle ( $data->masterId ) . lab_Tests::fetchField ( $data->form->rec->testId, 'title' );
		
		if ($data->form->rec->id) {
			$data->form->title = "Редактиране за тест|* \"" . $testHandler . "\",";
			$data->form->title .= "|*<br/>|метод|* \"" . $data->allMethodsArr [$data->form->rec->methodId] . "\"";
		} else {
			$data->form->title = "Добавяне на метод за тест|* \"" . $testHandler . "\"";
		}
	}
	
	/**
	 * Обработка на Master детайлите
	 *
	 * @param core_Mvc $mvc        	
	 * @param stdClass $row        	
	 * @param stdClass $rec        	
	 */
	static function on_AfterRecToVerbal($mvc, $row, $rec) {
		
		// $row->value
	
		
		if (is_numeric ( $row->value )) {
			$row->value = "<div style='float: right'>" . number_format ( $row->value, 2, ',', ' ' ) . "</div>";
		} else {
			$row->value = cls::get ( 'type_Text' )->toVerbal ( $rec->results );
		}
		
		if (($rec->value=='---')){
			$row->value = '---';
		}
		
		// $row->parameterName bp($rec);
		$paramId = $mvc->Methods->fetchField ( "#id = '{$rec->methodId}'", 'paramId' );
		$paramRec = $mvc->Params->fetch ( $paramId );
		$row->paramName = $paramRec->name . ($paramRec->dimension ? ', ' : '') . $paramRec->dimension;
		
		
		$row->refValue = '---';
		$row->error = '---';
		
	}
	
	/**
	 * След преобразуване на записа в четим за хора вид.
	 */
	protected static function on_AfterPrepareListRows($mvc, &$res, $data) {
		$rows = &$res->rows;
		$recs = &$res->recs;
		
		$compTest = Mode::get ( 'testCompare_' . lab_Tests::getHandle ( $data->masterId ) );
		if ($compTest) {
			
			array (
					$data->listFields ['refValue'] = 'Реф.Стойност' 
			);
			array (
					$data->listFields ['error'] = 'Отклонение' 
			);
			
			$dQuery = lab_TestDetails::getQuery ();
			$dQuery->where ( "#testId = {$compTest}" );
			
			while ( $testsDet = $dQuery->fetch () ) {
				
				foreach ( $rows as $key => $row ) {
					
					if ($compTest) {
						
						if ($recs [$key]->methodId == $testsDet->methodId) {
							
							$row->refValue = "<div style='float: right'>" . number_format ( $testsDet->value, 2, ',', ' ' ) . "</div>";
							$recs [$key]->refValue = $testsDet->value;
							if ($recs [$key]->refValue){
							$deviation = core_Type::getByName ( 'percent' )->toVerbal ( ($recs [$key]->refValue - $recs [$key]->value) / $recs [$key]->refValue );
							}else{
								$deviation = '---';
							}
							$row->error = ht::styleIfNegative ( $deviation, $deviation );
						}
					}
				}
			}
		}
		
		// bp($recs,$rows);
	}
	
	/**
	 * Създаване $rec->value, $rec->error и запис на lastChangeOn в 'lab_Tests'
	 *
	 * @param core_Mvc $mvc        	
	 * @param int $id        	
	 * @param stdClass $rec        	
	 */
	static function on_BeforeSave($mvc, &$id, $rec) {
		
		// Подготовка на масива за резултатите ($rec->results)
		$resultsArr = json_decode ( $rec->results )->value;
		
		
		
		// trim array elements
		if (is_array ( $resultsArr )) {
			foreach ( $resultsArr as $k => $v ) {
				$resultsArr [$k] = cls::get ( 'type_Double' )->fromVerbal ( $v );
			}
		}
		
		$methodsRec = $mvc->Methods->fetch ( $rec->methodId );
		$parametersRec = $mvc->Params->fetch ( $methodsRec->paramId );
		
		// BEGIN Обработки в зависимост от типа на параметъра
		if ($parametersRec->type == 'number') {
			// намираме средното аритметично
			$sum = 0;
			$totalResults = 0;
			
			$resCnt = count ( $resultsArr );
			
			for($i = 0; $i < $resCnt; $i ++) {
				if (trim ( $resultsArr [$i] )) {
					$sum += trim ( $resultsArr [$i] );
					$totalResults ++;
				}
			}
			
			$rec->value = 0;
			if (! empty ( $totalResults )) {
				$rec->value = $sum / $totalResults;
			}else{
				$rec->value = '---';
			}
			
			if ($resCnt > 1) {
				// Намираме грешката
				$dlt = 0;
				
				for($i = 0; $i < $resCnt; $i ++) {
					$dlt += ($resultsArr [$i] - $rec->value) * ($resultsArr [$i] - $rec->value);
				}
				
				// $rec->error = sqrt($dlt) / sqrt((count($resultsArr) * (count($resultsArr)-1))) / $rec->value;
				$rec->error = 'ok';
			} else {
				$rec->error = NULL;
			}
		} elseif ($parametersRec->type == 'bool') {
			$rec->value = $resultsArr [0];
			$rec->error = NULL;
		} elseif ($parametersRec->type == 'text') {
			$rec->value = $resultsArr [0];
			$rec->error = NULL;
		}
		
		// bp($rec);
		
		// END Обработки в зависимост от типа на параметъра
		
		// Запис в 'lab_Tests'
		if ($rec->testId) {
			$ltRec = new stdClass ();
			$ltRec->lastChangedOn = DT::verbal2mysql ();
			$ltRec->id = $rec->testId;
			$mvc->Tests->save ( $ltRec, 'lastChangedOn' );
		}
	}
	
	/**
	 * Извиква се след подготовката на toolbar-а за табличния изглед
	 */
	static function on_AfterPrepareListToolbar($mvc, $data, $rec) {
		$data->toolbar->removeBtn ( 'btnPrint' );
		// bp($data->toolbar);
		$parameters = array ();
		
		$parameters = keylist::toArray ( lab_Tests::fetch ( $data->masterId )->parameters );
		
		$url = array (
				$mvc,
				'add',
				$data->masterId 
		);
		$url = '';
		
		$paramName = lab_Parameters::getTitleById ( 13 );
		
		// bp($parameters);
		// $data->toolbar->addSelectBtn(array('' => '', $url => $paramName,'6'=>'6'));
		$data->toolbar->addSelectBtn ( array (
				'' => '',
				'6' => '6' 
		) );
		
		// Count all methods
		$allMethodsQuery = $mvc->Methods->getQuery ();
		
		$allMethodsQuery->where ( "1=1" );
		
		$methodsAllCounter = 0;
		
		while ( $mRec = $allMethodsQuery->fetch () ) {
			$methodsAllCounter ++;
		}
		
		// END Count all methods
		
		// Count methods for this test
		$methodsQuery = $mvc->getQuery ();
		
		$methodsQuery->where ( "#testId = {$rec->masterId}" );
		
		$methodsCounter = 0;
		
		while ( $rec = $methodsQuery->fetch () ) {
			$methodsCounter ++;
		}
		
		// END Count methods
	}
}

