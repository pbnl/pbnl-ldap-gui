/**
 * Created by paul on 12.04.17.
 */
$(document).ready(function() {
    $(".readonly").on('keydown paste', function (e) {
        e.preventDefault();
    });
});