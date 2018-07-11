<?php


/**
 * Прототип на драйвер за контролер
 *
 *
 * @category  bgerp
 * @package   sens2
 *
 * @author    Milen Georgiev <milen@experta.bg>
 * @copyright 2006 - 2015 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 */
class sens2_ProtoDriver
{
    /**
     * От кой номер започва броенето на слотовете
     */
    const FIRST_SLOT_NO = 0;
    
    
    /**
     * Интерфейси, поддържани от всички наследници
     */
    public $interfaces = 'sens2_ControllerIntf';
    
    
    /**
     *  Информация за входните портове на устройството
     *
     * @see  sens2_ControllerIntf
     *
     * @return array
     */
    public function getInputPorts($config = null)
    {
        $res = array();
        
        if (is_array($this->inputs)) {
            foreach ($this->inputs as $name => $params) {
                $res[$name] = (object) array('caption' => $params['caption'], 'uom' => $params['uom']);
            }
        }
        
        return $res;
    }
    
    
    /**
     * Информация за изходните портове на устройството
     *
     * @see  sens2_ControllerIntf
     *
     * @return array
     */
    public function getOutputPorts()
    {
        $res = array();
        
        if (is_array($this->outputs)) {
            foreach ($this->outputs as $name => $params) {
                $res[$name] = (object) array('caption' => $params['caption'], 'uom' => $params['uom']);
            }
        }
        
        return $res;
    }
    
    
    /**
     * Подготвя форма с настройки на контролера, като добавя полета с $form->FLD(....)
     *
     * @see  sens2_ControllerIntf
     *
     * @param core_Form
     */
    public function prepareConfigForm($form)
    {
    }
    
    
    /**
     * Проверява след  субмитване формата с настройки на контролера
     *
     * @see  sens2_ControllerIntf
     *
     * @param   core_Form
     */
    public function checkConfigForm($form)
    {
    }
    
    
    /**
     * Връща масив със стойностите на изразходваната активна мощност
     */
    public function readInputs($inputs, $config, &$persistentState)
    {
        return array();
    }
    
    
    /**
     * Сетва изходите на драйвера по зададен масив
     *
     * @return bool
     */
    public function writeOutputs($outputs, $config, &$persistentState)
    {
        return array();
    }
    
    
    /**
     * Връща снимка на контролера
     *
     * @param stdClass $config конфигурацията на контролера
     *
     * @return string|null
     */
    public static function getPicture($config)
    {
    }
    
    
    /**
     * Връща списъка с възможните слотове от посочен тип
     */
    public function getSlotOpt($type = array())
    {
        $slots = $this->getSlotCnt();
        
        $typeArr = arr::make($type, true);
        
        if (!count($typeArr)) {
            $typeArr = array_keys($slots);
        }
        
        $res = array();
        foreach ($typeArr as $st) {
            $cnt = (int) $slots[$st];
            
            for ($i = static::FIRST_SLOT_NO; $i < $cnt + static::FIRST_SLOT_NO; $i++) {
                $name = $st . '-' . $i;
                $res[$name] = $name;
            }
        }
        
        return $res;
    }
}
