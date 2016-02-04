<?php



/**
 * Драйвер за задачи за производство
 *
 *
 * @category  bgerp
 * @package   planning
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2015 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @title Задача за производство
 */
class planning_drivers_ProductionTask extends tasks_BaseDriver
{
	
	
	/**
	 * Интерфейси които имплементира
	 */
	public $interfaces = 'planning_DriverIntf';
	
	
	/**
	 * Кой може да избира драйвъра
	 */
	public $canSelectDriver = 'planning,ceo';
	
	
	/**
	 * Какво да е дефолтното име на задача от драйвера
	 */
	protected $defaultTitle = 'Задача за производство';
	
	
	/**
	 * Кои детайли да се заредят динамично към мастъра
	 */
	protected $details = 'planning_drivers_ProductionTaskDetails,planning_drivers_ProductionTaskProducts';
	
	
	/**
     * Добавя полетата на драйвера към Fieldset
     *
     * @param core_Fieldset $fieldset
     */
    public function addFields(core_Fieldset &$fieldset)
    {
		$fieldset->FLD('totalQuantity', 'double(smartRound)', 'mandatory,caption=Общо к-во,silent');
		$fieldset->FLD('totalWeight', 'cat_type_Weight', 'caption=Общо тегло,input=none');
		$fieldset->FLD('fixedAssets', 'keylist(mvc=planning_AssetResources,select=code,makeLinks)', 'caption=Машини');
	}
	
	
	/**
     * Обновяване на данните на мастъра
     * 
     * @param stdClass $rec - запис на ембедъра
     * @param void
     */
	public function updateEmbedder(&$rec)
	{
		 // Колко е общото к-во досега
		 $dQuery = planning_drivers_ProductionTaskDetails::getQuery();
		 $dQuery->where("#taskId = {$rec->id}");
		 $dQuery->where("#state != 'rejected'");
		 $dQuery->XPR('sumQuantity', 'double', 'SUM(#quantity)');
		 $dQuery->XPR('sumWeight', 'double', 'SUM(#weight)');
		 $dQuery->show('sumQuantity,sumWeight');
		 
		 $res = $dQuery->fetch();
		 $sumQuantity = $res->sumQuantity;
		 
		 // Преизчисляваме общото тегло
		 $rec->totalWeight = $res->sumWeight;
		      
		 // Изчисляваме колко % от зададеното количество е направено
		 $rec->progress = round($sumQuantity / $rec->totalQuantity, 2);
	}


