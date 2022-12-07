var header_ViewGTN = function () {
    var menuName = "ViewGTN_", fd = "Shipping/" + menuName + "data.php";

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
            setTable('dataTREE', json.data);
        }, btn);
    };

    var cells =
        [{
            header: "VIEW GTN",
            body: {
                rows: [
                ]
            }
        }];



    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ViewGTN",
        body:
        {
            id: "ViewGTN_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form",
                        id: $n("form1"),
                        elements:
                            [
                                {
                                    cols:
                                        [
                                            {
                                                rows: [
                                                    {},
                                                    vw1("button", 'btnExport', "Export Report", {
                                                        width: 150, css: 'webix_primary',
                                                        on:
                                                        {
                                                            onItemClick: function () {
                                                                var dataT1 = ele("dataTREE"), obj = {}
                                                                if (dataT1.count() != 0) {
                                                                    var obj = {};
                                                                    obj.filenameprefix = 'GTN_Report';
                                                                    $.fileDownload("Shipping/ViewGTN_data.php",
                                                                        {
                                                                            httpMethod: "POST",
                                                                            data: { obj: obj, type: 5 },
                                                                            successCallback: function (url) {
                                                                            },
                                                                            prepareCallback: function (url) {
                                                                            },
                                                                            failCallback: function (responseHtml, url) {

                                                                            }
                                                                        });
                                                                }
                                                                else {
                                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูลในตาราง', callback: function () { } });
                                                                }
                                                            }
                                                        }
                                                    }),
                                                ]
                                            },
                                            {},
                                            {
                                                rows: [
                                                    vw1("button", 'btnFind', "Find (ค้นหา)", {
                                                        width: 120, on:
                                                        {
                                                            onItemClick: function () {
                                                                var btn = this;
                                                                loadData(btn);
                                                            }
                                                        }
                                                    }),
                                                ]
                                            },
                                        ]
                                },
                                {
                                    view: "treetable", id: $n('dataTREE'), headerRowHeight: 20, rowLineHeight: 25, select: true,
                                    rowHeight: 25, resizeColumn: true, css: { "font-size": "13px" },
                                    editable: true, 
                                    //pager: "pagerA",
                                    scheme:
                                    {
                                        $change: function (item) {
                                            if (item.Is_Header == 'YES' && item.Status_Shipping == 'CONFIRM SHIP') {
                                                item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                            }
                                            if (item.Is_Header == 'YES' && (item.Status_Shipping == 'COMPLETE' || item.Status_Shipping == 'PENDING')) {
                                                item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                            }
                                        }
                                    },
                                    columns: [

                                        {
                                            id: $n("icon_cancel"), header: "&nbsp;", width: 40, template: function (row) {
                                                if (row.Is_Header == "YES" && (row.Status_Shipping == 'COMPLETE' || row.Status_Shipping == 'PENDING')) {
                                                    return "<span style='cursor:pointer' class='mdi mdi-cancel'></span>";
                                                }
                                                else {
                                                    return '';
                                                }
                                            }
                                        },
                                        {
                                            id: $n("icon_edit"), header: "&nbsp;", width: 40, template: function (row) {
                                                if (row.Is_Header == "YES" && (row.Status_Shipping == 'COMPLETE' || row.Status_Shipping == 'PENDING')) {
                                                    return "<span style='cursor:pointer' class='mdi mdi-pencil'></span>";
                                                }
                                                else {
                                                    return '';
                                                }
                                            }

                                        },
                                        {
                                            id: "tag_pdf", header: "&nbsp;", width: 40,
                                            template: function (row) {
                                                if (row.Is_Header == "YES" && row.Status_Shipping != 'CANCEL') {
                                                    return "<span style='cursor:pointer' class='mdi mdi-file'></span>";
                                                }
                                                else {
                                                    return '';
                                                }
                                            }
                                        },
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
                                        { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 60 },
                                        { id: "Total_Qty", header: ["Total", { content: "textFilter" }], width: 70 },
                                        { id: "Truck_ID", header: ["Truck ID", { content: "textFilter" }], width: 120 },
                                        { id: "Truck_Driver", header: ["Truck Driver", { content: "textFilter" }], width: 150 },
                                        { id: "Truck_Type", header: ["Truck Type", { content: "textFilter" }], width: 100 },
                                        { id: "Trip_Number", header: ["Trip Number", { content: "textFilter" }], width: 120 },
                                        { id: "Status_Shipping", header: ["Status Shipping", { content: "textFilter" }], width: 150 },
                                        { id: "Confirm_Shipping_DateTime", header: ["Confirm Shipping", { content: "textFilter" }], width: 150 },
                                    ],
                                    on:
                                    {

                                    },
                                    onClick:
                                    {
                                        "mdi-file": function (e, t) {
                                            var row = this.getItem(t);
                                            var data = row.GTN_Number;
                                            window.open("print/doc/gtn.php?data=" + data, '_blank');
                                        },
                                        "mdi-pencil": function (e, t) {
                                            var row = this.getItem(t);
                                            var obj = row.GTN_Number;
                                            msBox('แก้ไข', function () {
                                                ajax(fd, obj, 21, function (json) {
                                                    loadData();
                                                }, null,
                                                    function (json) {
                                                    });
                                            }, row);

                                        },
                                        "mdi-cancel": function (e, t) {
                                            var row = this.getItem(t), datatable = this;
                                            var obj = row.GTN_Number;
                                            console.log('obj : ', obj);
                                            msBox('ยกเลิก', function () {
                                                ajax(fd, obj, 31, function (json) {
                                                    loadData();
                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ยกเลิกสำเร็จ', callback: function () { } });

                                                }, null,
                                                    function (json) {
                                                    });
                                            }, row);
                                        },
                                    },
                                },
                                {
                                    view: "pager", id: "pagerA",
                                    animate: true,
                                    size: 50,
                                    group: 5
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