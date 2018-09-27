<?php


/**
 * Минимален брой групи, необходими за да се покаже страничната навигация
 */
defIfNot('EPAY_MIN', '');


/**
 * Минимален брой групи, необходими за да се покаже страничната навигация
 */
defIfNot('EPAY_CHECKSUM', '');


/**
 * Сметка по която се очаква да пристигат плащанията от ePay.bg
 */
defIfNot('EPAY_OWN_ACCOUNT_ID', '');


/**
 * Име на подател на имейл, по който да се разпознава че е дошъл от ePay
 */
defIfNot('EPAY_EMAIL_DOMAIN', 'ntf@epay.bg');


/**
 * Пакет за интеграция с ePay.bg
 *
 * @category  bgerp
 * @package   epay
 *
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2018 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 */
class epay_Setup extends core_ProtoSetup
{
    /**
     * Версията на пакета
     */
    public $version = '0.1';
    
    
    /**
     * Описание на модула
     */
    public $info = 'Интеграция с ePay.bg, In development';
    
    
    /**
     * Дефинирани класове, които имат интерфейси
     */
    public $defClasses = 'epay_driver_OnlinePayment';


    /**
     * Описание на конфигурационните константи
     */
    public $configDescription = array(
        'EPAY_MIN' => array('varchar', 'caption=Настройки за онлайн плащане->MIN'),
        'EPAY_CHECKSUM' => array('varchar', 'caption=Настройки за онлайн плащане->CHECKSUM'),
        'EPAY_OWN_ACCOUNT_ID' => array('key(mvc=bank_OwnAccounts,select=title,allowEmpty)', 'caption=Настройки за онлайн плащане->Сметка'),
        'EPAY_EMAIL_DOMAIN' => array('varchar', 'caption=Имейл за получаване на плащане->Имейл'),
    );
    

    /**
     * Списък с мениджърите, които съдържа пакета
     */
    public $managers = array(
        'epay_Tokens',
    );
}
