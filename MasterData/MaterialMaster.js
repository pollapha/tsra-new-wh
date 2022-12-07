var header_MaterialMaster = function () {
    var menuName = "MaterialMaster_", fd = "MasterData/" + menuName + "data.php";

    function init() {

    };

    function ele(name) {
        return $$($n(name));
    };

    function $n(name) {
        return menuName + name;
    };

    function focus(name) {
        setTimeout(function () { ele(name).focus(); }, 100);
    };

    function setView(target, obj) {
        var key = Object.keys(obj);
        for (var i = 0, len = key.length; i < len; i++) {
            target[key[i]] = obj[key[i]];
        }
        return target;
    };

    function vw1(view, name, label, obj) {
        var v = { view: view, required: true, label: label, id: $n(name), name: name, labelPosition: "top" };
        return setView(v, obj);
    };

    function vw2(view, id, name, label, obj) {
        var v = { view: view, required: true, label: label, id: $n(id), name: name, labelPosition: "top" };
        return setView(v, obj);
    };

    function setTable(tableName, data) {
        if (!ele(tableName)) return;
        ele(tableName).clearAll();
        ele(tableName).parse(data);
        ele(tableName).filterByAll();
    };

    function loadData(btn) {
        ajax(fd, {}, 1, function (json) {
            setTable('dataT1', json.data);

        }, null,
            function (json) {
                //ele('find').callEvent("onItemClick", []);
            }, btn);

    };

    //add
    webix.ui(
        {
            view: "window", id: $n("win_add"), modal: 1,
            head: "Add (เพิ่มข้อมูล)", top: 50, position: "center",
            body:
            {
                view: "form", scroll: false, id: $n("win_add_form"), width: 600,
                elements:
                    [
                        {
                            cols:
                                [
                                    {
                                        rows:
                                            [
                                                {
                                                    cols: [
                                                        vw1('text', 'Part_No', 'Part Number', { labelPosition: "top" }),
                                                        vw1('text', 'Sub_Part_No', 'Sub of Part Number', { labelPosition: "top" }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'Used', 'Used', { labelPosition: "top", required: false }),
                                                        {}
                                                    ],
                                                },
                                            ]
                                    }
                                ]
                        },
                        {
                            cols:
                                [
                                    {},
                                    vw1('button', 'save', 'Save (บันทึก)', {
                                        css: "webix_primary", width: 120,
                                        on: {
                                            onItemClick: function () {
                                                webix.confirm(
                                                    {
                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                        callback: function (res) {
                                                            if (res) {
                                                                var obj = ele('win_add_form').getValues();
                                                                console.log(obj);
                                                                ajax(fd, obj, 11, function (json) {
                                                                    loadData();
                                                                    ele('win_add').hide();
                                                                    ele('Part_No').setValue('');
                                                                    ele('Sub_Part_No').setValue('');
                                                                    ele('Used').setValue('');
                                                                })
                                                            }
                                                        }
                                                    });
                                            }
                                        }
                                    }),
                                    vw1('button', 'cancel', 'Cancel (ยกเลิก)', {
                                        type: 'danger', width: 150,
                                        on: {
                                            onItemClick: function () {
                                                ele('win_add').hide();
                                                ele('Part_No').setValue('');
                                                ele('Sub_Part_No').setValue('');
                                                ele('Used').setValue('');
                                            }
                                        }
                                    }),
                                ]
                        }
                    ],
                rules:
                {
                }
            }
        });




    //edit
    webix.ui(
        {
            view: "window", id: $n("win_edit"), modal: 1,
            head: "Edit (แก้ไขข้อมูล)", top: 50, position: "center",
            body:
            {
                view: "form", scroll: false, id: $n("win_edit_form"), width: 600,
                elements:
                    [
                        {
                            cols:
                                [
                                    {
                                        rows:
                                            [
                                                {
                                                    cols: [
                                                        vw1('text', 'ID', 'Sub Mat ID', { labelPosition: "top", hidden: 1 }),
                                                        vw2('text', 'Part_No_edit', 'Part_No', 'Part Number', { labelPosition: "top", disabled: true }),
                                                        vw2('text', 'Sub_Part_No_edit', 'Sub_Part_No', 'Sub of Part Number', { labelPosition: "top", disabled: true }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'Used_edit', 'Used', 'Used', { labelPosition: "top", required: false }),
                                                        {}
                                                    ],
                                                },
                                            ]
                                    }
                                ]
                        },
                        {
                            cols:
                                [
                                    {},
                                    vw2('button', 'save_edit', 'save', 'Save (บันทึก)', {
                                        type: 'form', width: 120, css: "webix_primary",
                                        on: {
                                            onItemClick: function () {
                                                webix.confirm(
                                                    {
                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                        callback: function (res) {
                                                            if (res) {
                                                                var obj = ele('win_edit_form').getValues();
                                                                console.log(obj);
                                                                ajax(fd, obj, 21, function (json) {
                                                                    ele('win_edit').hide();
                                                                    loadData();
                                                                })
                                                            }
                                                        }
                                                    });
                                            }
                                        }
                                    }),

                                    vw1('button', 'cancel_edit', 'Cancel (ยกเลิก)', {
                                        type: 'danger', width: 150,
                                        on: {
                                            onItemClick: function () {
                                                ele('win_edit').hide();
                                            }
                                        }
                                    }),
                                ]
                        }
                    ],
                rules:
                {
                }
            }
        });

    var cells =
        [{
            header: "MATERIAL MASTER",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_MaterialMaster",
        body:
        {
            id: "MaterialMaster_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form", scroll: false, id: $n('form1'),
                        elements: [
                            {
                                cols:
                                    [
                                        vw1('button', 'add', 'Add (เพิ่มข้อมูล)', {
                                            width: 150, css: "webix_primary",
                                            on: {
                                                onItemClick: function () {
                                                    console.log(ele('win_add').show());
                                                }
                                            }
                                        }),
                                        {},
                                        vw1('button', 'find', 'Find (ค้นหา)', {
                                            width: 120,
                                            on: {
                                                onItemClick: function (id, e) {
                                                    console.log(ele("form1").getValues());
                                                    var obj = ele('form1').getValues();

                                                    ajax(fd, obj, 1, function (json) {
                                                        //webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'บันทึกสำเร็จ', callback: function () { } });
                                                        setTable('dataT1', json.data);
                                                    }, null,
                                                        function (json) {
                                                            /* ele('find').callEvent("onItemClick", []); */
                                                        });
                                                }
                                            }
                                        }),
                                    ]
                            },
                            {
                                cols: [
                                    {
                                        view: "treetable", id: $n("dataT1"), navigation: true, select: "row", editaction: "custom",
                                        resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                        threeState: true, rowLineHeight: 25, rowHeight: 25,
                                        datatype: "json", headerRowHeight: 25, leftSplit: 4, editable: true,
                                        scheme:
                                        {
                                            $change: function (obj) {
                                                var css = {};
                                                obj.$cellCss = css;
                                            }
                                        },
                                        columns: [
                                            {
                                                id: "icon_edit", header: "&nbsp;", width: 40, template: function (row) {
                                                    if (row.Is_Header == 'YES') {
                                                        return "";
                                                    }
                                                    else {
                                                        return "<span style='cursor:pointer' class='webix_icon wxi-pencil'></span>";
                                                    }
                                                }
                                            },
                                            // { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                            { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                                            { id: "ID", header: ["ID", { content: "textFilter" }], width: 230, hidden: 1 },
                                            {
                                                id: "Part_No", header: ["Part Number", { content: "textFilter" }], editor: "", width: 300,
                                                template: "{common.treetable()} #Part_No#"
                                            },
                                            { id: "Sub_Part_No", header: ["Sub Part Number", { content: "textFilter" }], width: 250 },
                                            { id: "Used", header: ["Use", { content: "textFilter" }], width: 80 },
                                            { id: "Creation_DateTime", header: ["Creation Date", { content: "textFilter" }], width: 150 },

                                        ],
                                        onClick:
                                        {
                                            "wxi-pencil": function (e, t) {
                                                console.log(ele('win_edit').show());
                                                var row = this.getItem(t);
                                                console.log(row);
                                                console.log(ele('win_edit_form').setValues(row));
                                            },
                                        },
                                        on: {
                                            // "onEditorChange": function (id, value) {
                                            // }
                                            "onItemClick": function (id) {
                                                this.editRow(id);
                                            }
                                        }
                                    },
                                ],
                            },
                        ]
                    },
                ], on:
            {
                onHide: function () {

                },
                onShow: function () {

                },
                onAddView: function () {
                    init();
                }
            }
        }
    };
};