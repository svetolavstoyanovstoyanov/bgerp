<?php



/**
 * Покупки - опаковка
 *
 *
 * @category  bgerp
 * @package   sales
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class sales_Wrapper extends plg_ProtoWrapper
{
    
    
    /**
     * Описание на табовете
     */
    function description()
    {
        $this->TAB('sales_Sales', 'Продажби', 'ceo,sales');
        $this->TAB('sales_Invoices', 'Фактури', 'ceo,sales');
        $this->TAB('sales_Quotations', 'Оферти', 'ceo,sales');
        $this->TAB('sales_SaleRequests', 'Заявки', 'ceo,sales');
        $this->TAB('sales_Routes', 'Маршрути', 'ceo,sales');
        $this->TAB('dec_Declarations', 'Декларации', 'ceo,dec');
        $this->TAB('sales_ClosedDealsDebit', 'Приключени сделки', 'ceo,sales');
        
        $this->title = 'Продажби « Търговия';
        Mode::set('menuPage', 'Търговия:Продажби');
        
    }
}