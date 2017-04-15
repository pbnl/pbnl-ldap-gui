/**
 * Created by paul on 07.04.17.
 */

$(function(){
    $("#materialRequestsTable").tablesorter({
        theme : "bootstrap",

        headerTemplate : '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!

        widgets : [ "uitheme", "columns", "zebra" ],

        widgetOptions : {
            // using the default zebra striping class name, so it actually isn't included in the theme variable above
            // this is ONLY needed for bootstrap theming if you are using the filter widget, because rows are hidden
            zebra : ["even", "odd"],

            // class names added to columns when sorted
            columns: [ "primary", "secondary", "tertiary" ],

        }
    });
});