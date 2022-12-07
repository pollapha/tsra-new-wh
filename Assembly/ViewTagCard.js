var header_ViewTagCard = function () {
    var menuName = "ViewTagCard_", fd = "Assembly/" + menuName + "data.php";

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
            header: "VIEW TAG CARD",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_ViewTagCard",
        body:
        {
            id: "ViewTagCard_id",
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
                                                                        obj += this.getItem(id).ID + ",";
                                                                    }
                                                                });
                                                                var data = obj;
                                                                console.log(data);
                                                                if (obj == "") {
                                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล', callback: function () { } });
                                                                }
                                                                else {
                                                                    window.open("print/doc/tag-card-wheelLip.php?data=" + data, '_blank');
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
                                    view: "datatable", id: $n('dataTREE'), headerRowHeight: 20, rowLineHeight: 25, select: true,
                                    rowHeight: 25, resizeColumn: true, css: { "font-size": "13px" },
                                    editable: true,
                                    pager: "pagerA",
                                    scheme:
                                    {
                                        $change: function (item) {
                                            if (item.Status_Working != 'FG') {
                                                item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                            }
                                            if (item.Status_Working == 'FG') {
                                                item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                            }
                                        }
                                    },
                                    columns: [
                                        {
                                            id: "check_box", header: "",
                                            checkValue: "on", uncheckValue: "off", width: 40,
                                            template: function (row) {
                                                return '<input class="webix_table_checkbox" type = "checkbox"> ';
                                            },
                                        },
                                        { id: "NO", header: "No.", css: "rank", width: 40, sort: "int" },
                                        { id: "ID", header: ["ID", { content: "textFilter" }], width: 130, hidden: 1 },
                                        { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                        { id: "WorkOrder", header: ["Work order", { content: "textFilter" }], width: 150 },
                                        { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                        { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 280 },
                                        { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 150 },
                                        { id: "Model", header: ["Model", { content: "textFilter" }], width: 100 },
                                        { id: "Qty", header: ["Qty", { content: "textFilter" }], width: 80 },
                                        { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                        { id: "Status_Working", header: ["Status", { content: "textFilter" }], width: 130 },
                                    ],
                                    on:
                                    {

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