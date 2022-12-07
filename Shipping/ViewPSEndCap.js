var header_ViewPSEndCap = function () {
    var menuName = "ViewPSEndCap_", fd = "Shipping/" + menuName + "data.php";

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

    function loadData2(btn) {
        ajax(fd, {}, 2, function (json) {
            setTable('dataTREE2', json.data);
        }, btn);
    };

    var cells =
        [{
            header: "VIEW PS (END CAP)",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ViewPSEndCap",
        body:
        {
            id: "ViewPSEndCap_id",
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
                                    cols:[
                                        {
                                            rows:[
                                                {
                                                    cols:
                                                        [
                                                            {},
                                                            {
                                                                padding:{
                                                                    bottom:10,
                                                                  },
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
                                                    view: "treetable", id: $n('dataTREE'), headerRowHeight: 20, rowLineHeight: 25, rowHeight: 25, select: true,
                                                    resizeColumn: true, css: { "font-size": "13px" },
                                                    editable: true, threeState:true, 
                                                    //pager: "pagerA",
                                                    scheme:
                                                    {
                                                        $change: function (item) {
                                                            if (item.Is_Header == 'YES' && item.Confirm_Picking_DateTime != null && item.Status_Picking == 'COMPLETE') {
                                                                item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                                            }
                                                            if (item.Is_Header == 'YES' && item.Confirm_Picking_DateTime == null && item.Status_Picking == 'PENDING') {
                                                                item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                                            }
                                                        }
                                                    },
                                                    columns: [
                                                        {
                                                            id: $n("icon_cancel"), header: "&nbsp;", width: 40, template: function (row) {
                                                                if (row.Is_Header == "YES" && (row.Status_Picking == 'COMPLETE' || row.Status_Picking == 'PENDING')
                                                                    && row.Picking_Header_ID == null) {
                                                                    return "<span style='cursor:pointer' class='mdi mdi-cancel'></span>";
                                                                }
                                                                else {
                                                                    return '';
                                                                }
                                                            }
                                                        },
                                                        {
                                                            id: $n("icon_edit"), header: "&nbsp;", width: 40, template: function (row) {
                                                                if (row.Is_Header == "YES" && row.Status_Picking != 'CANCEL'
                                                                    && row.Picking_Header_ID == null) {
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
                                                                if (row.Is_Header == "YES" && row.Status_Picking != 'CANCEL') {
                                                                    return "<span style='cursor:pointer' class='mdi mdi-file'></span>";
                                                                }
                                                                else {
                                                                    return '';
                                                                }
                                                            }
                                                        },
                                                        { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                                                        {
                                                            id: "TS_Number", header: ["PS Number", { content: "textFilter" }], editor: "", width: 180,
                                                            template: "{common.treetable()} #TS_Number#"
                                                        },
                                                        { id: "Picking_Pre_ID", header: ["Picking_Pre_ID", { content: "textFilter" }], width: 130, hidden:1},
                                                        { id: "Pick_Date", header: ["Pick Date", { content: "textFilter" }], width: 100 },
                                                        { id: "Status_Picking", header: ["Status", { content: "textFilter" }], width: 100 },
                                                        { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                                        { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 100 },
                                                        { id: "Serial_Package", header: ["Package ID", { content: "textFilter" }], width: 150 },
                                                        { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                                        { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                                                        { id: "PO_Number", header: ["PO Number", { content: "textFilter" }], width: 100 },
                                                        { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 60 },
                                                        { id: "Total_Qty", header: ["Total", { content: "textFilter" }], width: 70 },
                                                        { id: "Confirm_Picking_DateTime", header: ["Confirm Picking", { content: "textFilter" }], width: 150 },
                                                    ],
                                                    on:
                                                    {
                
                                                    },
                                                    onClick:
                                                    {
                                                        "mdi-file": function (e, t) {
                                                            var row = this.getItem(t);
                                                            var data = row.TS_Number;
                                                            if(row.Serial_Number == null){
                                                                window.open("print/doc/ts-endcap-r.php?data=" + data, '_blank');
                                                            }
                                                            else{
                                                                window.open("print/doc/ts-endcap-b.php?data=" + data, '_blank');
                                                            }
                                                        },
                                                        "mdi-pencil": function (e, t) {
                                                            // ele('win_edit').show();
                                                            var row = this.getItem(t);
                                                            var obj = row.TS_Number;
                                                            // console.log(obj);
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
                                                            var obj = row.TS_Number;
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