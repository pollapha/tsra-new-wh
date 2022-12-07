var header_ScanWorkOrder = function () {
    var menuName = "ScanWorkOrder_", fd = "Assembly/" + menuName + "data.php";

    function init() {
        loadData();

        webix.event(ele("WorkOrder").getInputNode(), "paste", (e) => {
            e.preventDefault();
            let data = e.clipboardData.getData('text');
            if (data.indexOf(";") !== -1) {
                e.target.value += data.replace(/;/g, '');
            }
        });
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
            if (json.data.header.length > 0) {
                webix.UIManager.setFocus(ele('WorkOrder'));
                ele('form1').setValues(json.data.header[0]);
                ele('form2').setValues(json.data.header[0]);
                setTable('dataT1', json.data.body);
            }
            if (json.data.header.length == 0) {
                webix.UIManager.setFocus(ele('WorkOrder'));
                ele('dataT1').clearAll();
            }

        }, null,
            function (json) {
            }, btn);

    };

    var cells =
        [{
            header: "SCAN WORK ORDER",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ScanWorkOrder",
        body:
        {
            id: "ScanWorkOrder_id",
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
                                            vw1("datepicker", 'Assembly_Date', "Assembly Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 250 }),
                                            {
                                                rows: [
                                                    {},
                                                    {
                                                        rows: [
                                                            {},
                                                            vw1('button', 'refresh', 'Find (ค้นหา)', {
                                                                width: 120,
                                                                on: {
                                                                    onItemClick: function () {
                                                                        loadData();
                                                                    }
                                                                }
                                                            }),
                                                        ]
                                                    },
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
                        view: "form", scroll: false, id: $n('form2'), on:
                        {
                            "onSubmit": function (view, e) {

                                if (view.config.name == 'WorkOrder') {

                                    var obj1 = ele('form1').getValues();
                                    var obj2 = ele('form2').getValues();
                                    var obj3 = { ...obj1, ...obj2 };

                                    ajax(fd, obj3, 11, function (json) {
                                        var WorkOrder = ele('WorkOrder').getValue();
                                        var Date_Label = ele('Assembly_Date').getValue();
                                        var format = webix.Date.dateToStr("%Y-%m-%d");
                                        var string = format(new Date(Date_Label));
                                        var obj = {};
                                        obj.printerName = 'LABEL';
                                        obj.copy = 1;
                                        console.log(obj.WorkOrder = WorkOrder);
                                        console.log(obj.Date = string);
                                        obj.printType = 'F';
                                        obj.warter = 'NO';
                                        var temp = window.open("print/doc/label.php?printerName=" + obj.printerName + "&copy=" + obj.copy + "&WorkOrder=" + obj.WorkOrder + "&Date=" + obj.Date + "&printType=" + obj.printType + "&warter=" + obj.warter);
                                        //temp.addEventListener('load', function () { temp.close(); }, false);
                                        loadData();
                                        ele('WorkOrder').setValue('');
                                        ele('Location_Code').setValue('');
                                        webix.UIManager.setFocus(ele('WorkOrder'));
                                    }, null,
                                        function (json) {
                                            ele('WorkOrder').setValue('');
                                            ele('Location_Code').setValue('');
                                            webix.UIManager.setFocus(ele('WorkOrder'));
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
                                            vw1("text", 'Location_Code', "Line", { width: 250, hidden: 1 }),
                                            vw1("text", 'Assembly_Pre_ID', "Assembly_Pre_ID", { width: 250, hidden: 1 }),
                                            vw1("text", 'WorkOrder', "Work order", {
                                                width: 250,
                                                on: {
                                                    onKeyPress: function (code, e) {
                                                        if (e.key == ';')
                                                            return false;
                                                    },
                                                }
                                            }),
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
                                                                        var obj3 = { ...obj1, ...obj2 };
                                                                        webix.confirm(
                                                                            {
                                                                                title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                                callback: function (res) {
                                                                                    if (res) {
                                                                                        ajax(fd, obj3, 41, function (json) {
                                                                                            loadData();
                                                                                            ele('Assembly_Date').enable();
                                                                                            ele('Assembly_Date').setValue(new Date());
                                                                                            ele('WorkOrder').setValue('');
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
                                    { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                    { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                    { id: "WorkOrder", header: ["Work Order", { content: "textFilter" }], width: 150 },
                                    { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                    { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 280 },
                                    { id: "Model", header: ["Model", { content: "textFilter" }], width: 100 },
                                    { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 80 },
                                ],
                                onClick:
                                {
                                    "wxi-trash": function (e, t) {
                                        var row = this.getItem(t), datatable = this;
                                        var obj = row.GRN_Number.concat("/", row.FG_Serial_Number);
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