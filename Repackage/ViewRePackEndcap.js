var header_ViewRePackEndcap = function () {
    var menuName = "ViewRePackEndcap_", fd = "Repackage/" + menuName + "data.php";

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
            header: "VIEW RE-PACK (END CAP)",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ViewRePackEndcap",
        body:
        {
            id: "ViewRePackEndcap_id",
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
                                                    vw1("button", 'printTag', "Print Tag Card", {
                                                        width: 150, css: 'webix_primary',
                                                        on: {
                                                            onItemClick: function () {
                                                                var obj = "";
                                                                ele("dataTREE").eachRow(function (id) {
                                                                    if (this.getItem(id).check_box == "on") {
                                                                        obj += this.getItem(id).Serial_Number + ",";
                                                                    }
                                                                });
                                                                var data = obj;
                                                                console.log(data);
                                                                if (obj == "") {
                                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล', callback: function () { } });
                                                                }
                                                                else {
                                                                    window.open("print/doc/tag-card-endcap-r.php?data=" + data, '_blank');
                                                                    obj = "";
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
                                    view: "treetable", id: $n('dataTREE'), headerRowHeight: 20, rowLineHeight: 25, rowHeight: 25, 
                                    resizeColumn: true, select: "row", css: { "font-size": "13px" },
                                    editable: true,
                                    //pager: "pagerA",
                                    scheme:
                                    {
                                        $change: function (item) {
                                            if (item.Is_Header == 'YES' && item.Confirm_DateTime != null && item.Pick == 'Y') {
                                                item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                            }
                                            if (item.Is_Header == 'YES' && item.Confirm_DateTime != null && item.Pick != 'Y') {
                                                item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                            }
                                        }
                                    },
                                    columns: [
                                        {
                                            id: $n("icon_cancel"), header: "&nbsp;", width: 40, template: function (row) {
                                                if (row.Is_Header == "YES" && (row.Status == 'COMPLETE' || row.Status == 'PENDING')) {
                                                    return "<span style='cursor:pointer' class='mdi mdi-cancel'></span>";
                                                }
                                                else {
                                                    return '';
                                                }
                                            }
                                        },
                                        {
                                            id: $n("icon_edit"), header: "&nbsp;", width: 40, template: function (row) {
                                                if (row.Is_Header == "YES" && row.Status != 'CANCEL') {
                                                    return "<span style='cursor:pointer' class='mdi mdi-pencil'></span>";
                                                }
                                                else {
                                                    return '';
                                                }
                                            }

                                        },
                                        {
                                            id: "check_box", header: "",
                                            checkValue: "on", uncheckValue: "off", width: 40,
                                            template: function (row) {
                                                if (row.Is_Header != "YES") {
                                                    return "";
                                                }
                                                else if (row.status == "CANCEL") {
                                                    return "";
                                                }
                                                else {
                                                    return '<input class="webix_table_checkbox" type = "checkbox"> ';
                                                }
                                            },
                                        },
                                        { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                                        {
                                            id: "Serial_Number", header: ["Package ID", { content: "textFilter" }], editor: "", width: 180,
                                            template: "{common.treetable()} #Serial_Number#"
                                        },
                                        { id: "Palletizing_Pre_ID", header: ["Palletizing_Pre_ID", { content: "textFilter" }], width: 130, hidden: 1 },
                                        { id: "Confirm_DateTime", header: ["Date Time", { content: "textFilter" }], width: 150 },
                                        { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 100 },
                                        { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                        { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                        { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                        { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 320 },
                                        //{ id: "Model", header: ["Model", { content: "textFilter" }], width: 100 },
                                        { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 60 },
                                        //{ id: "Status_Working", header: ["Status", { content: "textFilter" }], width: 120 },
                                        { id: "Total_Qty", header: ["Total", { content: "textFilter" }], width: 60 },
                                    ],
                                    on:
                                    {

                                    },
                                    onClick:
                                    {

                                        "mdi-file": function (e, t) {
                                            var row = this.getItem(t);
                                            var data = row.Serial_Number;
                                            window.open("print/doc/control-rack-repack.php?data=" + data, '_blank');
                                        },
                                        "mdi-pencil": function (e, t) {
                                            // ele('win_edit').show();
                                            var row = this.getItem(t);
                                            var obj = row.Serial_Number;
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
                                            var obj = row.Serial_Number;
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