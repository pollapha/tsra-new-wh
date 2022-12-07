var header_404 = function()
{
    return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_404",
        body: 
        {
            id:"404_id",
            type:"clean",
            rows:
            [
                {},
                {
                    view:"label",height:420,label: "<center><img class=\'photo\' src=\'images/404.png\' width=\'700\' height=\'420\' /></center>"
                },
                {}
            ],on:
            {
                onHide:function()
                {
                    
                },
                onShow:function()
                {

                },
                onAddView:function()
                {

                }
            }
        }
    };
};