webix.type(webix.ui.tree, {
    name: "menuTree",
    height: 40,
    icon: function(e) {
        for (var t = "", i = "", a = 1; a <= e.$level; a++)
            if (a == e.$level && e.$count) 
            {
                var n = e.open ? "down" : "right";
                t += "<span class='" + i + " webix_icon fa-angle-" + n + "'></span>"
            }
        return t
    },
    folder: function(e) {
        return e.icon ? "<span class='webix_icon icon fa-" + e.icon + "'></span>" : ""
    }
});

webix.protoUI({
    name: "icon",
    $skin: function() {
        this.defaults.height = webix.skin.$active.inputHeight
    },
    defaults: {
        template: function(e) {
            var t = "<button style='height:100%;width:100%;line-height:" + e.aheight + "px' class='webix_icon_button'>";
            return t += "<span class='webix_icon fa-" + e.icon + "'></span>", e.value && (t += "<span class='webix_icon_count'>" + e.value + "</span>"), t += "</button>"
        },
        width: 33
    },
    _set_inner_size: function() {}
}, webix.ui.button)