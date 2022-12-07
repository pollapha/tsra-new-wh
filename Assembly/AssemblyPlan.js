var header_AssemblyPlan = function () {
    var menuName = "AssemblyPlan_", fd = "Assembly/" + menuName + "data.php";

    function init() {
        loadData();
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
        var obj = ele('form1').getValues();
        ajax(fd, obj, 1, function (json) {
            setTable('dataT1', json.data);

        }, null,
            function (json) {
            }, btn);

    };

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
                                                        vw1('text', 'Part_ID', 'Part ID.', { labelPosition: "top", hidden: 1 }),
                                                        vw2('text', 'Part_No_edit', 'Part_No', 'Part Number', { labelPosition: "top", width: 250, disabled: true }),
                                                        vw2('text', 'Part_Name_edit', 'Part_Name', 'Part Name', { labelPosition: "top", width: 300, disabled: true }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'Qty_edit', 'Qty', 'Qty', { labelPosition: "top", width: 250 }),
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
                                        css: "webix_primary", width: 120,
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
                                                                    setTable('dataT1', json.data);
                                                                    ele('win_edit').hide();
                                                                })
                                                            }
                                                        }
                                                    });
                                            }
                                        }
                                    }),

                                    vw1('button', 'cancel_edit', 'Cancel (ยกเลิก)', {
                                        type: 'danger', width: 130,
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
            },
        });

    var cells =
        [{
            header: "ASSEMBLY PLAN",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_AssemblyPlan",
        body:
        {
            id: "AssemblyPlan_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form", scroll: false, id: $n('form1'),
                        on:

                        {
                            "onSubmit": function (view, e) {
                                if (webix.UIManager.getNext(view).config.type == 'line') {
                                    webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                                }
                                else {
                                    webix.UIManager.setFocus(webix.UIManager.getNext(view));
                                }
                            },

                        },
                        elements: [
                            {
                                rows: [
                                    {
                                        cols: [
                                            vw1("datepicker", 'Assembly_Date', "Plan Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 200 }),
                                            vw1("text", 'Part_No', "Part Number", { width: 200, }),
                                            vw1("text", 'Qty', "Qty", { width: 200 }),
                                            {
                                                rows: [
                                                    {},
                                                    {
                                                        cols: [
                                                            vw1('button', 'save', 'Save (บันทึก)', {
                                                                css: "webix_primary",
                                                                width: 120,
                                                                on: {
                                                                    onItemClick: function () {
                                                                        var obj = ele('form1').getValues();
                                                                        webix.confirm(
                                                                            {
                                                                                title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                                callback: function (res) {
                                                                                    if (res) {
                                                                                        ajax(fd, obj, 11, function (json) {
                                                                                            loadData();
                                                                                            setTable('dataT1', json.data);
                                                                                            ele('Part_No').setValue('');
                                                                                            ele('Qty').setValue('');
                                                                                        }, null,
                                                                                            function (json) {
                                                                                            });
                                                                                    }
                                                                                }
                                                                            });
                                                                    }
                                                                }
                                                            }),
                                                            vw1('button', 'refresh', 'Find (ค้นหา)', {
                                                                width: 120,
                                                                on: {
                                                                    onItemClick: function (id, e) {
                                                                        console.log(ele("form1").getValues());
                                                                        var obj = ele('form1').getValues();

                                                                        ajax(fd, obj, 1, function (json) {
                                                                            loadData();
                                                                        }, null,
                                                                            function (json) {
                                                                            });
                                                                    }
                                                                }
                                                            }),
                                                        ]
                                                    },
                                                ]
                                            },
                                            {},
                                            {}
                                        ]
                                    },

                                ]
                            },
                        ]
                    },
                    {
                        padding: 3,
                        cols: [
                            {
                                view: "datatable", id: $n("dataT1"), navigation: true, select: "row", editaction: "custom",
                                resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                threeState: true, rowLineHeight: 25, rowHeight: 25,
                                datatype: "json", headerRowHeight: 25, leftSplit: 2, editable: true,
                                scheme:
                                {
                                    $change: function (obj) {
                                        var css = {};
                                        obj.$cellCss = css;
                                    }
                                },
                                columns: [

                                    {
                                        id: $n("icon_del"), header: "&nbsp;", width: 40, template: function (row) {
                                            if (row.Is_Header != "YES" && row.Status != 'COMPLETE' && row.Status != 'WORKING') {
                                                return "<span style='cursor:pointer' class='webix_icon wxi-trash'></span>";
                                            }
                                            else {
                                                return '';
                                            }
                                        }
                                    },
                                    {
                                        id: "icon_edit", header: "&nbsp;", width: 40, template: function (row) {
                                            if (row.Is_Header != "YES" && row.Status != 'COMPLETE') {
                                                return "<span style='cursor:pointer' class='webix_icon wxi-pencil'></span>";
                                            }
                                            else {
                                                return '';
                                            }
                                        }

                                    },
                                    { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                    { id: "Assembly_Date", header: ["Plan Date", { content: "textFilter" }], width: 150 },
                                    { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                    { id: "Side", header: ["", { content: "textFilter" }], width: 100 },
                                    { id: "Qty", header: ["Qty", { content: "textFilter" }], width: 80 },
                                ],
                                onClick:
                                {
                                    "wxi-pencil": function (e, t) {
                                        console.log(ele('win_edit').show());
                                        var row = this.getItem(t);
                                        console.log(ele('win_edit_form').setValues(row));
                                    },
                                    "wxi-trash": function (e, t) {
                                        var row = this.getItem(t), datatable = this;
                                        var obj = row.ID;
                                        console.log('obj : ', obj);
                                        msBox('ลบ', function () {
                                            ajax(fd, obj, 31, function (json) {
                                                loadData();
                                                webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ลบสำเร็จ', callback: function () { } });

                                            }, null,
                                                function (json) {
                                                });
                                        }, row);
                                    },
                                },
                                on: {
                                    "onItemClick": function (id) {
                                        this.editRow(id);
                                    }
                                }
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