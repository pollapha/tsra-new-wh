var header_Pick = function () {
    var menuName = "Pick_", fd = "Shipping/" + menuName + "data.php";

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
        ajax(fd, {}, 1, function (json) {
            if (json.data.header.length > 0) {
                ele('create_ts').disable();
                ele('Pick_Date').disable();
                ele('TS_Number').disable();
                //ele('Serial_Number').disable();
                ele('form1').setValues(json.data.header[0]);
                ele('form2').setValues(json.data.header[0]);
                //ele('form3').setValues(json.data.header[0]);
                setTable('dataT1', json.data.body);
            }

        }, null,
            function (json) {
                //ele('find').callEvent("onItemClick", []);
            }, btn);

    };


    function loadData2(btn) {
        var obj1 = ele('form1').getValues();
        var obj2 = ele('form2').getValues();
        var obj3 = { ...obj1, ...obj2 };
        ajax(fd, obj3, 2, function (json) {
            setTable('dataRepack', json.data);
        }, null,
            function (json) {
                //ele('find').callEvent("onItemClick", []);
            }, btn);

    };


    function loadData3(btn) {
        var obj1 = ele('form1').getValues();
        var obj2 = ele('form2').getValues();
        var obj3 = { ...obj1, ...obj2 };
        ajax(fd, obj3, 2, function (json) {
            setTable('dataNotRepack', json.data);
        }, null,
            function (json) {
                //ele('find').callEvent("onItemClick", []);
            }, btn);

    };


    //pick end cap
    webix.ui(
        {
            view: "window", id: $n("win_pick_Repack"), modal: 1,
            head: "Pick", top: 50, position: "center", width: 800, height: 550,
            body:
            {
                rows: [
                    {
                        view: "treetable", id: $n("dataRepack"), navigation: true, select: "row", editaction: "custom",
                        resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                        threeState: true, rowLineHeight: 25, rowHeight: 25,
                        datatype: "json", headerRowHeight: 25, leftSplit: 2, editable: true,
                        columns: [
                            {
                                id: "check_box", header: "",
                                checkValue: "on", uncheckValue: "off", width: 40,
                                template: function (row) {
                                    if (row.Is_Header == "YES" && row.Pick == 'N') {
                                        return '<input class="webix_table_checkbox" type = "checkbox" checked> ';
                                    }
                                    else if (row.Is_Header == "YES" && row.Pick == '') {
                                        return '<input class="webix_table_checkbox" type = "checkbox"> ';
                                    }
                                    else {
                                        return '';
                                    }
                                },
                            },
                            { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                            {
                                id: "Serial_Number", header: ["Package ID", { content: "textFilter" }], editor: "", width: 180,
                                template: "{common.treetable()} #Serial_Number#"
                            },
                            { id: "Palletizing_Pre_ID", header: ["Palletizing_Pre_ID", { content: "textFilter" }], width: 130, hidden: 1 },
                            { id: "Confirm_DateTime", header: ["Confirm Date Time", { content: "textFilter" }], width: 180 },
                            { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 100 },
                            { id: "Total_Qty", header: ["Total", { content: "textFilter" }], width: 100 },
                            { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                            { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                            { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                            { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 60 },
                            { id: "Customer_Code", header: ["Ship To", { content: "textFilter" }], width: 100 },
                            { id: "Status_Working", header: ["Status", { content: "textFilter" }], width: 120 },
                        ],
                        on: {
                            onCheck: function (rowId, colId, state) {
                                var row = this.getItem(rowId);
                                var obj = row.Serial_Number.concat("/", state);
                                console.log(obj);
                                ajax(fd, obj, 15, function (json) {
                                    loadData2();

                                }, null,
                                    function (json) {
                                        loadData2();
                                    });
                            },
                            //onCheck:checkMaster
                        }
                    },
                    {
                        cols: [
                            {},
                            vw2('button', 'save_Repack', 'save', 'Save (บันทึก)', {
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
                                                            ele("win_pick_Repack").hide();
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
                            vw1('button', 'cancel_Repack', 'Cancel (ยกเลิก)', {
                                type: 'danger', width: 130,
                                on: {
                                    onItemClick: function () {
                                        ele('win_pick_Repack').hide();
                                    }
                                }
                            }),
                        ]
                    }
                ]
            }

        });


    //pick wheel lip
    webix.ui(
        {
            view: "window", id: $n("win_pick_NotRepack"), modal: 1,
            head: "Pick", top: 50, position: "center", width: 800, height: 550,
            body:
            {
                rows: [
                    {
                        view: "datatable", id: $n('dataNotRepack'), headerRowHeight: 20, rowLineHeight: 25, select: true,
                        rowHeight: 25, resizeColumn: true, css: { "font-size": "13px" },
                        editable: true,
                        columns: [
                            {
                                id: "check_box", header: "",
                                checkValue: "on", uncheckValue: "off", width: 40,
                                template: function (row) {
                                    if (row.Pick == 'Y') {
                                        return '<input class="webix_table_checkbox" type = "checkbox" checked> ';
                                    }
                                    else if (row.Pick == '') {
                                        return '<input class="webix_table_checkbox" type = "checkbox"> ';
                                    }
                                    else {
                                        return '';
                                    }
                                },
                            },
                            { id: "NO", header: "No.", css: "rank", width: 40, sort: "int" },
                            { id: "ID", header: ["ID", { content: "textFilter" }], width: 130, hidden: 1 },
                            { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                            { id: "WorkOrder", header: ["Work order", { content: "textFilter" }], width: 150 },
                            { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                            { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                            { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 150 },
                            { id: "Model", header: ["Model", { content: "textFilter" }], width: 100 },
                            { id: "Qty", header: ["Qty", { content: "textFilter" }], width: 80 },
                            //{ id: "Status_Working", header: ["Status", { content: "textFilter" }], width: 100 },
                            { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                        ],
                        on:
                        {
                            onCheck: function (rowId, colId, state) {
                                var row = this.getItem(rowId);
                                var obj = row.ID.concat("/", state);
                                console.log(obj);
                                ajax(fd, obj, 13, function (json) {
                                    loadData3();

                                }, null,
                                    function (json) {
                                        loadData3();
                                    });
                            },
                        },
                    },
                    {
                        cols: [
                            {},
                            vw2('button', 'save_NotRepack', 'save', 'Save (บันทึก)', {
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
                                                            ele("win_pick_NotRepack").hide();
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
                            vw1('button', 'cancel_NotRepack', 'Cancel (ยกเลิก)', {
                                type: 'danger', width: 130,
                                on: {
                                    onItemClick: function () {
                                        ele('win_pick_NotRepack').hide();
                                    }
                                }
                            }),
                        ]
                    }
                ]
            }

        });



    var cells =
        [{
            header: "PICK PART",
            body: {
                rows: [
                ]
            }
        }];



    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_Pick",
        body:
        {
            id: "Pick_id",
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
                                            vw1("datepicker", 'Pick_Date', "Pick Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 250, }),
                                            {
                                                rows: [
                                                    {},
                                                    vw1('button', 'create_ts', 'Create PS', {
                                                        type: 'form', css: 'webix_primary',
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
                                                                                }, null,
                                                                                    function (json) {

                                                                                    });
                                                                            }
                                                                            else {
                                                                                ele('create_ts').enable();
                                                                                ele('Pick_Date').enable();
                                                                                ele('TS_Number').enable();
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
                        view: "form", scroll: false, id: $n('form2'), on:

                        {

                            "onSubmit": function (view, e) {

                                if (view.config.name == 'Customer_Code') {
                                    var obj2 = ele('form2').getValues();

                                    var Customer = obj2.Customer_Code;
                                    console.log(Customer);
                                    if (Customer == 'TSPT4') {
                                        ele('type_pack').hide();
                                        webix.UIManager.setFocus(ele('PO_Number'));
                                    } else if (Customer == 'TSPT') {
                                        ele('type_pack').hide();
                                        webix.UIManager.setFocus(ele('PO_Number'));
                                    } else {
                                        ele('type_pack').show();
                                        ele('type_pack').setValue('Repack');
                                        webix.UIManager.setFocus(ele('type_pack'));
                                    }
                                }

                                else if (webix.UIManager.getNext(view).config.type == 'line') {

                                    webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                                    // view.disable();

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
                                            vw1("text", 'TS_Number', "PS Number", { width: 200 }),
                                            vw1("text", 'Customer_Code', "Ship To", {
                                                required: true, suggest: fd + "?type=6",
                                                width: 200,
                                            }),
                                            vw1('richselect', 'type_pack', 'Repack or Not', {
                                                required: false,
                                                width: 150,
                                                hidden: 1,
                                                labelPosition: "top",
                                                value: 'Repack', options: [
                                                    { id: 'Repack', value: "Repack" },
                                                    { id: 'Not Repack', value: "Not Repack" },
                                                ]
                                            }),
                                            vw1("text", 'PO_Number', "PO Number", { width: 200 }),
                                            {
                                                rows: [
                                                    {},
                                                    {
                                                        cols: [
                                                            vw1('button', 'find', 'Find (ค้นหา)', {
                                                                width: 120,
                                                                on: {
                                                                    onItemClick: function () {
                                                                        var obj1 = ele('form1').getValues();
                                                                        var obj2 = ele('form2').getValues();
                                                                        var obj3 = { ...obj1, ...obj2 };
                                                                        console.log(obj3)
                                                                        ajax(fd, obj3, 2, function (json) {
                                                                            var data = json.data;
                                                                            var Customer = data[0].Customer_Code;
                                                                            var Package_Type = data[0].Package_Type;
                                                                            console.log(Package_Type);
                                                                            var type_pack = obj2.type_pack;

                                                                            if (Customer == 'TSPT4') {
                                                                                ele('win_pick_NotRepack').show();
                                                                                setTable('dataNotRepack', json.data);
                                                                                ele("dataNotRepack").hideColumn("WorkOrder");
                                                                            }
                                                                            else if (Customer == 'TSPT') {
                                                                                ele('win_pick_NotRepack').show();
                                                                                setTable('dataNotRepack', json.data);

                                                                            }
                                                                            else {
                                                                                if (type_pack == 'Repack') {
                                                                                    ele('win_pick_Repack').show();
                                                                                    setTable('dataRepack', json.data);
                                                                                } else if (type_pack == 'Not Repack') {
                                                                                    ele('win_pick_NotRepack').show();
                                                                                    setTable('dataNotRepack', json.data);
                                                                                    ele("dataNotRepack").hideColumn("WorkOrder");
                                                                                }
                                                                            }
                                                                        }, null,
                                                                            function (json) {
                                                                            });

                                                                    }
                                                                }
                                                            }),
                                                            vw2('button', 'save_form2', 'save', 'Save (บันทึก)', {
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
                                                                                        ajax(fd, obj3, 41, function (json) {
                                                                                            ele('create_ts').enable();
                                                                                            ele('Pick_Date').enable();
                                                                                            ele('TS_Number').enable();

                                                                                            ele('Pick_Date').setValue(new Date());
                                                                                            ele('TS_Number').setValue('');
                                                                                            ele('Customer_Code').setValue('');
                                                                                            ele('type_pack').setValue('Repack');
                                                                                            ele('PO_Number').setValue('');

                                                                                            ele('dataT1').clearAll();
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
                                rows: [
                                    {
                                        view: "datatable", id: $n("dataT1"), navigation: true, select: "row", editaction: "custom",
                                        resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                        threeState: true, rowLineHeight: 25, rowHeight: 25,
                                        datatype: "json", headerRowHeight: 25, leftSplit: 2, editable: true,
                                        //height: 300,
                                        columns: [
                                            {
                                                id: $n("icon_cancel"), header: "&nbsp;", width: 40, template: function (row) {
                                                    return "<span style='cursor:pointer' class='webix_icon wxi-trash'></span>";
                                                }
                                            },
                                            { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                            { id: "Picking_Header_ID", header: ["Picking_Header_ID", { content: "textFilter" }], width: 160, hidden: 1 },
                                            { id: "Picking_Pre_ID", header: ["Picking_Pre_ID", { content: "textFilter" }], width: 160, hidden: 1 },
                                            { id: "Palletizing_Header_ID", header: ["Palletizing_Header_ID", { content: "textFilter" }], width: 160, hidden: 1 },
                                            { id: "Serial_Package", header: ["Package ID", { content: "textFilter" }], width: 130 },
                                            { id: "PO_Number", header: ["PO Number", { content: "textFilter" }], width: 130 },
                                            { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 220 },
                                            { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 350 },
                                            { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 100 },
                                            { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 60 },
                                            { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                        ],
                                        onClick:
                                        {
                                            "wxi-trash": function (e, t) {
                                                var row = this.getItem(t), datatable = this;
                                                var obj = row.Picking_Header_ID.concat("/", row.Picking_Pre_ID).concat("/", row.Palletizing_Header_ID);
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
                                        },
                                        on: {
                                            "onItemClick": function (id) {
                                                this.editRow(id);
                                            }
                                        }
                                    },

                                ]
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