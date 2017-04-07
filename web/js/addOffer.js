/**
 * Created by paul on 06.04.17.
 */

function sendAddMaterialOfferRequest() {
    $.get('/ajax/addMaterialOffer',
        { offerName: $("#offerName").val(),offerDescription: $("#offerDescription").val(),offerURL: $("#offerURL").val(),offerPrice: $("#offerPrice").val()},
        function(response){
            if(response.code == 100 && response.success){
                var text = $("#form_offersIds").val();
                if(text == "") {
                    $("#form_offersIds").val(response.materialOfferId);
                }
                else {
                    $("#form_offersIds").val(text + " ; " + response.materialOfferId);
                }
            }

        }, "json");
}