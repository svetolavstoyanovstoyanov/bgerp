<?php
/**
 * Клас 'sales_SalesDetails'
 *
 * Детайли на мениджър на документи за продажба на продукти (@see sales_Sales)
 *
 * @category  bgerp
 * @package   sales
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class sales_SalesDetails extends deals_DealDetail
{
    /**
     * Заглавие
     * 
     * @var string
     */
    public $title = 'Детайли на продажби';


    /**
     * Заглавие в единствено число
     *
     * @var string
     */
    public $singleTitle = 'Артикул';
    
    
    /**
     * Име на поле от модела, външен ключ към мастър записа
     */
    public $masterKey = 'saleId';
    
    
    /**
     * Плъгини за зареждане
     * 
     * var string|array
     */
    public $loadList = 'plg_RowTools, plg_Created, sales_Wrapper, plg_RowNumbering, plg_SaveAndNew,
                        plg_AlignDecimals2, doc_plg_HidePrices';
    
    
    /**
     * Активен таб на менюто
     * 
     * @var string
     */
    public $menuPage = 'Търговия:Продажби';
    
    
    /**
     * Кой има право да чете?
     * 
     * @var string|array
     */
    public $canRead = 'ceo, sales';
    
    
    /**
     * Кой има право да променя?
     * 
     * @var string|array
     */
    public $canEdit = 'ceo, sales';
    
    
    /**
     * Кой има право да добавя?
     * 
     * @var string|array
     */
    public $canAdd = 'ceo, sales';
    
    
    /**
     * Кой може да го види?
     * 
     * @var string|array
     */
    public $canView = 'ceo, sales';
    
    
    /**
     * Кой може да го изтрие?
     * 
     * @var string|array
     */
    public $canDelete = 'ceo, sales';
    
    
    /**
     * Брой записи на страница
     * 
     * @var integer
     */
    public $listItemsPerPage;
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = 'productId, packagingId, uomId, packQuantityDelivered, packQuantity, packPrice, discount, amount';
    
        
    /**
     * Полето в което автоматично се показват иконките за редакция и изтриване на реда от таблицата
     */
    public $rowToolsField = 'RowNumb';


    /**
     * Полета свързани с цени
     */
    public $priceFields = 'price,amount,discount,packPrice';
    
    
    /**
     * Какви мета данни да изискват продуктите, които да се показват
     */
    public $metaProducts = 'canSell';
    
    
    /**
     * Описание на модела (таблицата)
     */
    public function description()
    {
        $this->FLD('saleId', 'key(mvc=sales_Sales)', 'column=none,notNull,silent,hidden,mandatory');
        $this->FLD('classId', 'class(interface=cat_ProductAccRegIntf, select=title)', 'caption=Мениджър,silent,input=hidden');
        $this->FLD('productId', 'int', 'caption=Продукт,notNull,mandatory', 'tdClass=large-field leftCol');
        $this->FLD('uomId', 'key(mvc=cat_UoM, select=shortName)', 'caption=Мярка,input=none');
        $this->FLD('packagingId', 'key(mvc=cat_Packagings, select=name, allowEmpty)', 'caption=Мярка', 'tdClass=small-field');

        // Количество в основна мярка
        $this->FLD('quantity', 'double', 'caption=Количество,input=none');
        
        $this->FLD('quantityDelivered', 'double', 'caption=К-во->Доставено,input=none'); // Експедирано количество (в основна мярка)
        $this->FNC('packQuantityDelivered', 'double(minDecimals=0)', 'caption=Дост.,input=none'); // Експедирано количество (в брой опаковки)
        
        $this->FLD('quantityInvoiced', 'double', 'caption=К-во->Фактурирано,input=none'); // Фактурирано количество (в основна мярка)
        
        // Количество (в осн. мярка) в опаковката, зададена от 'packagingId'; Ако 'packagingId'
        // няма стойност, приема се за единица.
        $this->FLD('quantityInPack', 'double', 'input=none');
        
        // Цена за единица продукт в основна мярка
        $this->FLD('price', 'double', 'caption=Цена,input=none');
        
        // Брой опаковки (ако има packagingId) или к-во в основна мярка (ако няма packagingId)
        $this->FNC('packQuantity', 'double(Min=0)', 'caption=К-во,input=input,mandatory');
        $this->FNC('amount', 'double(minDecimals=2,maxDecimals=2)', 'caption=Сума');
        
        // Цена за опаковка (ако има packagingId) или за единица в основна мярка (ако няма packagingId)
        $this->FNC('packPrice', 'double(minDecimals=2)', 'caption=Цена,input');
        $this->FLD('discount', 'percent(min=-1,max=1)', 'caption=Отстъпка');
    }
}
