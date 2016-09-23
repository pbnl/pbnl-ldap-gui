/**
 * Created by paul on 20.09.16.
 */



$(document).ready(function(){
    $(addUserToTeamSearchButton).click(function () {
        $.get('/ajax/usersNotInGroup',
            {gid: getUrlVars()["gid"], searchedUserName: $(addUserToTeamSearch).val()},
            function(response){
                if(response.code == 100 && response.success){
                    $(form_givenName)
                        .find('option')
                        .remove()
                        .end()
                        for(i = 0; i <response.users.length;i++)
                        {
                            $(form_givenName).append(new Option(response.users[i],response.users[i]));
                        }
                }

            }, "json");
    });

});