<?php



/**
 * Клас 'store_plg_Document'
 *
 * Плъгин даващ възможност на даден документ да бъде складов документ
 *
 *
 * @category  bgerp
 * @package   store
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2013 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class store_plg_Document extends core_Plugin
{
	
	
	/**
	 * Помощна ф-я връщаща линк към документа с иконка
	 */
	public static function on_AfterGetDocLink($mvc, &$res, $id)
	{
		if($mvc->haveRightFor('single', $id)){
	    	$icon = sbf($mvc->getIcon($id), '');
	    	$handle = $mvc->getHandle($id);
	    	$attr['class'] = "linkWithIcon";
	        $attr['style'] = "background-image:url('{$icon}');";
	        $attr['title'] = "{$mvc->singleTitle} №{$id}";
	        
	    	$res = ht::createLink($handle, array($mvc, 'single', $id), NULL, $attr);
	    }
	}
	
	
	/**
	 * Изчислява обема и теглото на продуктите в документа
	 * @param core_Mvc $mvc
	 * @param stdClass $res
	 * @param array $products - продуктите в документа
	 */
	public function on_AfterGetMeasures($mvc, &$res, $products)
	{
		$obj = new stdClass();
		$obj->volume = 0;
		$obj->weight = 0;
		
		foreach ($products as $p){
			$pInfo = cls::get($p->classId)->getProductInfo($p->productId, $p->packagingId);
			if($obj->volume !== NULL){
				if($pack = $pInfo->packagingRec){
					$volume = $pack->sizeWidth * $pack->sizeHeight * $pack->sizeDepth;
					(!$volume) ? $obj->volume = NULL : $obj->volume += $p->packQuantity * $volume;
				} else {
					//$obj->volume = NULL;
				}
			}
			
			if($obj->weight !== NULL){
				if($pack = $pInfo->packagingRec){
					$weight = $pack->netWeight + $pack->tareWeight;
					(!$volume) ? $obj->weight = NULL : $obj->weight += $p->packQuantity * $weight;
				} else {
					//$obj->weight = NULL;
				}
			}
		}
		
		$res = $obj;
	}
}