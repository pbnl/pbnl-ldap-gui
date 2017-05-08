/**
 * Created by paul on 06.04.17.
 */

function sendAddMaterialOfferRequest(associatedMaterialPieceId) {
    $.get('/ajax/addMaterialOffer',
        { offerName: $("#offerName").val(),offerDescription: $("#offerDescription").val(),offerURL: $("#offerURL").val(),offerPrice: $("#offerPrice").val(),associatedMaterialPieceID: associatedMaterialPieceId},
        function(response){
            if(response.code == 100 && response.success){
                var text = $("#form_offersIds").val();
                if(text == "") {
                    $("#form_offersIds").val(response.materialOfferId);
                }
                else {
                    $("#form_offersIds").val(text + " ; " + response.materialOfferId);
                }

                var doc = document;

                var fragment = doc.createDocumentFragment();
                var tr = doc.createElement("tr");

                var name = doc.createElement("td");
                name.innerHTML = response.materialOfferName;
                tr.appendChild(name);

                var description = doc.createElement("td");
                description.innerHTML = response.materialOfferDescription;
                tr.appendChild(description);

                var url = doc.createElement("td");
                url.innerHTML = response.materialOfferURL;
                tr.appendChild(url);

                var price = doc.createElement("td");
                price.innerHTML = response.materialOfferPrice;
                tr.appendChild(price);

                fragment.appendChild(tr);
                $("#offerTable").append(tr);
            }

        }, "json");
}

function ajaxDelMaterialOffer(id,pieceId) {
    $.get('/ajax/delMaterialOffer',
        { id: id,pieceId: pieceId},
        function(response){
            if(response.code == 100 && response.success){
                var text = $("#form_offersIds").val();
                if(text.includes(" ; "+id)) text = text.replace(" ; "+id,"")
                else if(text.includes(id+" ; "))  text = text.replace(id+" ; ","")
                else if(text.includes(id)) text = "";
                $("#form_offersIds").val(text);

                $("#offerTablePiece"+id).remove();
            }

        }, "json");
}