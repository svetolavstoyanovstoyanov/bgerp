var timeout;
var url;


function render_citysuggestions(data)
{
    $('#citySuggestions').remove();
    $('.cvcBillOfLading').append("<datalist id='citySuggestions'></datalist>");

    $places = data.cities;
    $("input[name=recipientPlace]").autocomplete({source: $places, autoFocus:true});
}


function enableApi(url) {

    /**
     * При спиране на писането в полето за търсене
     */
    $(document.body).on('keyup', "input[name=recipientPlace]", function(e){
        clearTimeout(timeout);

        // Правим Ajax заявката като изтече време за изчакване
        timeout = setTimeout(function(){
            var searchCityString = $("input[name=recipientPlace]").val();
            var countryId = $("select[name=recipientCountryId]").val();
            if(searchCityString.length >= 3){
                var resObj = new Object();
                resObj['url'] = url;

                var params = {string:searchCityString,countryId:countryId};
                getEfae().process(resObj, params);
            }
        }, 1000);
    });

}