<?php


/**
 * Конвертор на вътрешен markup към esc/pos команди за отпечатване
 *
 * @category  bgerp
 * @package   escprint
 * @author    Milen Georgiev <milen@experta.bg>
 * @copyright 2006 - 2017 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class escpos_Convert extends core_Manager
{
    
    
    /**
     * Заглавие
     */
    public $title = 'Конвертор на вътрешен markup към esc/pos команди за отпечатване';
    
    
    /**
     * 
     */
    public $canAdd = 'no_one';
    
    
    /**
     * 
     */
    public $canDelete = 'no_one';
    
    
    /**
     * 
     */
    public $canEdit = 'no_one';
    
    
    /**
     *
     */
    public $canList = 'no_one';
    


    /**
     * Процесира текст от ПринтМаркъп ESC команди и ascii текст
     */
    public static function process($markup, $driver = 'escpos_driver_Html')
    {
        $driver = cls::get($driver);

        $s = str_replace(array("\n", "\r", "\t"), array(), $markup);
     
        $elArr = explode('<', $s);
        
        $lines = array();
        $i = 0;
        $l = '';
 
        foreach($elArr as $el) {

            $col  = 0;
            $bold    = FALSE;
            $underline = '';
            $font = '';
            $tab  = $driver->getSpace();
            $width = $driver->getWidth();
            
            if(strpos($el, '>') !== FALSE) {
                list($tag, $text) = explode('>', $el);

                $textLen = mb_strlen($text);

                if(strlen($tag)) {
                    $cmd = strtolower($tag{0});
                    $attr = substr($tag, 1);
                    $attrArr = explode(' ', trim($attr));
                    foreach($attrArr as $a) {

                        if(!trim($a)) continue;

                        if(is_numeric($a)) {
                            $col = (int) $a;
                            continue;
                        }

                        if($a == 'b' || $a == 'B') {
                            $bold = TRUE;
                            continue;
                        }

                        if($a == 'u' ) {
                            $underline = 1;
                            continue;
                        }

                        if($a == 'U' ) {
                            $underline = 2;
                            continue;
                        }
                        
                        if($a == 'F' || $a == 'f') {
                            $font = $a;
                            $width = $driver->getWidth($font);
                            continue;
                        }

                        if($a == '.' || $a == '_' || $a == '=' || $a == '-') {
                            $tab = $a;
                            continue;
                        }

                        expect(FALSE, "Непознат атрибут", $a, $el);
                    }

                    $font = $driver->getFont($font, $bold, $underline);
                    $fontEnd = $driver->getFontEnd();
                    $newLine = $driver->GetNewLine();

                    switch($cmd) {
                        // Нова линия
                        case 'p':
                            $res .= $l;
                            // Код за преместване на хартията
                            $l = $newLine . $font . $text . $fontEnd;
                            $lLen = mb_strlen($text);
                            break;
                        case 'c':
                            $res .= $l;
                            // Код за преместване на хартията
                            $r = (int) (($width-$textLen)/2);

                            $r = max($r, 0);
                            if($r) {
                                $pad = str_repeat($tab , $r);
                            } else {
                                $pad = '';
                            }
                            $l = $newLine . $pad . $font . $text .$fontEnd;
                            $lLen = $r + $textLen;
                            break;
                        case 'l':
                            $r = $col - $lLen;
                            $r = max($r, 0);
                            if($r) {
                                $pad = str_repeat($tab , $r);
                            } else {
                                $pad = '';
                            }

                            $l .=  $pad . $font .  $text . $fontEnd;
                            $lLen += $r + $textLen;
                            break;

                        case 'r':
                            $r = $col - $lLen - $textLen;

                            $r = max($r, 0);
                            if($r) {
                                $pad = str_repeat($tab , $r);
                            } else {
                                $pad = '';
                            }
                            $l .= $pad . $font .  $text . $fontEnd;
                            $lLen = $r + $textLen;
                            break;
                        default:
                            expect(FALSE, "Непозната команда", $cmd, $el);

                    }
                }
            } else {
                $l .= $el;
            }
        }

        $res .= $l;

        $res = $driver->encode($res);

        return $res;
    }



    /**
     * Тестване на печата
     */
    function act_Test()
    {
        $test = "<c F b>Фактура №123/28.02.17" . 
        "<p><r32 =>" .
        "<p b>1.<l3 b>Кисело мляко" .
        "<p><l4>2.00<l12>х 0.80<r32>= 1.60" .
        "<p b>2.<l3 b>Хляб \"Добруджа\"" . "<l f> | годност: 03.03" .
        "<p><l4>2.00<l12>х 0.80<r32>= 1.60" .
        "<p b>3.<l3 b>Минерална вода" . 
        "<p><l4>2.00<l12>х 0.80<r32>= 1.60" .
        "<p><r32 =>" .
        "<p><r29 F b>Общо: 34.23 лв.";
        
        if (Request::get('p')) {
            $res = self::process($test, 'escpos_driver_Ddp250');
            echo $res;
            shutdown();
        }

        return self::process($test);
    }
}
