var header_ConfirmShip = function () {
    var menuName = "ConfirmShip_", fd = "Shipping/" + menuName + "data.php";

    function init() {
        webix.UIManager.setFocus(ele('GTN_Number'));
        // webix.event(ele("GTN_Number").getInputNode(), "paste", (e) => {
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

    function loadData(btn) {
        var obj = ele('form1').getValues();
        ajax(fd, obj, 1, function (json) {
            setTable('dataT1', json.data);
        }, null,

            function (json) {
                ele('dataT1').clearAll();
                ele('GTN_Number').enable();
                ele('GTN_Number').setValue('');
                ele('confirm').hide();
                webix.UIManager.setFocus(ele('GTN_Number'));
            });
    };

    var cells =
        [{
            header: "CONFIRM SHIP",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ConfirmShip",
        body:
        {
            id: "ConfirmShip_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form",
                        id: $n("form1"),
                        on:
                        {
                            "onSubmit": function (view, e) {
                                if (view.config.name == 'GTN_Number') {
                                    var obj = ele('form1').getValues();
                                    console.log(obj);
                                    loadData();
                                    ele('confirm').show();
                                }
                            },
                        },
                        elements:
                            [
                                {
                                    cols:
                                        [
                                            {
                                                cols: [
                                                    vw1("text", 'GTN_Number', "GTN Number", {
                                                        width: 200,
                                                        on: {
                                                            onKeyPress: function (code, e) {
                                                                if (e.key == ';')
                                                                    return false;
                                                            },
                                                        }
                                                    }),
                                                ]

                                            },
                                            {
                                                rows: [
                                                    {},
                                                    vw1('button', 'confirm', 'Confirm', {
                                                        css: 'webix_primary',
                                                        width: 150,
                                                        hidden: 1,
                                                        on: {
                                                            onItemClick: function () {
                                                                var obj = ele('form1').getValues();
                                                                webix.confirm(
                                                                    {
                                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                        callback: function (res) {
                                                                            if (res) {
                                                                                ajax(fd, obj, 41, function (json) {
                                                                                    ele('GTN_Number').enable();
                                                                                    ele('GTN_Number').setValue('');
                                                                                    setTable('dataT1', json.data);
                                                                                    webix.UIManager.setFocus(ele('GTN_Number'));
                                                                                }, null,
                                                                                    function (json) {
                                                                                        ele('GTN_Number').enable();
                                                                                        ele('GTN_Number').setValue('');
                                                                                        ele('dataT1'), clearAll();
                                                                                        webix.UIManager.setFocus(ele('GTN_Number'));
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

                                        ]
                                },

                                {
                                    padding: 3,
                                    cols: [
                                        {
                                            view: "treetable", id: $n("dataT1"), navigation: true, select: true, editaction: "custom",
                                            resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                            threeState: true, rowLineHeight: 25, rowHeight: 25,
                                            datatype: "json", headerRowHeight: 25, leftSplit: 2, editable: true,
                                            scheme:
                                            {
                                                $change: function (item) {
                                                    if (item.Is_Header == 'YES' && item.Status_Shipping == 'CONFIRM SHIP') {
                                                        item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                                    }
                                                }
                                            },
                                            columns: [
                                                { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                                                {
                                                    id: "GTN_Number", header: ["GTN Number", { content: "textFilter" }], editor: "", width: 180,
                                                    template: "{common.treetable()} #GTN_Number#"
                                                },
                                                { id: "Invoice_Number", header: ["Invoice Number", { content: "textFilter" }], width: 150 },
                                                { id: "Ship_Date", header: ["Ship Date", { content: "textFilter" }], width: 100 },
                                                { id: "Ship_Time", header: ["Ship Time", { content: "textFilter" }], width: 100 },
                                                { id: "Customer_Code", header: ["Ship To", { content: "textFilter" }], width: 100 },
                                                { id: "TS_Number", header: ["TS Number", { content: "textFilter" }], width: 130 },
                                                { id: "Serial_Package", header: ["Package ID", { content: "textFilter" }], width: 130 },
                                                { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                                { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 150 },
                                                { id: "Model", header: ["Model", { content: "textFilter" }], width: 100 },
                                                { id: "Qty_Package", header: ["Qty(PCS)", { content: "textFilter" }], width: 120 },
                                                { id: "Total_Qty", header: ["Total", { content: "textFilter" }], width: 80 },
                                                { id: "Truck_ID", header: ["Truck ID", { content: "textFilter" }], width: 100 },
                                                { id: "Truck_Driver", header: ["Truck Driver", { content: "textFilter" }], width: 120 },
                                                { id: "Truck_Type", header: ["Truck Type", { content: "textFilter" }], width: 100 },
                                                { id: "Trip_Number", header: ["Trip Number", { content: "textFilter" }], width: 120 },
                                                { id: "Status_Shipping", header: ["Status Shipping", { content: "textFilter" }], width: 150 },
                                            ],
                                            onClick:
                                            {
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
                    }
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