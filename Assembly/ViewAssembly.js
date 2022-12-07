var header_ViewAssembly = function () {
    var menuName = "ViewAssembly_", fd = "Assembly/" + menuName + "data.php";

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
            header: "VIEW ASSEMBLY",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ViewAssembly",
        body:
        {
            id: "ViewAssembly_id",
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
                                    editable: true,
                                    pager: "pagerA",
                                    scheme:
                                    {
                                        $change: function (item) {
                                            if (item.status == 'PACKING') {
                                                item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                            }
                                            if (item.status == 'WORKING') {
                                                item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                            }
                                            if (item.Count == 0) {
                                                item.$css = { "background": "" };
                                            }
                                        }
                                    },
                                    columns: [
                                        { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                        { id: "Assembly_Date", header: ["Assembly Date", { content: "textFilter" }], width: 140 },
                                        { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                        { id: "WorkOrder", header: ["Work Order", { content: "textFilter" }], width: 150 },
                                        { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                        { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 280 },
                                        { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 80 },
                                        { id: "Count", header: ["Completed", { content: "textFilter" }], width: 90 },
                                    ],
                                    on:
                                    {

                                    },
                                    onClick:
                                    {
                                        "wxi-file": function (e, t) {
                                            var row = this.getItem(t);
                                            var data = row.Assy_Number;
                                            if (row.Status_Assembly == 'PENDING') {
                                                window.open("print/doc/assy.php?data=" + data, '_blank');
                                            } else if (row.Status_Assembly != 'PENDING' && row.Status_Assembly != 'CANCEL') {
                                                window.open("print/doc/tag-card.php?data=" + data, '_blank');
                                                window.open("print/doc/assy.php?data=" + data, '_blank');
                                            }
                                        },
                                        "fa-pencil": function (e, t) {
                                            var row = this.getItem(t);
                                            var obj = row.GRN_Number;
                                            msBox('แก้ไข', function () {
                                                ajax(fd, obj, 21, function (json) {
                                                    loadData();
                                                }, null,
                                                    function (json) {
                                                    });
                                            }, row);

                                        },
                                        "fa-ban": function (e, t) {
                                            var row = this.getItem(t), datatable = this;
                                            var obj = row.GRN_Number;
                                            console.log('obj : ', obj);
                                            msBox('บันทึก', function () {
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