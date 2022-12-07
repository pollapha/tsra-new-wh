var header_addMenu = function () {
    return {
        view: "scrollview",
        scroll: "native-y",
        id: 'header_addMenu',
        body:
        {
            id: "addMenu_id",
            type: "clean",
            rows:
            [
                {
                    view: "form",
                    paddingY: 0,
                    id: "addMenu_form1",
                    elements:
                    [
                        {
                            cols:
                            [
                                { view: "text", id: "addMenu_group", name: "menu_group", label: "Group", labelPosition: "top",
                                    on:
                                    {
                                        onKeyPress: function (code, e) 
                                        {
                                            setTimeout(function () 
                                            {
                                                var txt = $$(e.target).getValue(),obj=$$('addMenu_id');
                                                if(txt.length > 0) obj.setValue(txt);
                                                else obj.setValue('');
                                            },50)
                                            
                                        }
                                    }
                                },
                                {
                                    view: "combo", id: "addMenu_header", name: "menu_header", label: "Header", labelPosition: "top", value: '0', options: ['0', '1'],
                                    on:
                                    {
                                        onChange: function (v) {
                                            if (parseInt(v) == 1) _disable_(['addMenu_url']);
                                            else _enable_(['addMenu_url']);
                                        }
                                    }
                                },
                                { view: "text", id: "addMenu_id", name: "menu_menuId", label: "Menu Id", labelPosition: "top" },
                                { view: "text", id: "addMenu_name", name: "menu_menuName", label: "Menu Name", labelPosition: "top" },
                                {
                                    view: "text", id: "addMenu_use", name: "menu_menuUse", label: "Menu Object", labelPosition: "top",
                                    on:
                                    {
                                        onKeyPress: function (code, e) 
                                        {
                                            var obj=$$('addMenu_url'),dataT1 = $$('addMenu_dataT1');
                                            var rowObj ,menu='',menuID=parseInt($$('addMenu_group').getValue());
                                            dataT1.eachRow(function (row)
                                            {
                                                rowObj = dataT1.getItem(row);
                                                if(parseInt(rowObj.data1) == menuID && parseInt(rowObj.data2) == 1)
                                                    menu = rowObj.data5;
                                            });

                                            setTimeout(function () 
                                            {
                                                if(parseInt($$('addMenu_header').getValue()) == 1) return;
                                                var txt = $$(e.target).getValue();
                                                if(txt.length > 0) obj.setValue(menu+'/'+txt+'.js');
                                                else obj.setValue('');
                                            },50)
                                            
                                            
                                        }
                                    }
                                },
                                { view: "text", id: "addMenu_url", name: "menu_menuUrl", label: "Menu Url", labelPosition: "top" },
                                { view: "combo", id: "addMenu_for", name: "menu_for", label: "Menu For", labelPosition: "top", value: "ALL", options: ['ALL', 'ADMIN', 'SUPPORT'] }
                            ]
                        },
                        {
                            cols:
                            [

                                {
                                    view: "button", value: "Save (บันทึกข้อมูล)", css: "webix_primary", id: "addMenu_save", width: 160, on:
                                    {
                                        onItemClick: function (id, e) {
                                            if ($$('addMenu_form1').validate()) {
                                                webix.confirm(
                                                    {
                                                        title: "<b>ข้อความจากระบบ</b>", ok: 'ใช่', cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>", callback: function (result) {
                                                            if (result) {
                                                                var dataT1 = $$('addMenu_dataT1'), objAr = [];
                                                                var btn = $$('addMenu_save');
                                                                btn.disable();
                                                                $.post("admin/addMenu_insert.php", { obj: $$('addMenu_form1').getValues(), type: 1 })
                                                                    .done(function (data) {
                                                                        btn.enable();
                                                                        var data = eval('(' + data + ')');
                                                                        if (data.ch == 1) {
                                                                            /*webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});
                                                                            $$('addMenu_clear').callEvent("onItemClick", []);*/
                                                                            window.location.reload();
                                                                        }
                                                                        else if (data.ch == 2) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); }
                                                                        else if (data.ch == 9) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); }
                                                                        else if (data.ch == 10) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { window.open("login.php", "_self"); } }); }
                                                                    });
                                                                console.log($$('addMenu_form1').getValues());
                                                            }
                                                        }
                                                    });
                                            }
                                            else webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'กรุณาป้อนข้อมูล<font color="#ce5545"><b>ในช่องสีแดง</b></font>ให้ครบ', callback: function () { } });
                                        }
                                    }
                                },
                                {
                                    view: "button", value: "Clear (ล้างข้อมูล)", type: "danger", width: 150, id: "addMenu_clear", on:
                                    {
                                        onItemClick: function (id, e) {
                                            $$('menu_group').setValue('');
                                            $$('menu_header').setValue('1');
                                            $$('menu_menuId').setValue('');
                                            $$('menu_menuName').setValue('');
                                            $$('menu_menuUse').setValue('');
                                            $$('menu_for').setValue('ALL');
                                        }
                                    }
                                }, {},
                                {}, {}, {}
                            ]
                        }
                    ],
                    rules:
                    {
                        menu_group: webix.rules.isNotEmpty,
                        menu_header: webix.rules.isNotEmpty,
                        menu_menuId: webix.rules.isNotEmpty,
                        menu_menuName: webix.rules.isNotEmpty,
                        menu_menuUse: webix.rules.isNotEmpty,
                        menu_for: webix.rules.isNotEmpty
                    }
                },
                {
                    paddingX: 20,
                    rows:
                    [
                        {
                            view: "datatable", id: "addMenu_dataT1", navigation: true,
                            autoheight: true, resizeColumn: true, datatype: "jsarray",
                            hover: "myhover",
                            scrollAlignY: true,
                            columns:
                            [
                                { id: "data0", header: "id", width: 50 },
                                { id: "data1", header: "menu_group", width: 120 },
                                { id: "data2", header: "menu_header", width: 120 },
                                { id: "data3", header: "menu_menuId", width: 120 },
                                { id: "data4", header: "menu_menuName", width: 250 },
                                { id: "data5", header: "menu_menuUse", width: 120 },
                                { id: "data6", header: "menu_at", width: 180 },
                                { id: "data7", header: "For", width: 100 }
                            ],
                            on:
                            {
                                onItemClick: function (id) {

                                }
                            }
                        },
                        {
                            type: "wide",
                            cols:
                            [
                                {
                                    view: "pager", id: "addMenu_pagerA",
                                    template: function (data, common) {
                                        var start = data.page * data.size
                                            , end = start + data.size;
                                        if (data.count == 0) start = 0;
                                        else start += 1;
                                        if (end >= data.count) end = data.count;
                                        var html = "<b>showing " + (start) + " - " + end + " total " + data.count + " </b>";
                                        return common.first() + common.prev() + " " + html + " " + common.next() + common.last();
                                    },
                                    size: 10,
                                    group: 5
                                }
                            ]
                        }
                    ]
                }
            ], on:
            {
                onHide: function () {

                },
                onShow: function () {
                },
                onAddView: function () {
                    $.post("admin/addMenu_getData.php", { type: 1 })
                        .done(function (data) {
                            var dataT1 = $$('addMenu_dataT1');
                            data = eval('(' + data + ')');
                            if (data.ch == 1) {
                                dataT1.clearAll();
                                dataT1.parse(data.data, "jsarray");
                            }
                            else if (data.ch == 2) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); dataT1.clearAll(); }
                            else if (data.ch == 9) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); }
                            else if (data.ch == 10) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { window.open("login.php", "_self"); } }); }
                        });
                }
            }
        }
    };
};