<?php

/**
 *  class bank_Setup
 *
 *  Инсталиране/Деинсталиране на
 *  мениджъра Bank
 *
 */
class bank_Setup
{
    /**
     *  @todo Чака за документация...
     */
    var $version = '0.1';
    
    
    /**
     *  @todo Чака за документация...
     */
    var $startCtr = 'bank_OwnAccounts';
    
    
    /**
     *  @todo Чака за документация...
     */
    var $startAct = 'default';
    
    
    /**
     *  @todo Чака за документация...
     */
    var $depends = 'drdata=0.1';
    
    
    /**
     *  Инсталиране на пакета
     */
    function install()
    {
        $managers = array(
            'bank_Accounts',
            'bank_AccountTypes',
            'bank_OwnAccounts',
            'bank_Documents'
        );
        
        // Роля за power-user на този модул
        $role = 'bank';
        $html = core_Roles::addRole($role) ? "<li style='color:green'>Добавена е роля <b>$role</b></li>" : '';
        
        $instances = array();
        
        foreach ($managers as $manager) {
            $instances[$manager] = &cls::get($manager);
            $html .= $instances[$manager]->setupMVC();
        }
        
        $Menu = cls::get('bgerp_Menu');
        $html .= $Menu->addItem(2, 'Финанси', 'Банки',  'bank_OwnAccounts', 'default', "{$role}, admin");
        
        return $html;
    }
        
    
    /**
     *  Де-инсталиране на пакета
     */
    function deinstall()
    {
        return "";
    }
}