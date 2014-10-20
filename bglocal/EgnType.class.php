<?php



/**
 * Клас 'drdata_EgnType' -
 *
 * тип ЕГН
 *
 * @category  bgerp
 * @package   bglocal
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class bglocal_EgnType extends type_Varchar
{
    
    
    /**
     * Колко символа е дълго полето в базата
     */
    var $dbFieldLen = 10;
    
    
    /**
     * Параметър определящ максималната широчина на полето
     */
    var $maxFieldSize = 10;
    
    
    /**
     * Performs the parity check - we expect a 10-digit number!
     *
     * @param string $egn_string
     * @return boolean
     */
    function isValid($value)
    {
        if(!$value) return NULL;
        
        $value = trim($value);
        
        try {
            $Egn = new bglocal_BulgarianEGN($value);
        } catch(Exception $e) {
            $err = $e->getMessage();
        }
        
        $res = array();
        $res['value'] = $value;
        
        if($err) {
            $res['error'] = $err;
            $Lnc = new bglocal_BulgarianLNC();
            
            if ($Lnc->isLnc($value) === TRUE) {
                unset($res['error']);
            } else {
                $res['error'] .= $Lnc->isLnc($value);
            }
        }
        
        return $res;
    }
    
    
    /**
     * Представя ЕГН-то в разбираем за потребителя вид
     */
    function toVerbal($value)
    {
        if(!$value) return NULL;
        
        try {
            $Egn = new bglocal_BulgarianEGN($value);
        } catch(Exception $e) {
            $err = $e->getMessage();
        }
        
        if($err) {
            $color = 'green';
            $type = 'ЛНЧ';
        } else {
            $color = 'black';
            $type = 'ЕГН';
        }
        
        return "<span style=\"color:{$color}\">" . tr($type) . " {$value}</span>";
    }
}