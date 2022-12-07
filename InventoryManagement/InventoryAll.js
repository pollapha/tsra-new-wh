var header_InventoryAll = function () {
    var menuName = "InventoryAll_", fd = "InventoryManagement/" + menuName + "data.php";

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
            header: "INVENTORY",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_InventoryAll",
        body:
        {
            id: "InventoryAll_id",
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
                                                        width: 150, css: 'webix_primary', on:
                                                        {
                                                            onItemClick: function () {
                                                                var dataT1 = ele("dataTREE"), obj = {}
                                                                if (dataT1.count() != 0) {
                                                                    var obj = {};
                                                                    obj.filenameprefix = 'Inventory_Report';
                                                                    $.fileDownload("InventoryManagement/InventoryAll_data.php",
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
                                    view: "datatable", id: $n('dataTREE'), headerRowHeight: 20, rowLineHeight: 25, select: true,
                                    rowHeight: 25, resizeColumn: true, css: { "font-size": "13px" },
                                    editable: true, footer: true,
                                    //pager: "pagerA",
                                    scheme:
                                    {
                                        $change: function (item) {

                                        }
                                    },
                                    columns: [
                                        {
                                            id: "NO", header: "No.", css: "rank", width: 50, sort: "int",
                                            footer: { text: "Total:" }
                                        },
                                        { id: "GRN_Number", header: ["GRN Number", { content: "textFilter" }], width: 150 },
                                        { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                        { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                                        { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                        { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                        { id: "WorkOrder", header: ["Work order", { content: "textFilter" }], width: 150 },
                                        //{ id: "Qty", header: ["Qty", { content: "textFilter" }], width: 70, },
                                        {
                                            id: "Total_Qty", header: ["On hand", { content: "textFilter" }], width: 80,
                                            footer: { content: "summColumn" }
                                        },
                                        { id: "Qty_Unit", header: ["Unit", { content: "textFilter" }], width: 70 },
                                        { id: "Status_Working", header: ["Status", { content: "textFilter" }], width: 130 },
                                        { id: "Location_Code", header: ["Location", { content: "textFilter" }], width: 100 },
                                        { id: "Area", header: ["Area", { content: "textFilter" }], width: 100 },
                                        { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 120 },
                                        { id: "Package_Type", header: ["Package Type", { content: "textFilter" }], width: 120 },
                                        { id: "Receive_Date", header: ["Receive Date", { content: "textFilter" }], width: 120 },
                                        { id: "Receive_Time", header: ["Receive Time", { content: "textFilter" }], width: 120 },
                                        { id: "Dimansion", header: ["Dimansion", { content: "textFilter" }], width: 120 },
                                        { id: "Color", header: ["Color", { content: "textFilter" }], width: 200 },
                                        { id: "Model", header: ["Model", { content: "textFilter" }], width: 80 },
                                        { id: "Mat_SAP1", header: ["Mat SAP1", { content: "textFilter" }], width: 100 },
                                        { id: "Mat_SAP1", header: ["Mat SAP3", { content: "textFilter" }], width: 100 },
                                        { id: "DN_Number", header: ["DN Number", { content: "textFilter" }], width: 120 },
                                    ],
                                    on:
                                    {

                                    },
                                },
                                {
                                    view: "pager", id: "pagerA",
                                    animate: true,
                                    size: 100,
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