<?php


/**
 * Помощен клас-имплементация на интерфейса acc_TransactionSourceIntf за класа planning_ReturnNotes
 *
 * @category  bgerp
 * @package   planning
 *
 * @author    Ivelin Dimov <ivelin_pdimov@abv.com>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 * @see acc_TransactionSourceIntf
 *
 */
class planning_transaction_ReturnNote extends acc_DocumentTransactionSource
{
    /**
     * @param int $id
     *
     * @return stdClass
     *
     * @see acc_TransactionSourceIntf::getTransaction
     */
    public function getTransaction($id)
    {
        // Извличане на мастър-записа
        expect($rec = $this->class->fetchRec($id));
        $rec->valior = empty($rec->valior) ? dt::today() : $rec->valior;
        
        $result = (object) array(
            'reason' => "Протокол за връщане от производство №{$rec->id}",
            'valior' => $rec->valior,
            'totalAmount' => null,
            'entries' => array()
        );
        
        if (isset($rec->id)) {
            $entries = $this->getEntries($rec, $result->totalAmount);
            
            if (countR($entries)) {
                $result->entries = array_merge($result->entries, $entries);
            }
        }
        
        return $result;
    }
    
    
    /**
     * Връща записите на транзакцията
     */
    private static function getEntries($rec, &$total)
    {
        $entries = array();
        $errorArr = array();
        $productsArr = array();
        
        $dQuery = planning_ReturnNoteDetails::getQuery();
        $dQuery->where("#noteId = {$rec->id}");
        while ($dRec = $dQuery->fetch()) {
            if(empty($dRec->quantity)) continue;

            $prodRec = cat_Products::fetch($dRec->productId, 'canStore,fixedAsset');
            $productsArr[$dRec->productId] = $dRec->productId;
            $creditArr = null;
            
            if ($rec->useResourceAccounts == 'yes') {
                $creditArr = array('61101', array('cat_Products', $dRec->productId), 'quantity' => $dRec->quantity);
            }
            
            // Ако не е ресурс, кредитираме общата сметка за разходи '61102. Други разходи (общо)'
            if($prodRec->canStore == 'yes'){
                $debitArr = array(321, array('store_Stores', $rec->storeId), array('cat_Products', $dRec->productId), 'quantity' => $dRec->quantity);
                $reason = 'Връщане на материал в производството';
            } else {
                $expenseItem = ($prodRec->fixedAsset == 'yes') ? array('cat_Products', $dRec->productId) : acc_Items::forceSystemItem('Неразпределени разходи', 'unallocated', 'costObjects')->id;
                $debitArr = array(60201, $expenseItem, array('cat_Products', $dRec->productId), 'quantity' => $dRec->quantity);
                $reason = 'Връщане на услуга в производството';
            }

            $averageAmount = null;
            if (empty($creditArr)) {
                $creditArr = array('61102');

                $type = ($prodRec->canStore == 'yes') ? 'материал' : 'услуга';
                $reason = "Връщане от производство на {$type} без детайли";

                // Сумата с която ще върнем артикула в склада е неговата средно претеглена
                if($prodRec->canStore == 'yes'){
                    $averageAmount = cat_Products::getWacAmountInStore($dRec->quantity, $dRec->productId, $rec->valior, $rec->storeId);

                    if (!isset($averageAmount)) {
                        $averageAmount = cat_Products::getPrimeCost($dRec->productId);
                        if (isset($averageAmount)) {
                            $averageAmount = $dRec->quantity * $averageAmount;
                        }
                    }

                    if (!isset($averageAmount)) {
                        $errorArr[] = cls::get('cat_Products')->getTitleById($dRec->productId);
                        $averageAmount = 0;
                    }
                }
            }

            $entry = array('debit' => $debitArr, 'credit' => $creditArr, 'reason' => $reason);
            
            if (!is_null($averageAmount)) {
                $entry['amount'] = $averageAmount;
                $total += $averageAmount;
            }
            
            $entries[] = $entry;
        }
        
        // Ако някой от артикулите не може да бдъе произведем сетваме, че ще правим редирект със съобщението
        if (acc_Journal::throwErrorsIfFoundWhenTryingToPost()) {
            if (countR($errorArr)) {
                $errorArr = implode(', ', $errorArr);
                acc_journal_RejectRedirect::expect(false, "Артикулите: |{$errorArr}|* не могат да бъдат върнати защото липсва себестойност");
            }
            
            $msg = "трябва да са вложими";
            if($redirectError = deals_Helper::getContoRedirectError($productsArr, 'canConvert', null, $msg)){
                
                acc_journal_RejectRedirect::expect(false, $redirectError);
            }
            
            $msg = "са генерични и трябва да бъдат заменени";
            if($redirectError = deals_Helper::getContoRedirectError($productsArr, null, 'generic', $msg)){
                
                acc_journal_RejectRedirect::expect(false, $redirectError);
            }
        }
        
        // Връщаме ентритата
        return $entries;
    }
}
