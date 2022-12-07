var header_Receive = function () {
    var menuName = "Receive_", fd = "Receiving/" + menuName + "data.php";

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
        ajax(fd, {}, 4, function (json) {
            if (json.data.header.length > 0) {
                ele('create_grn').disable();
                ele('GRN_Number').disable();
                ele('DN_Number').disable();
                webix.UIManager.setFocus(ele('Part_No'));
                ele('form1').setValues(json.data.header[0]);
                ele('form2').setValues(json.data.header[0]);
                setTable('dataT1', json.data.body);
            }

        }, null,
            function (json) {
            }, btn);

    };


    var cells =
        [{
            header: "RECEIVE",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_Receive",
        body:
        {
            id: "Receive_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form", scroll: false, id: $n('form1'),
                        elements: [
                            {
                                rows: [
                                    {
                                        cols: [
                                            vw1("text", 'DN_Number', "DN Number", {
                                                required: true, suggest: fd + "?type=1", width: 200
                                            }),
                                            //vw1("datepicker", 'Receive_Date', "Receive Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 250 }),
                                            {
                                                rows: [
                                                    {},
                                                    vw1('button', 'create_grn', 'Create GRN', {
                                                        css: "webix_primary",
                                                        width: 100,
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
                                                                                    webix.UIManager.setFocus(ele('Part_Number'));
                                                                                }, null,
                                                                                    function (json) {
                                                                                    });
                                                                            }
                                                                            else {
                                                                                ele('create_grn').enable();
                                                                                //ele('DN_Number').enable();
                                                                                //ele('Receive_Date').enable();
                                                                            }
                                                                        }
                                                                    });
                                                            },
                                                        }
                                                    })
                                                ]
                                            },
                                            {},
                                        ],
                                    },
                                ]
                            },
                        ]
                    },
                    {
                        view: "form", scroll: false, id: $n('form2'),
                        on:
                        {

                            "onSubmit": function (view, e) {

                                if (view.config.name == 'Qty') {

                                    var obj1 = ele('form1').getValues();
                                    var obj2 = ele('form2').getValues();
                                    var obj4 = { ...obj1, ...obj2 };

                                    ajax(fd, obj4, 12, function (json) {
                                        loadData();
                                        ele('Qty').setValue('');
                                        ele('Part_No').setValue('');
                                        ele('Customer_Code').hide();
                                        webix.UIManager.setFocus(ele('Part_No'));

                                    }, null,
                                        function (json) {
                                            ele('Qty').setValue('');
                                            ele('Part_No').setValue('');
                                            ele('Customer_Code').hide();
                                            webix.UIManager.setFocus(ele('Part_No'));
                                        });
                                }

                                if (view.config.name == 'Part_No') {

                                    var obj1 = ele('form1').getValues();
                                    var obj2 = ele('form2').getValues();
                                    var obj4 = { ...obj1, ...obj2 };
                                    ajax(fd, obj4, 6, function (json) {
                                        var data = json.data
                                        console.log(data[0].Part_Type);
                                        if (data[0].Part_Type == 'Finish good') {
                                            ele('Customer_Code').show('');
                                            webix.UIManager.setFocus(ele('Customer_Code'));
                                        }
                                        else {
                                            ele('Customer_Code').hide();
                                            webix.UIManager.setFocus(ele('Qty'));
                                        }
                                    }, null,
                                        function (json) {
                                            ele('Customer_Code').hide();
                                            webix.UIManager.setFocus(ele('Part_No'));
                                        });

                                }

                                else if (webix.UIManager.getNext(view).config.type == 'line') {
                                    webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                                    //view.disable();
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
                                            vw1("text", 'GRN_Number', "GRN Number", { width: 200 }),
                                            vw1('richselect', 'Package_Type', 'Package Type', {
                                                required: false, width: 150,
                                                labelPosition: "top",
                                                value: '1', options: [
                                                    { id: '1', value: "--- Select ---" },
                                                    { id: 'Rack', value: "Rack" },
                                                    { id: 'Box', value: "Box" },
                                                    { id: 'Bag', value: "Bag" },
                                                ]
                                            }),
                                            vw1("text", 'Part_No', "Part Number", {
                                                width: 250,
                                            }),
                                            vw1("text", 'Customer_Code', "Customer", {
                                                required: true, suggest: fd + "?type=5",
                                                width: 200, hidden: 1,
                                            }),
                                            vw1("text", 'Qty', "Qty", { width: 150 }),
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
                                                                        var obj1 = ele('form1').getValues();
                                                                        var obj2 = ele('form2').getValues();
                                                                        var obj4 = { ...obj1, ...obj2 };
                                                                        webix.confirm(
                                                                            {
                                                                                title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                                callback: function (res) {
                                                                                    if (res) {
                                                                                        ajax(fd, obj4, 41, function (json) {
                                                                                            setTable('dataT1', json.data);
                                                                                            ele('create_grn').enable();
                                                                                            ele('GRN_Number').enable();
                                                                                            ele('DN_Number').enable();
                                                                                            ele('DN_Number').setValue('');
                                                                                            ele('Customer_Code').setValue('');
                                                                                            ele('GRN_Number').setValue('');
                                                                                            ele('Part_No').setValue('');
                                                                                            ele('Qty').setValue('');
                                                                                            ele('Package_Type').setValue('1');
                                                                                            ele('Customer_Code').hide();
                                                                                        }, null,
                                                                                            function (json) {
                                                                                                //ele('find').callEvent("onItemClick", []);
                                                                                            });
                                                                                    }
                                                                                }
                                                                            });
                                                                    }
                                                                }
                                                            }),
                                                        ]
                                                    },
                                                ]
                                            },
                                            {}
                                        ]
                                    },
                                ]
                            },
                        ]
                    },
                    {
                        cols: [
                            {
                                cols: [
                                    {},
                                    vw1('button', 'GenRack', 'Create Package Number', {
                                        css: 'webix_primary',
                                        width: 200,
                                        on: {
                                            onItemClick: function () {
                                                var obj1 = ele('form1').getValues();
                                                var obj2 = ele('form2').getValues();
                                                var obj4 = { ...obj1, ...obj2 };
                                                webix.confirm(
                                                    {
                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                        callback: function (res) {
                                                            if (res) {
                                                                ajax(fd, obj4, 14, function (json) {
                                                                    loadData();
                                                                }, null,
                                                                    function (json) {
                                                                        loadData();
                                                                    });
                                                            }
                                                        }
                                                    });
                                            }
                                        }
                                    }),
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
                                        id: $n("icon_cancel"), header: "&nbsp;", width: 40, template: function (row) {
                                            return "<span style='cursor:pointer' class='webix_icon wxi-trash'></span>";
                                        }
                                    },
                                    {
                                        id: "check_box", header: "",
                                        checkValue: "on", uncheckValue: "off", width: 40,
                                        template: function (row) {
                                            if (row.Serial_ID == null && row.Pick == 'Y') {
                                                return '<input class="webix_table_checkbox" type = "checkbox" checked> ';
                                            }
                                            else if (row.Serial_ID == null && row.Pick == '') {
                                                return '<input class="webix_table_checkbox" type = "checkbox"> ';
                                            }
                                            else if (row.Serial_ID != null) {
                                                return '';
                                            }
                                        },
                                    },
                                    { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                    { id: "Receiving_Pre_ID", header: ["Receiving_Pre_ID", { content: "textFilter" }], width: 130, hidden: 1 },
                                    { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                    { id: "WorkOrder", header: ["Work Order", { content: "textFilter" }], width: 150 },
                                    { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                    { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                                    { id: "Model", header: ["Model", { content: "textFilter" }], width: 100 },
                                    { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 150 },
                                    { id: "Package_Type", header: ["Package Type", { content: "textFilter" }], width: 120 },
                                    { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 80 },
                                    { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                ],
                                on: {
                                    onCheck: function (rowId, colId, state) {
                                        var row = this.getItem(rowId);
                                        var obj = row.Receiving_Pre_ID.concat("/", state);
                                        //console.log(obj);
                                        ajax(fd, obj, 13, function (json) {
                                            loadData();
                                        }, null,
                                            function (json) {
                                            });
                                    }
                                },
                                onClick:
                                {
                                    "wxi-trash": function (e, t) {
                                        var row = this.getItem(t), datatable = this;
                                        var obj = row.GRN_Number.concat("/", row.Receiving_Pre_ID);
                                        console.log('obj : ', obj);
                                        msBox('ลบ', function () {
                                            ajax(fd, obj, 31, function (json) {
                                                loadData();
                                            }, null,
                                                function (json) {
                                                });
                                        }, row);
                                    },
                                },
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