function eshopActions() {
	
	// Изтрива ред от кошницата
	$(document.body).on("click", '.remove-from-cart', function(event){
		
		var url = $(this).attr("data-url");
	    if(!url) return;
	    
	    var cartId = $(this).attr("data-cart");
	    var data = {cartId:cartId};
	   
	    resObj = new Object();
		resObj['url'] = url;
		
		getEfae().process(resObj, data);
	});
	
	$(document.body).on("click", '.cart-add-product-btn', function(event){
		
		var url = $(this).attr("data-url");
	    if(!url) return;
	    
	    var eshopProductId = $(this).attr("data-eshopproductpd");
	    var productId = $(this).attr("data-productid");
	    var packQuantity = $("input[name=product" + productId + "]").val();
	    
	    if(!packQuantity){
	    	packQuantity = $("input[name=product" + productId + "]").attr("data-quantity");
	    }
	    
	    var data = {eshopProductId:eshopProductId,productId:productId,packQuantity:packQuantity};
	    
	    resObj = new Object();
		resObj['url'] = url;
		getEfae().process(resObj, data);
	});
	
	// Използване на числата за въвеждане на суми за плащания
	$(document.body).on('keyup', ".option-quantity-input", function(e){
		
		//this.value = this.value.replace(/[^0-9\.]/g,'');
		$(this).removeClass('cart-input-error');
		var packQuantity = $(this).val();
		if(!$.isNumeric(packQuantity)){
			$(this).addClass('cart-input-error');
		} else {
			var url = $(this).attr("data-url");
		    if(!url) return;
		    var data = {packQuantity:packQuantity};
		    
		    
		    resObj = new Object();
			resObj['url'] = url;
			getEfae().process(resObj, data);
		}
	});
	
	
	// Използване на числата за въвеждане на суми за плащания
	$(document.body).on('keyup', ".eshop-product-option", function(e){
		$(this).removeClass('cart-input-error');
		
		var packQuantity = $(this).val();
		if(!$.isNumeric(packQuantity)){
			$(this).addClass('cart-input-error');
		}
	});
};