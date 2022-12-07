var header_role = function () {
    return {
        view: "scrollview",
        scroll: "native-y",
        id: 'header_role',
        body:
        {
            id: "role_id",
            type: "clean",
            rows:
                [
                    {
                        view: "form",
                        paddingY: 0,
                        id: "role_form1",
                        elements:
                            [
                                {
                                    cols:
                                        [
                                            {
                                                view: "fieldset", label: "Update Role (แก้ใขบทบาท)", body:
                                                {
                                                    rows:
                                                        [
                                                            {
                                                                view: "combo", id: "role_roleName", name: "role", label: "Role Name", labelPosition: "top", yCount: "10", suggest: "userM/role_getRole.php?type=1", on:
                                                                {
                                                                    onChange: function (value) {
                                                                        $.post("userM/role_getRole.php", { obj: value, type: 2 })
                                                                            .done(function (data) {
                                                                                var dataT1 = $$('role_dataT1');
                                                                                data = eval('(' + data + ')');
                                                                                if (data.ch == 1) {
                                                                                    dataT1.clearAll();
                                                                                    dataT1.parse(data.data);
                                                                                }
                                                                                else if (data.ch == 2) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); dataT1.clearAll(); }
                                                                                else if (data.ch == 9) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); }
                                                                                else if (data.ch == 10) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { window.open("login.php", "_self"); } }); }
                                                                            });
                                                                    }
                                                                }
                                                            },
                                                            {
                                                                cols:
                                                                    [

                                                                        {
                                                                            view: "button", value: "Save (บันทึกข้อมูล)", css: 'webix_primary', id: "role_save", on:
                                                                            {
                                                                                onItemClick: function (id, e) {
                                                                                    if ($$('role_dataT1').count() > 0) {
                                                                                        webix.confirm(
                                                                                            {
                                                                                                title: "<b>ข้อความจากระบบ</b>", ok: 'ใช่', cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>", callback: function (result) {
                                                                                                    if (result) {
                                                                                                        var dataT1 = $$('role_dataT1'), objAr = [];
                                                                                                        dataT1.eachRow(function (row) {

                                                                                                            tRow = dataT1.getItem(row);
                                                                                                            if (tRow.$parent == 0) {
                                                                                                                tRow = webix.copy(tRow);
                                                                                                                tRow.d2 = 1;
                                                                                                                tRow.d3 = 1;
                                                                                                                tRow.d4 = 1;
                                                                                                            }
                                                                                                            delete tRow.$parent;
                                                                                                            delete tRow.$level;
                                                                                                            delete tRow.$count;
                                                                                                            objAr[objAr.length] = tRow;
                                                                                                        });

                                                                                                        var btn = $$('role_save');
                                                                                                        btn.disable();
                                                                                                        $.post("userM/role_update.php", { obj: objAr, type: 1 })
                                                                                                            .done(function (data) {
                                                                                                                btn.enable();
                                                                                                                var data = eval('(' + data + ')');
                                                                                                                if (data.ch == 1) {
                                                                                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } });
                                                                                                                    $$('role_clear').callEvent("onItemClick", []);
                                                                                                                }
                                                                                                                else if (data.ch == 2) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); }
                                                                                                                else if (data.ch == 9) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); }
                                                                                                                else if (data.ch == 10) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { window.open("login.php", "_self"); } }); }
                                                                                                            });
                                                                                                    }
                                                                                                }
                                                                                            });
                                                                                    }
                                                                                    else webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: '<b>ไม่พบข้อมูลในตาราง</b>', callback: function () { } });
                                                                                }
                                                                            }
                                                                        },
                                                                        {
                                                                            view: "button", value: "Clear (ล้างข้อมูล)", type: "danger", id: "role_clear", on:
                                                                            {
                                                                                onItemClick: function (id, e) {
                                                                                    var dataT1 = $$('role_dataT1'), role = $$('role_roleName');
                                                                                    dataT1.clearAll();
                                                                                    role.blockEvent();
                                                                                    role.setValue('');
                                                                                    role.unblockEvent();
                                                                                    role.getPopup().getBody().load("userM/role_getRole.php?type=1");
                                                                                    $$('role_roleName2').setValue('');
                                                                                }
                                                                            }
                                                                        }
                                                                    ]
                                                            },
                                                            {
                                                                paddingX: 2,
                                                                rows:
                                                                    [
                                                                        {
                                                                            view: "treetable", id: "role_dataT1", navigation: true,
                                                                            autoheight: true, resizeColumn: true,
                                                                            autowidth: true, hover: "myhover", threeState: true,
                                                                            scrollAlignY: true,
                                                                            columns:
                                                                                [
                                                                                    { id: "value", header: "Menu Name", width: 250, template: "{common.treetable()} #value#" },
                                                                                    { id: "d1", header: "ดู", width: 50, template: "{common.checkbox()}", css: "role_checkBox1" },
                                                                                    { id: "d2", header: "เพิ่ม", width: 50, template: "{common.checkbox()}", css: "role_checkBox1" },
                                                                                    { id: "d3", header: "ลบ", width: 50, template: "{common.checkbox()}", css: "role_checkBox1" },
                                                                                    { id: "d4", header: "แก้ใข", width: 80, template: "{common.checkbox()}", css: "role_checkBox1" }
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
                                                                                        view: "pager", id: "role_pagerA",
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
                                                        ]
                                                }
                                            },
                                            {
                                                view: "fieldset", label: "Add Role (เพิ่มบทบาท)", css: 'webix_primary',width:200, body:
                                                {
                                                    rows:
                                                        [
                                                            { view: "text", id: "role_roleName2", name: "role2", label: "Role Name", labelPosition: "top"},
                                                            {
                                                                cols:
                                                                    [
                                                                        {
                                                                            view: "button", value: "Add (เพิ่มบทบาท)", type: "form", id: "role_save2", width:200, on:
                                                                            {
                                                                                onItemClick: function (id, e) {
                                                                                    if ($$('role_form1').validate()) {
                                                                                        webix.confirm(
                                                                                            {
                                                                                                title: "<b>ข้อความจากระบบ</b>", ok: 'ใช่', cancel: "ไม่", text: "คุณต้องเพิ่มกฏ<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>", callback: function (result) {
                                                                                                    if (result) {
                                                                                                        var btn = $$('role_save2');
                                                                                                        btn.disable();
                                                                                                        $.post("userM/role_insert.php", { obj: $$('role_roleName2').getValue(), type: 1 })
                                                                                                            .done(function (data) {
                                                                                                                btn.enable();
                                                                                                                var data = eval('(' + data + ')');
                                                                                                                if (data.ch == 1) {
                                                                                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } });
                                                                                                                    $$('role_clear').callEvent("onItemClick", []);
                                                                                                                }
                                                                                                                else if (data.ch == 2) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); }
                                                                                                                else if (data.ch == 9) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { } }); }
                                                                                                                else if (data.ch == 10) { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: data.data, callback: function () { window.open("login.php", "_self"); } }); }
                                                                                                            });
                                                                                                    }
                                                                                                }
                                                                                            });
                                                                                    }
                                                                                    else webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'กรุณาป้อนข้อมูล<font color="#ce5545"><b>ในช่องสีแดง</b></font>ให้ครบ', callback: function () { } });
                                                                                }
                                                                            }
                                                                        }, {}
                                                                    ]
                                                            }
                                                        ]
                                                }
                                            }, {}
                                        ]
                                },
                                {

                                }
                            ],
                        rules:
                        {
                            role2: webix.rules.isNotEmpty
                        }
                    }
                ], on:
            {
                onHide: function () {

                },
                onShow: function () {
                    setTimeout(function () { $$('role_roleName').focus(); }, 50);
                },
                onAddView: function () {

                    $$('role_dataT1').on_click.webix_table_checkbox = function (e, id, trg) {
                        var column = "d" + trg.parentNode.getAttribute("column"), nextId, isOpen;
                        if (this.getItem(id).$parent != 0) {
                            if (column == "d1") {
                                if (this.getItem(this.getItem(id).$parent).d1) {
                                    if (!this.getItem(id)[column]) {
                                        this.getItem(id).d1 = 1;
                                        this.getItem(id).d2 = 1;
                                        this.getItem(id).d3 = 1;
                                        this.getItem(id).d4 = 1;
                                    }
                                    else {
                                        this.getItem(id).d1 = 0;
                                        this.getItem(id).d2 = 0;
                                        this.getItem(id).d3 = 0;
                                        this.getItem(id).d4 = 0;
                                    }
                                }
                            }
                            else {
                                if (this.getItem(id).d1) {
                                    if (this.getItem(id)[column]) this.getItem(id)[column] = 0;
                                    else this.getItem(id)[column] = 1;
                                }
                            }
                        }
                        else if (column == "d1") {
                            if (this.getItem(id)[column]) this.getItem(id)[column] = 0;
                            else this.getItem(id)[column] = 1;

                            this.refresh(id);
                            isOpen = this.getItem(id)[column];
                            nextId = this.getItem(id).id;
                            for (var i = -1, len = this.getItem(id).$count; ++i < len;) {
                                nextId = this.getNextId(nextId, 1);
                                if (isOpen) {
                                    this.getItem(nextId).d1 = 1;
                                    this.getItem(nextId).d2 = 1;
                                    this.getItem(nextId).d3 = 1;
                                    this.getItem(nextId).d4 = 1;
                                }
                                else {
                                    this.getItem(nextId).d1 = 0;
                                    this.getItem(nextId).d2 = 0;
                                    this.getItem(nextId).d3 = 0;
                                    this.getItem(nextId).d4 = 0;
                                }
                                this.refresh(nextId);
                            }
                        }
                        this.refresh(id);
                        return false;
                    };
                }
            }
        }
    };
};