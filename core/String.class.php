<?php

/**
 * Клас 'core_String' ['str'] - Функции за за работа със стрингове
 *
 *
 * @category   Experta Framework
 * @package    core
 * @subpackage string
 * @author     Milen Georgiev <milen@download.bg>
 * @copyright  2006-2009 Experta Ltd.
 * @license    GPL 2
 * @version    CVS: $Id:$
 * @link
 * @since      v 0.1
 */
class core_String
{
    
    
    /**
     * Конвертира всички европейски азбуки,
     * включително и кирилицата, но без гръцката към латиница
     *
     * @param  string $text текст за конвертиране
     * @return string резултат от конвертирането
     * @access public
     */
    function utf2ascii($text)
    {
        static $trans = array();
        
        if (!count($trans)) {
            ob_start();
            require_once(dirname(__FILE__) . '/transliteration.inc.php');
            ob_end_clean();
            
            $trans = $code;
        }
        
        foreach ($trans as $alpha => $lat) {
            $text = str_replace($alpha, $lat, $text);
        }
        
        preg_match_all('/[A-Z]{2,3}[a-z]/', $text, $matches);
        
        foreach ($matches[0] as $upper) {
            $cap = ucfirst(strtolower($upper));
            $text = str_replace($upper, $cap, $text);
        }
        
        return $text;
    }
    
    
    /**
     *  @todo Чака за документация...
     */
    function getUniqId($len = 8)
    {
        $res = chr(ord('a') + rand(0, 25));
        
        while ($len > 1) {
            $r = rand(0, 35);
            
            if ($r <= 9) {
                $res .= chr(ord('0') + $r);
            } else {
                $res .= chr(ord('a') + $r - 10);
            }
            
            $len--;
        }
        
        return $res;
    }
    
    
    /**
     *  @todo Чака за документация...
     */
    function cut($str, $beginMark, $endMark = '', $caseSensitive = FALSE)
    {
        if (!$caseSensitive) {
            $sample = mb_strtolower($str);
            $beginMark = mb_strtolower($beginMark);
            $endMark = mb_strtolower($endMark);
        } else {
            $sample = $str;
        }
        
        $begin = mb_strpos($sample, $beginMark);
        
        if ($begin === FALSE) return;
        
        $begin = $begin + mb_strlen($beginMark);
        
        if ($endMark) {
            $end = mb_strpos($str, $endMark, $begin);
            
            if ($end === FALSE) return;
            
            $result = mb_substr($str, $begin, $end - $begin);
        } else {
            $result = mb_substr($str, $begin);
        }
        
        return $result;
    }
    
    
    /**
     *  @todo Чака за документация...
     */
    function findOn($str, $match, $until = -1)
    {
        $str = mb_strtolower($str);
        $match = mb_strtolower($match);
        $find = mb_strpos($str, $match);
        
        if ($find === FALSE)
        return FALSE;
        
        if ($until < 0)
        return TRUE;
        
        if ($find <= $until)
        return TRUE;
        else
        return FALSE;
    }
    
    
    /**
     *  @todo Чака за документация...
     */
    function addHash($str, $length = 4)
    {
        
        return $str . "_" . substr(md5(EF_SALT . $str), 0, $length);
    }
    
    
    /**
     *  @todo Чака за документация...
     */
    function checkHash($str, $length = 4)
    {
        if ($str == str::addHash(substr($str, 0, strlen($str) - $length - 1), $length) && substr($str, -1 - $length, 1) == "_") {
            return substr($str, 0, strlen($str) - $length - 1);
        }
        
        return FALSE;
    }
    
    
    /**
     * Конвертиране между PHP и MySQL нотацията
     */
    function phpToMysqlName($name)
    {
        $name = trim($name);
        
        for ($i = 0; $i < strlen($name); $i++) {
            $c = $name{$i};
            
            if ((($lastC >= "a" && $lastC <= "z") || ($lastC >= "0" && $lastC <= "9")) && ($c >= "A" && $c <= "Z")) {
                $mysqlName .= "_";
            }
            $mysqlName .= $c;
            $lastC = $c;
        }
        
        return strtolower($mysqlName);
    }
    
    
    /**
     * Превръща mysql име (с подчертавки) към нормално име
     */
    function mysqlToPhpName($name)
    {
        $cap = FALSE;
        
        for ($i = 0; $i < strlen($name); $i++) {
            $c = $name{$i};
            
            if ($c == "_") {
                $cap = TRUE;
                continue;
            }
            
            if ($cap) {
                $out .= strtoupper($c);
                $cap = FALSE;
            } else {
                $out .= strtolower($c);
            }
        }
        
        return $out;
    }
    
    
    /**
     * Конвертира стринг до уникален стринг с дължина, не по-голяма от указаната
     * Уникалността е много вероятна, но не 100% гарантирана ;)
     */
    function convertToFixedKey($str, $length = 64, $md5Len = 32, $separator = "_")
    {
        if (strlen($str) <= $length) return $str;
        
        $strLen = $length - $md5Len - strlen($separator);
        
        if ($strlen < 0)
        error("Дължината на MD5 участъка и разделителя е по-голяма от зададената обща дължина", array(
            'length' => $length,
            'md5Len' => $md5Len
        ));
        
        if (ord(substr($str, $strLen - 1, 1)) >= 128 + 64) {
            $strLen--;
            $md5Len++;
        }
        
        $md5 = substr(md5(_SALT_ . $str), 0, $md5Len);
        
        return substr($str, 0, $strLen) . $separator . $md5;
    }
    
    
    /**
     * Парсира израз, където променливите започват с #
     */
    function prepareExpression($expr, $nameCallback)
    {
        $len = strlen($expr);
        $esc = FALSE;
        $isName = FALSE;
        $lastChar = '';
        
        for ($i = 0; $i <= $len; $i++) {
            $c = $expr{$i};
            
            if ($c == "'" && $lastChar != "\\") {
                $esc = (!$esc);
            }
            
            if ($esc) {
                $out .= $c;
                $lastChar = $c;
                continue;
            }
            
            if ($isName) {
                if (($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || ($c >= '0' && $c <= '9') || $c == '_') {
                    $name .= $c;
                    continue;
                } else {
                    // Край на името
                    $isName = FALSE;
                    $out .= call_user_func($nameCallback, $name);
                    $out .= $c;
                    $lastChar = $c;
                    continue;
                }
            } else {
                if ($c == '#') {
                    $name = '';
                    $isName = TRUE;
                    continue;
                } else {
                    $out .= $c;
                    $lastChar = $c;
                }
            }
        }
        
        return $out;
    }
    
    
    /**
     * Проверка дали символът е латинска буква
     */
    function isLetter($c)
    {
        
        return ($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || $c == '_';
    }
    
    
    /**
     * Проверка дали символът е цифра
     */
    function isDigit($c)
    {
        
        return $c >= '0' && $c <= '9';
    }
}