<?php


/**
 * Клас 'drdata_Vats' -
 *
 * @todo: Да се документира този клас
 *
 * @category   Experta Framework
 * @package    drdata
 * @author
 * @copyright  2006-2011 Experta OOD
 * @license    GPL 2
 * @version    CVS: $Id:$\n * @link
 * @since      v 0.1
 */
class drdata_Vats extends core_Manager
{
    /**
     *  @todo Чака за документация...
     */
    var $loadList = 'plg_RowTools,plg_Sorting,drdata_Wrapper';
    
    
    /**
     *  @todo Чака за документация...
     */
    const statusUnknow = 'unknown';
    
    
    /**
     *  @todo Чака за документация...
     */
    const statusValid = 'valid';
    
    
    /**
     *  @todo Чака за документация...
     */
    const statusInvalid = 'invalid';
    
    
    /**
     *  @todo Чака за документация...
     */
    const statusSyntax = 'syntax';
    
    
    /**
     *  @todo Чака за документация...
     */
    const statusNotVat = 'not_vat';
    
    
    /**
     *  @todo Чака за документация...
     */
    var $title = 'Регистър на данъчните номера';
    
    
    /**
     *  @todo Чака за документация...
     */
    var $canWrite = 'no_one';
    
    
    /**
     *  Описание на модела (таблицата)
     */
    function description()
    {
        $this->FLD('vat', 'drdata_vatType(64)', 'caption=VAT');
        $this->FLD('status', 'enum(syntax,valid,invalid,unknown)', 'caption=Състояние,input=none');
        $this->FLD('lastChecked', 'datetime', 'caption=Проверен на,input=none');
        $this->FLD('lastUsed', 'datetime', 'caption=Използван на,input=none');
        
        $this->setDbUnique('vat');
    }
    
    
    /**
     *  @todo Проверява за съществуващ VAT номер
     */
    function act_Check()
    {
    	$form = drdata_Vats::getForm();
    	$form->title = 'Проверка на VAT номер';
    	$form->toolbar->addSbBtn('Провери');
    	$form->toolbar->addBtn('Назад', array($this));
    	$form->input();
    	if ($form->isSubmitted()) {
			if (!strlen(trim($form->input()->vat))) {
				$res = new Redirect (array($this, 'Check'), 'Не сте въвели VAT номер');
			} else {
				$res = new Redirect (array($this), 'Данъчният номер е валиден');	
			}
    		
    		return $res;
    	}
    	
    	return $this->renderWrapping($form->renderHtml());
    }
    
    
    /**
     *  @todo Генерира бутон, който препраща в страница за проверка на VAT номер
     */
    function on_AfterPrepareListToolbar($mvc, $res, $data)
    {
    	$data->toolbar->addBtn('Проверка на VAT номер', array($this, 'Check'));
    }
    
    
    /**
     * Пълна проверка на VAT номер - синтактична + онлайн проверка.
     *
     * @param string $vat
     * @return string 'syntax', 'valid', 'invalid', 'unknown'
     */
    function check(&$vat)
    {
        $canonocalVat = $this->canonize($vat);
        
        $rec = $this->fetch(array("#vat = '[#1#]'", $canonocalVat));
        
        if(!$this->isHaveVatPrefix($vat)) {
            $status = self::statusNotVat;
        } elseif (!$this->checkSyntax($canonocalVat)) {
            $status = self::statusSyntax;
        } elseif ($rec) {
            $status = $rec->status;
            $rec->lastUsed = dt::verbal2mysql();
        } else {
            $status = $this->checkStatus($canonocalVat);
            $rec->vat = $canonocalVat;
            $rec->status = $status;
            $rec->lastUsed = dt::verbal2mysql();
            $rec->lastChecked = dt::verbal2mysql();
        }
        
        if ($rec) {
            $this->save($rec);
        }
        
        return $status;
    }
    
    
    /**
     * Проверява дали номерът започва с префикс, като за VAT
     */
    function isHaveVatPrefix($value)
    {
        $vatPrefixes = arr::make("BE,BG,CY,CZ,DK,EE,EL,DE,PT,FR,FI,HU,LU,MT,SI,IE,IT,LV,LT,NL,PL,SK,RO,SE,ES,GB", TRUE);
        
        if($vatPrefixes[substr($value, 0, 2)]) {
            
            return TRUE;
        } else {
            
            return FALSE;
        }
    }
    
    
    /**
     *  @todo Чака за документация...
     */
    function act_UpdateStatus()
    {
        expect(isDebug());
        
        return $this->cron_UpdateStatus();
    }
    
    
    /**
     *  @todo Чака за документация...
     */
    function cron_UpdateStatus()
    {
        $query = $this->getQuery();
        $before2Month = dt::addDays(-60);
        $query->where('DATE_SUB(#lastUsed, INTERVAL 1 MONTH) >= #lastChecked');
        $query->where("#lastChecked < '{$before2Month}'");
        $query->limit(1);
        
        $nAffected = 0;
        
        while ($rec = $query->fetch()) {
            $recentStatus = $this->check($rec->vat);
            
            if ($recentStatus != self::statusUnknown && $recentStatus != $rec->status) {
                $rec->status = $recentStatus;
            }
            $rec->lastChecked = dt::verbal2mysql();
            $this->save($rec);
            $nAffected++;
        }
        
        return "Обновени {$nAffected} VAT номера.";
    }
    
    
    /**
     * Онлайн проверка за валидността на VAT номер.
     *
     * @param string $vat
     * @return string 'valid', 'invalid', 'unknown'
     */
    function checkStatus($vat)
    {
        $countryCode = substr($vat, 0, 2);
        $vatNumber = substr($vat, 2);
        
        $client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
        $params = array('countryCode' => $countryCode, 'vatNumber' => $vatNumber);
        
        try {
            $result = $client->checkVat($params);
        } catch (Exception $e) {
        }
        
        $res = self::statusUnknow;
        
        if ($result->valid === true) {
            $res = self::statusValid;
        } elseif ($result->valid === false) {
            $res = self::statusInvalid;
        }
        
        return $res;
    }
    
    
    /**
     * Синтактична валидация на VAT номер от Европейския съюз
     *
     * @see http://php.net/manual/de/function.preg-match.php
     *
     * @param integer $vat VAT number to test e.g. GB123 4567 89
     * @return integer -1 if country not included OR 1 if the VAT Num matches for the country OR 0 if no match
     *
     */
    function checkSyntax($vat) {
        switch(strtoupper(substr($vat,0, 2))) {
            case 'AT':
                $regex = '/^(AT){0,1}U[0-9]{8}$/i';
                break;
            case 'BE':
                $regex = '/^(BE){0,1}[0]{0,1}[0-9]{9}$/i';
                break;
            case 'BG':
                $regex = '/^(BG){0,1}[0-9]{9,10}$/i';
                break;
            case 'CY':
                $regex = '/^(CY){0,1}[0-9]{8}[A-Z]$/i';
                break;
            case 'CZ':
                $regex = '/^(CZ){0,1}[0-9]{8,10}$/i';
                break;
            case 'DK':
                $regex = '/^(DK){0,1}([0-9]{2}[\ ]{0,1}){3}[0-9]{2}$/i';
                break;
            case 'EE':
            case 'DE':
            case 'PT':
            case 'EL':
                $regex = '/^(EE|EL|DE|PT){0,1}[0-9]{9}$/i';
                break;
            case 'FR':
                $regex = '/^(FR){0,1}[0-9A-Z]{2}[\ ]{0,1}[0-9]{9}$/i';
                break;
            case 'FI':
            case 'HU':
            case 'LU':
            case 'MT':
            case 'SI':
                $regex = '/^(FI|HU|LU|MT|SI){0,1}[0-9]{8}$/i';
                break;
            case 'IE':
                $regex = '/^(IE){0,1}[0-9][0-9A-Z\+\*][0-9]{5}[A-Z]$/i';
                break;
            case 'IT':
            case 'LV':
                $regex = '/^(IT|LV){0,1}[0-9]{11}$/i';
                break;
            case 'LT':
                $regex = '/^(LT){0,1}([0-9]{9}|[0-9]{12})$/i';
                break;
            case 'NL':
                $regex = '/^(NL){0,1}[0-9]{9}B[0-9]{2}$/i';
                break;
            case 'PL':
            case 'SK':
                $regex = '/^(PL|SK){0,1}[0-9]{10}$/i';
                break;
            case 'RO':
                $regex = '/^(RO){0,1}[0-9]{2,10}$/i';
                break;
            case 'SE':
                $regex = '/^(SE){0,1}[0-9]{12}$/i';
                break;
            case 'ES':
                $regex = '/^(ES){0,1}([0-9A-Z][0-9]{7}[A-Z])|([A-Z][0-9]{7}[0-9A-Z])$/i';
                break;
            case 'GB':
                $regex = '/^(GB){0,1}([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2})|([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2}[\ ]{0,1}[0-9]{3})|((GD|HA)[0-9]{3})$/i';
                break;
            default:
            return -1;
            break;
        }
        
        return preg_match($regex, $vat);
    }
    
    
    /**
     * Връща каноничното представяне на VAT номер - големи букви, без интервали.
     *
     * @param string $vat
     */
    function canonize($vat)
    {
        $canonicalVat = preg_replace('/[^a-z\d]/i', '', $vat);
        $canonicalVat = strtoupper($canonicalVat);
        
        return $canonicalVat;
    }
}