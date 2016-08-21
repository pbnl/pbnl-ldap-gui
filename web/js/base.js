/**
 * Created by paul on 20.08.16.
 */
function goBack() {
    window.history.back();
}

$(document).ready(function(){

    $( form_generatePassword ).click(function() {
        password = generatePassword(10);
        $("#form_clearPassword").val(password);
        $("#form_generatedPassword").val(password);
    });



});
