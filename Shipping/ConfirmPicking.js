var header_ConfirmPicking = function () {
    var menuName = "ConfirmPicking_", fd = "Shipping/" + menuName + "data.php";

    function init() {
        loadData();
        webix.event(ele("Serial_Package").getInputNode(), "paste", (e) => {
            e.preventDefault();
            let data = e.clipboardData.getData('text');
            if (data.indexOf(";") !== -1) {
                e.target.value += data.replace(/;/g, '');
            }
        });
        webix.event(ele("Part_No").getInputNode(), "paste", (e) => {
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
                ele('TS_Number').enable();
                webix.UIManager.setFocus(ele('TS_Number'));
                ele('form1').setValues(json.data.header[0]);
            }
        }, btn);
    };

    function loadData1(btn) {
        var obj = ele('form1').getValues();
        ajax(fd, obj, 3, function (json) {
            setTable('dataT1', json.data);
        }, null,

            function (json) {
                ele('TS_Number').enable();
                ele('TS_Number').setValue('');
                ele('Part_No').hide();
                webix.UIManager.setFocus(ele('TS_Number'));
            });
    };

    var cells =
        [{
            header: "CONFIRM PICKING",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ConfirmPicking",
        body:
        {
            id: "ConfirmPicking_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form", scroll: false, id: $n('form1'),
                        on:

                        {

                            "onSubmit": function (view, e) {
                                if (view.config.name == 'TS_Number') {
                                    var obj = ele('form1').getValues();
                                    console.log(obj);
                                    loadData1();
                                    ele('TS_Number').disable();
                                    webix.UIManager.setFocus(ele('Serial_Package'));
                                }

                                else if (view.config.name == 'Serial_Package') {
                                    var obj = ele('form1').getValues();
                                    ajax(fd, obj, 2, function (json) {
                                        webix.UIManager.setFocus(ele('Part_No'));
                                    }, null,
                                        function (json) {
                                            console.log(4);
                                            ele('Serial_Package').setValue('');
                                            ele('Part_No').setValue('');
                                            webix.UIManager.setFocus(ele('Serial_Package'));
                                        });
                                }

                                else if (view.config.name == 'Part_No') {
                                    var obj = ele('form1').getValues();
                                    ajax(fd, obj, 11, function (json) {
                                        console.log(3);
                                        ele('Serial_Package').setValue('');
                                        ele('Part_No').setValue('');
                                        setTable('dataT1', json.data);
                                        webix.UIManager.setFocus(ele('Serial_Package'));
                                    }, null,
                                        function (json) {
                                            console.log(4);
                                            ele('Serial_Package').setValue('');
                                            ele('Part_No').setValue('');
                                            webix.UIManager.setFocus(ele('Serial_Package'));
                                        });
                                }

                                else if (webix.UIManager.getNext(view).config.type == 'line') {
                                    console.log(5);
                                    webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                                    //view.disable();

                                }

                                else {
                                    console.log(6);
                                    webix.UIManager.setFocus(webix.UIManager.getNext(view));
                                    //view.disable();

                                }
                            },
                        },
                        elements: [
                            {
                                rows: [
                                    {
                                        cols: [
                                            vw1("datepicker", 'Pick_Date', "Pick Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 250, hidden: 1 }),
                                            vw1("text", 'TS_Number', "PS Number", {
                                                required: true, suggest: fd + "?type=5",
                                                width: 250
                                            },
                                            ),
                                            vw1("text", 'Serial_Package', "Package ID", {
                                                width: 250,
                                                on: {
                                                    onKeyPress: function (code, e) {
                                                        if (e.key == ';')
                                                            return false;
                                                    },
                                                }
                                            }),
                                            vw1("text", 'Part_No', "Part Number", {
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
                                                    vw1('button', 'confirm', 'Save (บันทึก)', {
                                                        css: 'webix_primary',
                                                        width: 120,
                                                        on: {
                                                            onItemClick: function () {
                                                                var obj = ele('form1').getValues();
                                                                webix.confirm(
                                                                    {
                                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                        callback: function (res) {
                                                                            if (res) {
                                                                                ajax(fd, obj, 41, function (json) {
                                                                                    ele('TS_Number').enable();
                                                                                    ele('TS_Number').setValue('');
                                                                                    ele('Serial_Package').setValue('');
                                                                                    ele('dataT1').clearAll();

                                                                                }, null,
                                                                                    function (json) {
                                                                                    });
                                                                            }
                                                                            ele('confirm').show();
                                                                        }
                                                                    });
                                                            }
                                                        }
                                                    }),
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
                        padding: 3,
                        cols: [
                            {
                                view: "datatable", id: $n("dataT1"), navigation: true, select: true, editaction: "custom",
                                resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                threeState: true, rowLineHeight: 25, rowHeight: 25,
                                datatype: "json", headerRowHeight: 25, leftSplit: 2, editable: true,
                                scheme:
                                {
                                    $change: function (item) {
                                        if (item.Confirm_Picking_DateTime == null && item.Status_Picking == 'PENDING' && item.Count != 0 && item.Qty_Package != item.Count) {
                                            item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                        }
                                        if (item.Confirm_Picking_DateTime == null && item.Status_Picking == 'PENDING' && item.Count != 0 && item.Qty_Package == item.Count) {
                                            item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                        }
                                    }
                                },
                                columns: [
                                    { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                    { id: "Pick_Date", header: ["Pick Date", { content: "textFilter" }], width: 120 },
                                    { id: "Serial_Package", header: ["Package ID", { content: "textFilter" }], width: 150 },
                                    { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                    { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 100 },
                                    { id: "Count", header: ["Count", { content: "textFilter" }], width: 100 },
                                    { id: "Confirm_Picking_DateTime", header: ["Confirm Picking Date", { content: "textFilter" }], width: 200 },
                                ],
                                onClick:
                                {
                                },
                                on: {
                                    "onItemClick": function (id) {
                                        this.editRow(id);
                                    },
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