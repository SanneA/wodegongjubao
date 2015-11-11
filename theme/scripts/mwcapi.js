
function genIn(params)
{
    if(params["element"] != undefined || params["noresponse"] == true )
    {
        if(params["address"] != undefined)
        {
            if(params["type"] != undefined)
            {
                if(params["type"].toUpperCase() !='POST')
                    params["type"] = 'GET';
                else
                    params["type"] = params["type"].toUpperCase();
            }
            else
                params["type"] = 'GET';

            if(params["dataType"] != undefined)
            {
                params["dataType"]='html';
            }


            if(params["loadicon"] != undefined)
            {
                $("#"+params["element"]).empty();
                $("#"+params["element"]).append(params["loadicon"]);
            }
            if( params["data"] != undefined)
                indata = params["data"];
            else
                var indata ="";

            if(params["before"] != undefined)
            {
                params["before"]();
            }

            $.ajax({
                url: params["address"],
                cache: false,
                type: params["type"],
                data: indata,
                dataType: params["dataType"],
                success: function(response)
                {
                    if(params["noresponse"] == undefined || params["noresponse"] == false)
                    {

                        $("#"+params["element"]).empty();

                        if(params["fade"] != undefined)
                            $("#"+params["element"]).append(response).fadeIn(params["fade"]);
                        else
                            $("#"+params["element"]).append(response);
                    }

                    if(params["callback"] != undefined)
                    {
                        params["callback"](response);
                    }
                },
                error:  function(){

                    if(params["errcallback"] != undefined)
                    {
                        params["errcallback"]();
                    }
                    else
                    {
                        if(params["noresponse"] == true)
                        {
                            alert("Error 404?");
                        }
                        else
                        {
                            $("#"+params["element"]).empty();
                            $("#"+params["element"]).append("ERROR 404.");
                        }
                    }
                }
            });

        }
        else
            console.error("-> function genIn, parameter 'address' is undefined, action aborted");
    }
    else
        console.error("-> function genIn, parameter 'element' is undefined, action aborted");

}
