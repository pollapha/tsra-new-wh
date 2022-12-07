var header_Transaction = function () {
    var menuName = "Transaction_", fd = "InventoryManagement/" + menuName + "data.php";

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
        }, btn);
    };

    var cells =
        [{
            header: "TRANSACTION",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_Transaction",
        body:
        {
            id: "Transaction_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form", paddingY: 10, scroll: false, id: $n('form1'),
                        elements: [
                            {
                                rows: [
                                    {
                                        cols: [
                                            vw1("button", 'btnExport', "Export Report", {
                                                width: 150, css: 'webix_primary', on:
                                                {
                                                    onItemClick: function () {
                                                        var dataT1 = ele("dataT1"), obj = {}
                                                        if (dataT1.count() != 0) {
                                                            var obj = {};
                                                            obj.filenameprefix = 'Transaction_Report';
                                                            $.fileDownload("InventoryManagement/Transaction_data.php",
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
                                            {},
                                            vw1('button', 'find', 'Find (ค้นหา)', {
                                                width: 120,
                                                on: {
                                                    onItemClick: function () {
                                                        loadData();
                                                    }
                                                }
                                            }),
                                        ],
                                    },

                                ]
                            },
                        ]
                    },
                    {
                        cols: [
                            {
                                view: "datatable", id: $n("dataT1"), navigation: true, select: true, editaction: "custom",
                                resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                threeState: true, rowLineHeight: 25, rowHeight: 25,
                                datatype: "json", headerRowHeight: 25, leftSplit: 4, editable: true,
                                pager: "pagerA",
                                scheme:
                                {
                                    $change: function (obj) {
                                        var css = {};
                                        obj.$cellCss = css;
                                    }
                                },
                                columns: [
                                    { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                    { id: "GRN_Number", header: ["GRN Number", { content: "textFilter" }], width: 130 },
                                    { id: "TS_Number", header: ["TS Number", { content: "textFilter" }], width: 130 },
                                    { id: "GTN_Number", header: ["GTN Number", { content: "textFilter" }], width: 130 },
                                    { id: "Invoice_Number", header: ["Invoice Number", { content: "textFilter" }], width: 130 },
                                    { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                    { id: "Serial_Number", header: ["Package ID", { content: "textFilter" }], width: 120 },
                                    { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 140 },
                                    { id: "Qty", header: ["Qty", { content: "textFilter" }], width: 60 },
                                    { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                    { id: "Trans_Type", header: ["Transaction Type", { content: "textFilter" }], width: 150 },
                                    { id: "From_Area", header: ["From Area", { content: "textFilter" }], width: 100 },
                                    { id: "To_Area", header: ["To Area", { content: "textFilter" }], width: 100 },
                                    { id: "From_Location_Code", header: ["From Location", { content: "textFilter" }], width: 120 },
                                    { id: "To_Location_Code", header: ["To Location", { content: "textFilter" }], width: 120 },
                                    { id: "Creation_DateTime", header: ["Creation Date", { content: "textFilter" }], width: 120 },
                                    { id: "Created_By", header: ["Created By", { content: "textFilter" }], width: 120 },
                                    { id: "Last_Updated_DateTime", header: ["Last Updated Date", { content: "textFilter" }], width: 120 },
                                    { id: "Updated_By", header: ["Updated By", { content: "textFilter" }], width: 120 },
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
                    {
                        view: "pager", id: "pagerA",
                        animate: true,
                        size: 200,
                        group: 5
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