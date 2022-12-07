webix.protoUI(
{
    name:"userIcon",
    $init:function(config)
    {
        config.url = config.url == undefined ? 'images/user/icon_index.jpg': 'images/user/icon_index.jpg';
        this.$view.className = "webix_view webix_control webix_el_label";
        this.$view.innerHTML = '<div class="webix_view header_person" view_id="person_template" style="display: inline-block; vertical-align: top; margin-left: 4px; width: 180px;"><div class=" webix_template"><div style="height:100%;width:100%;" onclick="webix.$$(&quot;profilePopup&quot;).show(this)"><img class="photo" src="'+config.url+'"><span class="name">Oliver Parr</span><span class="webix_icon fa-angle-down"></span></div></div></div>';             
    }
}, webix.ui.view);