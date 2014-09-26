<?php
/**
 * Помощен клас-имплементация на интерфейса acc_TransactionSourceIntf за класа cash_Pko
 *
 * @category  bgerp
 * @package   cash
 * @author    Ivelin Dimov <ivelin_pdimov@abv.com>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * 
 * @see acc_TransactionSourceIntf
 *
 */
class cash_transaction_Pko
{
    /**
     * 
     * @var cash_Pko
     */
    public $class;
    
    
    /**
     * @param int $id
     * @return stdClass
     * @see acc_TransactionSourceIntf::getTransaction
     */
    public function finalizeTransaction($id)
    {
    	$rec = $this->class->fetchRec($id);
    	$rec->state = 'closed';
    
    	if($this->class->save($rec)) {
    		$this->class->invoke('AfterActivation', array($rec));
    	}
    }
    
    
    /**
     *  Имплементиране на интерфейсен метод (@see acc_TransactionSourceIntf)
     *  Създава транзакция която се записва в Журнала, при контирането
     */
    public function getTransaction($id)
    {
    	// Извличаме записа
    	expect($rec = $this->class->fetchRec($id));
    	 
    	$origin = $this->class->getOrigin($rec);
    	
    	if($rec->isReverse == 'yes'){
    		// Ако документа е обратен, правим контировката на РКО-то но с отрицателен знак
    		$entry = cash_transaction_Rko::getReverseEntries($rec, $origin);
    	} else {
    		
    		// Ако документа не е обратен, правим нормална контировка на ПКО
    		$entry = $this->getEntry($rec, $origin);
    	}
    	
    	// Подготвяме информацията която ще записваме в Журнала
    	$result = (object)array(
    			'reason' => $rec->reason, // основанието за ордера
    			'valior' => $rec->valior,   // датата на ордера
    			'entries' => $entry
    	);
    	
    	return $result;
    }
    
    
    /**
     * Връща записа на транзакцията
     */
    private function getEntry($rec, $origin, $reverse = FALSE)
    {
    	$dealInfo = $origin->getAggregateDealInfo();
    	$amount = $rec->rate * $rec->amount;
    	
    	// Ако е обратна транзакцията, сумите и к-та са с минус
    	$sign = ($reverse) ? -1 : 1;
    	
    	// Кредита е винаги във валутата на пораждащия документ,
    	$creditCurrency = currency_Currencies::getIdByCode($dealInfo->get('currency'));
    	$creditQuantity = $amount / $dealInfo->get('rate');
    	
    	// Дебитираме касата
    	$debitArr = array($rec->debitAccount,
    						array('cash_Cases', $rec->peroCase),
    						array('currency_Currencies', $rec->currencyId),
    						'quantity' => $sign * $rec->amount,);
    	
    	// Кредитираме разчетната сметка
    	$creditArr = array($rec->creditAccount,
    							array($rec->contragentClassId, $rec->contragentId),
    							array($origin->className, $origin->that),
    							array('currency_Currencies', $creditCurrency),
    							'quantity' => $sign * $creditQuantity);
    	
    	$entry = array('amount' => $sign * $amount, 'debit' => $debitArr, 'credit' => $creditArr,);
    	
    	return array($entry);
    }
    
    
    /**
     * Връща обратна контировка на стандартната
     */
    public static function getReverseEntries($rec, $origin)
    {
    	$self = cls::get(get_called_class());
    	 
    	return $self->getEntry($rec, $origin, TRUE);
    }
}