    /**
     * Добавя ключови думи за пълнотекстово търсене
     * 
     * @param tasks_BaseDriver $Driver
     * @param embed_Manager $Embedder
     * @param stdClass $res
     * @param stdClass $rec
     */
    public static function on_AfterGetSearchKeywords(tasks_BaseDriver $Driver, embed_Manager $Embedder, &$res, $rec)
    {
    	if(empty($rec->id)) return;
    	
    	$details = $Driver->getDetails();
    	
    	if(is_array($details)){
    		foreach ($details as $Detail){
    			$Detail = cls::get($Detail);
    			
    			$dQuery = $Detail->getQuery();
    			$dQuery->where("#taskId = {$rec->id}");
    			
    			$detailsKeywords = '';
    			while($dRec = $dQuery->fetch()){
    				
    				if($dRec->serial){
    					$detailsKeywords .= " " . plg_Search::normalizeText($Detail->getVerbal($dRec, 'serial'));
    				}
    					
    				if($dRec->fixedAsset){
    					$detailsKeywords .= " " . plg_Search::normalizeText($Detail->getVerbal($dRec, 'fixedAsset'));
    				}
    			}
    			
    			// Добавяме новите ключови думи към старите
    			$res = " " . $res . " " . $detailsKeywords;
    		}
    	}
    }
    
    
    /**
     * Връща полетата, които ще се показват в антетката
     * 
     * @param stdObject $rec
     * @param stdObject $row
     * 
     * @return array
     */
    public static function prepareFieldLetterHeaded($rec, $row)
    {
        $resArr = array();
        
        if ($row->timeStart) {
            $resArr['timeStart'] =  array('name' => tr('Начало'), 'val' =>"[#timeStart#]");
        }
        
        if ($row->timeDuration) {
            $resArr['timeDuration'] =  array('name' => tr('Продължителност'), 'val' =>"[#timeDuration#]");
        }
        
        if ($row->timeEnd) {
            $resArr['timeEnd'] =  array('name' => tr('Краен срок'), 'val' =>"[#timeEnd#] [#remainingTime#]");
        }
        
        if ($row->expectedTimeStart) {
            $resArr['expectedTimeStart'] =  array('name' => tr('Очаквано начало'), 'val' =>"[#expectedTimeStart#]");
        }
        
        if ($row->expectedTimeEnd) {
            $resArr['expectedTimeEnd'] =  array('name' => tr('Очакван край'), 'val' =>"[#expectedTimeEnd#]");
        }
        
        $resArr['totalQuantity'] =  array('name' => tr('Общо к-во'), 'val' =>"[#totalQuantity#]");
        
        if ($row->totalWeight) {
            $resArr['totalWeight'] =  array('name' => tr('Общо тегло'), 'val' =>"[#totalWeight#]");
        }
        
        if ($row->fixedAssets) {
            $resArr['fixedAssets'] =  array('name' => tr('Машини'), 'val' =>"[#fixedAssets#]");
        }
        
        $resArr['progressBar'] =  array('name' => tr('Прогрес'), 'val' =>"[#progressBar#] [#progress#]");
        
        if ($row->originId) {
            $resArr['originId'] =  array('name' => tr('Към задание'), 'val' =>"[#originId#]");
        }
        
        return $resArr;
    }
    
    
    /**
     * Преди клонирането на записа
     * 
     * @param tasks_BaseDriver $Driver
     * @param embed_Manager $Embedder
     * @param stdClass $rec
     * @param stdClass $nRec
     */
    public static function on_BeforeSaveCloneRec(tasks_BaseDriver $Driver, embed_Manager $Embedder, &$rec, &$nRec)
    {
    	unset($nRec->totalWeight);
    }
    
    
    /**
     * Преди проверка за права
     * 
     * @param tasks_BaseDriver $Driver
     * @param embed_Manager $Embedder
     * @param string $requiredRoles
     * @param string $action
     * @param stdClass $rec
     * @param int $userId
     */
    public static function on_AfterGetRequiredRoles(tasks_BaseDriver $Driver, embed_Manager $Embedder, &$requiredRoles, $action, $rec, $userId = NULL)
    {
    	if($action == 'reject' && isset($rec)){
    		
    		// Ако има прогрес, задачата не може да се оттегля
    		if(planning_drivers_ProductionTaskDetails::fetchField("#taskId = {$rec->id} AND #state != 'rejected'")){
    			$requiredRoles = 'no_one';
    		}
    	}
    }
    
    
    /**
     * След успешен запис
     */
    public static function on_AfterCreate(tasks_BaseDriver $Driver, embed_Manager $Embedder, &$rec)
    {
    	if(isset($rec->originId)){
    		$originDoc = doc_Containers::getDocument($rec->originId);
    		$originRec = $originDoc->fetch();
    		
    		// Ако е по източник
    		if(isset($rec->systemId)){
    			$tasks = cat_Products::getDefaultProductionTasks($originRec->productId, $originRec->quantity);
    			if(isset($tasks[$rec->systemId])){
    				$def = $tasks[$rec->systemId];
    			
    				// Намираме на коя дефолтна задача отговаря и извличаме продуктите от нея
    				$r = array();
    				foreach (array('production' => 'product', 'input' => 'input', 'waste' => 'waste') as $var => $type){
    					if(is_array($def->products[$var])){
    						foreach ($def->products[$var] as $p){
    							$p = (object)$p;
    							$nRec = new stdClass();
    							$nRec->taskId         = $rec->id;
    							$nRec->packagingId    = $p->packagingId;
    							$nRec->quantityInPack = $p->quantityInPack;
    							$nRec->planedQuantity = $p->packQuantity * $rec->totalQuantity;
    							$nRec->productId      = $p->productId;
    							$nRec->type			  = $type;
    							$nRec->storeId		  = $originRec->storeId;
    							
    							planning_drivers_ProductionTaskProducts::save($nRec);
    						}
    					}
    				}
    			}
    		} else {
    			
    			// Ако не е към дефолтна задача, винаги слагаме за произвеждане самия артикул
    			$nRec = new stdClass();
    		    $nRec->taskId = $rec->id;
    			$nRec->packagingId    = cat_Products::fetchField($originRec->productId, 'measureId');
    			$nRec->quantityInPack = 1;
    			$nRec->planedQuantity = $rec->totalQuantity;
    			$nRec->productId      = $originRec->productId;
    			$nRec->type			  = 'product';
    			$nRec->storeId		  = $originRec->storeId;
    			
    			planning_drivers_ProductionTaskProducts::save($nRec);
    		}
    	}
    }
}
