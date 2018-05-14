<?php



/**
 * Помощен клас за рутиране на запитвания
 *
 * @category  bgerp
 * @package   marketing
 * @author    Ivelin Dimov <ivelin_pdimov@abv.com>
 * @copyright 2006 - 2018 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 *
 */
class marketing_InquiryRouter extends core_Manager
{
	
	
	/**
	 * Рутиране на запитване
	 * 
	 * @param stdClass $rec - запис на запитване
	 * @return int - ид на папка
	 */
	public static function route($rec)
	{
		if(core_Packs::isInstalled('colab')){
			$firstFolderId = colab_FolderToPartners::getLastSharedCompanyFolder();
			
			if(!empty($firstFolderId)) return $firstFolderId;
		}
		
		// Кой ще е отговорника на папката
		$inCharge = marketing_Router::getInChargeUser($rec->place, $rec->country);
		
		// Ако има компания
		if(empty($rec->company)){
			try{
				expect($rec->personNames, $rec);
			} catch(core_exception_Expect $e){
				reportException($e);
				static::logErr('Липсва име за контактни данни');
			}
			// Рутиране на запитване от лице
			$folderId = static::routeInquiryFromPerson($rec, $inCharge);
		} else {
			
			// Рутиране на запитване от фирма
			$folderId = static::routeInquiryFromCompany($rec, $inCharge);
		}
		
		// Трябва да е намерена папка
		expect($folderId);
		
		return $folderId;
	}
	
	
	/**
	 * Рутира запитване от лице
	 * 
	 * 1.Която е от тип фирма и във визитката има същия имейл, като посочения в запитването;
	 * 1.Която е от тип "Лице" и във визитката има същия имейл като посочения в запитването.
	 * 1.Търсим папка, по зададения имейл, чрез метода на имейл-рутера, но само от тип "Фирма" или "Лице".
	 *   Ако намерената папка не е от посочения тип - това правило пропада.
	 * 1.Която е от тип "Лице" и има същото име на човек и е от същата държава. Това правило сработва, само ако имаме посочени поне две имена на лицето.
	 * 1.Ако нито едно от горните не сработва, създаваме нова папка, с корица "Лице" с данните от запитването.
	 * 
	 * @param stdClass $rec - запис на лице
	 * @param int $inCharge - отговорник
	 * @return int - ид на папка
	 */
	private static function routeInquiryFromPerson($rec, $inCharge)
	{
		// Ако има папка на фирма с този имейл
		$folderId = marketing_Router::routeByCompanyEmail($rec->email, $inCharge);
		if($folderId) return $folderId;
		
		// Ако има папка на лице с този имейл
		$folderId = marketing_Router::routeByPersonEmail($rec->email, $inCharge);
		if($folderId) return $folderId;
		
		// Ако има папка на контрагент с този имейл
		$folderId = marketing_Router::routeByEmail($rec->email, 'contragent');
		if($folderId) return $folderId;
		
		// Ако има лице във визитника от същата държава
		$folderId = marketing_Router::routeByPerson($rec->personNames, $rec->country, $inCharge);
		if($folderId) return $folderId;
		
		// Опит за рутиране по БРИД
		$folderId = marketing_Router::routeByBrid($rec->brid);
		if($folderId) return $folderId;
		
		// Форсиране на папка и запис във визитника на лице с посочените данни
		return marketing_Router::forcePersonFolder($rec->personNames, $rec->email, $rec->country, $rec->tel, $rec->pCode, $rec->place, $rec->address, $inCharge);
	}
	
	
	/**
	 * Рутиране на запитване от фирма
	 * 
	 * 1 Която има визитка тип "Фирма" и в нея има същия имейл, като от запитването
	 * 2 Търсим папка (но само от тип "Фирма"), по зададения имейл, чрез метода на имейл-рутера. Ако намерената папка не е "Фирма" - това правило пропада.
	 * 3 Която е от тип "Фирма" и има същото (приблизително) име и държава, като от запитването;
	 * 4 Ако нито едно от горните не сработва, създаваме нова папка, с корица "Фирма" с данните от запитването.
	 * 
	 * @param stdClass $rec - запис на запитване
	 * @param int $inCharge - ид на отговорник
	 * @return int - ид на папка
	 */
	private static function routeInquiryFromCompany($rec, $inCharge)
	{
		// Намираме папка на компания с този имейл
		core_Debug::startTimer("routebycompanyemail{$rec->id}");
		
		$folderId = marketing_Router::routeByCompanyEmail($rec->email, $inCharge);
		
		core_Debug::stopTimer("routebycompanyemail{$rec->id}");
		$time1 = round(core_Debug::$timers["routebycompanyemail{$rec->id}"]->workingTime, 2);
		log_System::add('marketing_Inquiries2', "routeByCompanyEmail - '{$time1}' - FOUND '{$folderId}'", $rec->id);
		
		if($folderId) return $folderId;
		
		// Рутиране според имейла, взимаме папката ако корицата и е фирма
		core_Debug::startTimer("routeByEmail{$rec->id}");
		$folderId = marketing_Router::routeByEmail($rec->email, 'company');
		core_Debug::stopTimer("routeByEmail{$rec->id}");
		$time2 = round(core_Debug::$timers["routeByEmail{$rec->id}"]->workingTime, 2);
		log_System::add('marketing_Inquiries2', "routeByEmail - '{$time2}' - FOUND '{$folderId}'", $rec->id);
		
		if($folderId) return $folderId;
		
		// Рутираме в папка на фирма със същото име от същата държава
		core_Debug::startTimer("routeByCompanyName{$rec->id}");
		$folderId = marketing_Router::routeByCompanyName($rec->company, $rec->country, $inCharge);
		core_Debug::stopTimer("routeByCompanyName{$rec->id}");
		$time3 = round(core_Debug::$timers["routeByCompanyName{$rec->id}"]->workingTime, 2);
		log_System::add('marketing_Inquiries2', "routeByCompanyName - '{$time3}' - FOUND '{$folderId}'", $rec->id);
		
		if($folderId) return $folderId;
		
		// Опит за рутиране по БРИД
		core_Debug::startTimer("routeByBrid{$rec->id}");
		$folderId = marketing_Router::routeByBrid($rec->brid);
		core_Debug::stopTimer("routeByBrid{$rec->id}");
		$time4 = round(core_Debug::$timers["routeByBrid{$rec->id}"]->workingTime, 2);
		log_System::add('marketing_Inquiries2', "routeByBrid - '{$time4}' - FOUND '{$folderId}'", $rec->id);
		
		if($folderId) return $folderId;
		
		// Форсиране на папка и визитка на фирма с въведените данни
		return marketing_Router::forceCompanyFolder($rec->company, $rec->email, $rec->country, $rec->tel, $rec->pCode, $rec->place, $rec->address, $inCharge);
	}
}