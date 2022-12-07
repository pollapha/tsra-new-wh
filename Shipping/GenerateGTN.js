var header_GenerateGTN = function () {
    var menuName = "GenerateGTN_", fd = "Shipping/" + menuName + "data.php";

    function init() {
        loadData();
        loadData3();
        // webix.event(ele("TS_Number").getInputNode(), "paste", (e) => {
        //     e.preventDefault();
        //     let data = e.clipboardData.getData('text');
        //     if (data.indexOf(";") !== -1) {
        //         e.target.value += data.replace(/;/g, '');
        //     }
        // });
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


    function loadData2(btn) {
        ajax(fd, {}, 2, function (json) {
            if (json.data.header.length > 0) {
                setTable('dataT2', json.data.body);
            }
        }, null,
            function (json) {
            }, btn);
    };

    function loadData(btn) {
        ajax(fd, {}, 1, function (json) {
            if (json.data.header.length > 0) {
                ele('create_gdn').disable();
                ele('Ship_Date').disable();
                ele('Ship_Time').disable();
                ele('GTN_Number').disable();
                ele('Invoice_Number').disable();
                ele('Ship_Time').show();
                ele('GTN_Number').show();
                ele('form1').setValues(json.data.header[0]);
                ele('form2').setValues(json.data.header[0]);
                setTable('dataT1', json.data.body);
            }
        }, null,
            function (json) {
            }, btn);
    };

    function loadData3(btn) {
        ajax(fd, {}, 3, function (json) {
            if (json.data.header.length > 0) {
                ele('Truck_ID').disable();
                ele('Truck_Driver').disable();
                ele('Truck_Type').disable();
                ele('Freight').disable();
                ele('save_1').disable();
                ele('form2').setValues(json.data.header[0]);
            }
        }, null,
            function (json) {
            }, btn);

    };

    function loadData8(btn) {
        var obj1 = ele('form1').getValues();
        var obj2 = ele('form2').getValues();
        var obj3 = ele('form3').getValues();
        var obj4 = { ...obj1, ...obj2, ...obj3 };
        ajax(fd, obj4, 8, function (json) {
            setTable('dataT2', json.data);
        }, null,
            function (json) {
            }, btn);
    };

    //pick
    webix.ui(
        {
            view: "window", id: $n("win_pick"), modal: 1,
            head: "Pick", top: 50, position: "center", width: 1000, height: 500,
            body:
            {
                rows: [
                    {
                        view: "treetable", id: $n('dataT2'), headerRowHeight: 20, rowLineHeight: 25, rowHeight: 25, resizeColumn: true, css: { "font-size": "13px" },
                        editable: true,
                        columns: [
                            {
                                id: "check_box", header: "",
                                checkValue: "on", uncheckValue: "off", width: 40,
                                template: function (row) {
                                    if (row.Is_Header == 'YES' && row.Pick == 'Y') {
                                        return '<input class="webix_table_checkbox" type = "checkbox" checked> ';
                                    }
                                    else if (row.Is_Header == 'YES' && row.Pick == '') {
                                        return '<input class="webix_table_checkbox" type = "checkbox"> ';
                                    }
                                    else {
                                        return '';
                                    }
                                },
                            },
                            { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                            {
                                id: "TS_Number", header: ["PS Number", { content: "textFilter" }], editor: "", width: 180,
                                template: "{common.treetable()} #TS_Number#"
                            },
                            { id: "Picking_Pre_ID", header: ["Picking_Pre_ID", { content: "textFilter" }], width: 130, hidden: 1 },
                            { id: "Pick_Date", header: ["Pick Date", { content: "textFilter" }], width: 100 },
                            { id: "Status_Picking", header: ["Status", { content: "textFilter" }], width: 100 },
                            { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                            { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 100 },
                            { id: "Serial_Package", header: ["Package ID", { content: "textFilter" }], width: 150 },
                            { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                            { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                            { id: "PO_Number", header: ["PO Number", { content: "textFilter" }], width: 100 },
                            { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 80 },
                            { id: "Total_Qty", header: ["Total", { content: "textFilter" }], width: 80 },
                            { id: "Confirm_Picking_DateTime", header: ["Confirm Picking DateTime", { content: "textFilter" }], width: 200 },
                        ],
                        on: {
                            onCheck: function (rowId, colId, state) {
                                var row = this.getItem(rowId);
                                var obj = row.TS_Number.concat("/", state);
                                console.log(obj);
                                ajax(fd, obj, 14, function (json) {
                                    loadData8();

                                }, null,
                                    function (json) {
                                    });
                            }
                        }
                    },
                    {
                        cols: [
                            {},
                            vw2('button', 'save_2', 'save', 'Save (บันทึก)', {
                                css: 'webix_primary',
                                width: 120,
                                on: {
                                    onItemClick: function () {
                                        var obj1 = ele('form1').getValues();
                                        var obj2 = ele('form2').getValues();
                                        var obj3 = { ...obj1, ...obj2 };
                                        webix.confirm(
                                            {
                                                title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                callback: function (res) {
                                                    if (res) {
                                                        ajax(fd, obj3, 13, function (json) {
                                                            loadData();
                                                            //loadData2();
                                                            loadData3();
                                                            ele('win_pick').hide();
                                                        }, null,
                                                            function (json) {
                                                                loadData();
                                                                //loadData2();
                                                                loadData3();
                                                            });
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
                                        ele('win_pick').hide();
                                    }
                                }
                            }),
                        ]
                    },

                ]
            }

        });

    var cells =
        [{
            header: "GENERATE GTN",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_GenerateGTN",
        body:
        {
            id: "GenerateGTN_id",
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
                                            vw1("datepicker", 'Ship_Date', "Ship Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 150 }),
                                            vw1("text", 'Ship_Time', "Ship Time", { width: 150, hidden: 1 }),
                                            vw1("text", 'GTN_Number', "GTN Number", { width: 150, hidden: 1 }),
                                            {
                                                rows: [
                                                    {},
                                                    vw1('button', 'create_gdn', 'Create GTN', {
                                                        css: 'webix_primary',
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
                                                                                    ele('Ship_Time').show();
                                                                                    ele('GTN_Number').show();
                                                                                }, null,
                                                                                    function (json) {
                                                                                        ele('Ship_Time').hide();
                                                                                        ele('GTN_Number').hide();
                                                                                    });
                                                                            }
                                                                            else {
                                                                                ele('create_gdn').enable();
                                                                                ele('Ship_Date').enable();
                                                                                ele('GTN_Number').enable();
                                                                                ele('Invoice_Number').enable();
                                                                                ele('Ship_Time').hide();
                                                                                ele('GTN_Number').hide();
                                                                            }
                                                                        }
                                                                    });
                                                            },
                                                        }
                                                    })
                                                ]
                                            },
                                            {}
                                        ],
                                    },

                                ]
                            },
                        ]
                    },
                    {
                        view: "form", scroll: false, id: $n('form2'),
                        elements: [
                            {
                                rows: [
                                    {
                                        cols: [
                                            vw1("text", 'Invoice_Number', "Invoice Number", { width: 150 }),
                                            vw1("text", 'Truck_ID', "Truck ID", { width: 150 }),
                                            vw1("text", 'Truck_Driver', "Truck Driver", { width: 150 }),
                                            vw1("text", 'Truck_Type', "Truck Type", { width: 150 }),
                                            vw1("text", 'Freight', "Freight", { width: 150, hidden: 1 }),
                                            {
                                                rows: [
                                                    {},
                                                    {
                                                        cols: [
                                                            vw2('button', 'save_1', 'save', 'Save (บันทึก)', {
                                                                css: 'webix_primary',
                                                                width: 120,
                                                                on: {
                                                                    onItemClick: function () {
                                                                        var obj1 = ele('form1').getValues();
                                                                        var obj2 = ele('form2').getValues();
                                                                        var obj3 = { ...obj1, ...obj2 };
                                                                        webix.confirm(
                                                                            {
                                                                                title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                                callback: function (res) {
                                                                                    if (res) {
                                                                                        ajax(fd, obj3, 12, function (json) {
                                                                                            loadData();
                                                                                            loadData3();
                                                                                            webix.UIManager.setFocus(ele('TS_Number'));
                                                                                        }, null,
                                                                                            function (json) {
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
                        view: "form", scroll: false, id: $n('form3'),
                        on:
                        {

                            "onSubmit": function (view, e) {

                                if (view.config.name == 'TS_Number') {

                                    var obj1 = ele('form1').getValues();
                                    var obj2 = ele('form2').getValues();
                                    var obj3 = ele('form3').getValues();
                                    var obj4 = { ...obj1, ...obj2, ...obj3 };

                                    ajax(fd, obj4, 15, function (json) {
                                        loadData();
                                        ele('TS_Number').setValue('');
                                        webix.UIManager.setFocus(ele('TS_Number'));
                                    }, null,

                                        function (json) {
                                            ele('TS_Number').setValue('');
                                            webix.UIManager.setFocus(ele('TS_Number'));
                                        });


                                }

                                else if (webix.UIManager.getNext(view).config.type == 'line') {

                                    webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                                    view.disable();

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
                                            vw1("text", 'Ship_To', "Ship To", {
                                                required: true, suggest: fd + "?type=5",
                                                width: 120,
                                                hidden: 1
                                            }),
                                            vw1("text", 'TS_Number', "PS Number", {
                                                width: 150,
                                                on: {
                                                    onKeyPress: function (code, e) {
                                                        if (e.key == ';')
                                                            return false;
                                                    },
                                                }
                                            }),
                                            vw1("text", 'Location_Code', "Truck Sim", {
                                                required: true, suggest: fd + "?type=6",
                                                width: 150,
                                            }),
                                            {
                                                rows: [
                                                    {},
                                                    {
                                                        cols: [
                                                            vw2('button', 'save_3', 'save', 'Save (บันทึก)', {
                                                                css: 'webix_primary',
                                                                width: 120,
                                                                on: {
                                                                    onItemClick: function () {
                                                                        var obj1 = ele('form1').getValues();
                                                                        var obj2 = ele('form2').getValues();
                                                                        var obj3 = ele('form3').getValues();
                                                                        var obj4 = { ...obj1, ...obj2, ...obj3 };
                                                                        console.log(obj4);
                                                                        webix.confirm(
                                                                            {
                                                                                title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                                callback: function (res) {
                                                                                    if (res) {
                                                                                        ajax(fd, obj4, 41, function (json) {
                                                                                            ele('create_gdn').enable();
                                                                                            ele('Ship_Date').enable();
                                                                                            ele('GTN_Number').enable();
                                                                                            ele('Ship_Date').enable();
                                                                                            ele('Ship_Time').enable();
                                                                                            ele('Invoice_Number').enable();
                                                                                            ele('Ship_To').enable();
                                                                                            ele('Truck_ID').enable();
                                                                                            ele('Truck_Driver').enable();
                                                                                            ele('Truck_Type').enable();
                                                                                            ele('Freight').enable();
                                                                                            ele('save_1').enable();
                                                                                            ele('Location_Code').enable();
                                                                                            ele('Ship_Date').setValue(new Date());
                                                                                            ele('GTN_Number').setValue('');
                                                                                            ele('Ship_Time').setValue('');
                                                                                            ele('Invoice_Number').setValue('');
                                                                                            ele('Ship_To').setValue('');
                                                                                            ele('Truck_ID').setValue('');
                                                                                            ele('Truck_Driver').setValue('');
                                                                                            ele('Truck_Type').setValue('');
                                                                                            ele('Freight').setValue('');
                                                                                            ele('Location_Code').setValue('');
                                                                                            ele('dataT1').clearAll();
                                                                                            ele('dataT2').clearAll();
                                                                                            ele('Ship_Time').hide();
                                                                                            ele('GTN_Number').hide();
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
                        padding: 3,
                        cols: [
                            {
                                view: "treetable", id: $n('dataT1'), headerRowHeight: 20, rowLineHeight: 25, select: true,
                                rowHeight: 25, resizeColumn: true, css: { "font-size": "13px" },
                                editable: true,
                                columns: [
                                    {
                                        id: $n("icon_cancel"), header: "&nbsp;", width: 40, template: function (row) {
                                            if (row.Is_Header != "YES") {
                                                return "";
                                            }
                                            else {
                                                return "<span style='cursor:pointer' class='webix_icon wxi-trash'></span>";
                                            }
                                        }
                                    },
                                    { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                                    {
                                        id: "TS_Number", header: ["TS Number", { content: "textFilter" }], editor: "", width: 180,
                                        template: "{common.treetable()} #TS_Number#"
                                    },
                                    { id: "Shipping_Header_ID", header: ["Shipping_Header_ID", { content: "textFilter" }], width: 100, hidden: 1 },
                                    { id: "Shipping_Pre_ID", header: ["Shipping_Pre_ID", { content: "textFilter" }], width: 100, hidden: 1 },
                                    { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                    { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 150 },
                                    { id: "Serial_Package", header: ["Package ID", { content: "textFilter" }], width: 150 },
                                    { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                    { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                                    { id: "Model", header: ["Model", { content: "textFilter" }], width: 100 },
                                    { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 80 },
                                    { id: "Total_Qty", header: ["Total", { content: "textFilter" }], width: 80 },
                                    { id: "Pick", header: ["Pick", { content: "textFilter" }], width: 80, hidden: 1 },
                                ],
                                onClick: {
                                    "wxi-trash": function (e, t) {
                                        var row = this.getItem(t), datatable = this;
                                        var obj = row.Shipping_Header_ID.concat("/", row.Shipping_Pre_ID);
                                        console.log('obj : ', obj);
                                        msBox('ลบ', function () {
                                            ajax(fd, obj, 31, function (json) {
                                                loadData();

                                            }, null,
                                                function (json) {
                                                    loadData();
                                                });
                                        }, row);
                                    },
                                }
                            }
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