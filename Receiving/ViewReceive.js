var header_ViewReceive = function () {
    var menuName = "ViewReceive_", fd = "Receiving/" + menuName + "data.php";

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
            header: "VIEW RECEIVE",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ViewReceive",
        body:
        {
            id: "ViewReceive_id",
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
                                                        width: 150, css: "webix_primary", on:
                                                        {
                                                            onItemClick: function () {
                                                                var dataT1 = ele("dataTREE"), obj = {}
                                                                if (dataT1.count() != 0) {
                                                                    var obj = {};
                                                                    obj.filenameprefix = 'GRN_Report';
                                                                    $.fileDownload("Receiving/ViewReceive_data.php",
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
                                                    //{},
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
                                            if (item.Is_Header == 'YES' && item.Confirm_Receive_DateTime != null && item.Status_Receiving == 'COMPLETE') {
                                                item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                            }
                                            if (item.Is_Header == 'YES' && item.Confirm_Receive_DateTime == null && item.Status_Receiving == 'PENDING') {
                                                item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                            }
                                            if (item.Is_Header == 'YES' && item.Area == '') {
                                                item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                            }
                                        }
                                    },
                                    columns: [
                                        {
                                            id: $n("icon_cancel"), header: "&nbsp;", width: 40, template: function (row) {
                                                if (row.Is_Header == "YES" && (row.Status_Receiving == 'COMPLETE' || row.Status_Receiving == 'PENDING')
                                                    && row.Pick_status != 'Y' && row.Picking_Header_ID == null) {
                                                    return "<span style='cursor:pointer' class='mdi mdi-cancel'></span>";
                                                }
                                                else {
                                                    return '';
                                                }
                                            }
                                        },

                                        {
                                            id: $n("icon_edit"), header: "&nbsp;", width: 40, template: function (row) {
                                                if (row.Is_Header == "YES" && row.Status_Receiving != 'CANCEL'
                                                    && row.Pick_status != 'Y' && row.Picking_Header_ID == null) {
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
                                                if (row.Is_Header == "YES" && row.Status_Receiving != 'CANCEL') {
                                                    return "<span style='cursor:pointer' class='mdi mdi-file'></span>";
                                                }
                                                else {
                                                    return '';
                                                }
                                            }
                                        },
                                        { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                                        {
                                            id: "GRN_Number", header: ["GRN Number", { content: "textFilter" }], editor: "", width: 180,
                                            template: "{common.treetable()} #GRN_Number#"
                                        },
                                        { id: "DN_Number", header: ["DN Number", { content: "textFilter" }], width: 100 },
                                        { id: "Confirm_Receive_DateTime", header: ["Receive Date Time", { content: "textFilter" }], width: 150 },
                                        { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 100 },
                                        { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                        { id: "Package_Type", header: ["Package Type", { content: "textFilter" }], width: 120 },
                                        { id: "Serial_Number", header: ["Package ID", { content: "textFilter" }], width: 130 },
                                        { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                        { id: "WorkOrder", header: ["Work order", { content: "textFilter" }], width: 120 },
                                        { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                        { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                                        //{ id: "Model", header: ["Model", { content: "textFilter" }], width: 100 },
                                        { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 60 },
                                        { id: "Status_Working", header: ["Status", { content: "textFilter" }], width: 120 },
                                    ],
                                    on:
                                    {

                                    },
                                    onClick:
                                    {
                                        "mdi-file": function (e, t) {
                                            var row = this.getItem(t);
                                            var data = row.GRN_Number;
                                            if (row.Part_Type == 'Finish good') {

                                                if (row.Serial_Number != null) {
                                                    window.open("print/doc/receive-tag.php?data=" + data, '_blank');
                                                    window.open("print/doc/grn-fg.php?data=" + data, '_blank');
                                                    window.open("print/doc/control-rack.php?data=" + data, '_blank');
                                                }
                                                else {
                                                    window.open("print/doc/receive-tag.php?data=" + data, '_blank');
                                                    window.open("print/doc/grn-fg.php?data=" + data, '_blank');
                                                }
                                            } else if (row.Part_Type == 'Assembly part') {
                                                window.open("print/doc/work-order.php?data=" + data, '_blank');
                                                window.open("print/doc/grn.php?data=" + data, '_blank');
                                            }
                                        },
                                        "mdi-pencil": function (e, t) {
                                            // ele('win_edit').show();
                                            var row = this.getItem(t);
                                            var obj = row.GRN_Number;
                                            // console.log(obj);
                                            msBox('แก้ไข', function () {
                                                ajax(fd, obj, 21, function (json) {
                                                    loadData();
                                                    //webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'แก้ไขสำเร็จ', callback: function () { } });

                                                }, null,
                                                    function (json) {
                                                    });
                                            }, row);

                                        },
                                        "mdi-cancel": function (e, t) {
                                            var row = this.getItem(t), datatable = this;
                                            var obj = row.GRN_Number;
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