<?php


/**
 * Дали да се форсира синхронизацията на регистрите
 */
defIfNot('ZTM_FORCE_REGISTRY_SYNC', false);


/**
 * Клас 'ztm_Plugin'
 *
 * Табло с настройки за състояния
 *
 *
 * @author    Nevena Georgieva <nevena.georgieva89@gmail.com>
 * @copyright 2006 - 2019 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 */
class ztm_Setup extends core_ProtoSetup
{
    
    /**
     * Необходими пакети
     */
    public $depends = 'acs=0.1,sens2=0.1';
    
    
    /**
     * Версия на пакета
     */
    public $version = '0.1';
    
    
    /**
     * Мениджър - входна точка в пакета
     */
    public $startCtr = 'ztm_Adapter';
    
    
    /**
     * Описание на модула
     */
    public $info = 'Контролен панел';
    
    
    /**
     * Роли за достъп до модула
     */
    public $roles = array(
        array('ztm'),
    );
    
    
    /**
     * Връзки от менюто, сочещи към модула
     */
    public $menuItems = array(
        array(3.4, 'Мониторинг', 'ZTM', 'ztm_Devices', 'default', 'ztm, ceo'),
    );


    /**
     * @var string
     */
    public $defClasses = 'ztm_SensMonitoring';
    
    
    /**
     * Списък с мениджърите, които съдържа пакета
     */
    public $managers = array(
        'ztm_Devices',
        'ztm_Groups',
        'ztm_Registers',
        'ztm_RegisterValues',
        'ztm_LongValues',
        'ztm_Profiles',
        'ztm_ProfileDetails',
        'migrate::addZtmSens2402'
    );


    /**
     * Миграция за добавяне на драйвер за ZTM
     */
    function addZtmSens2402()
    {
        $zQuery = ztm_Devices::getQuery();
        $zQuery->where("#state = 'active'");
        while ($zRec = $zQuery->fetch()) {
            ztm_SensMonitoring::addSens($zRec->name);
        }
    }
}
