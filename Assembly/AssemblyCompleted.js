var header_AssemblyCompleted = function () {
    var menuName = "AssemblyCompleted_", fd = "Assembly/" + menuName + "data.php";

    function init() {
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
        var obj1 = ele('form1').getValues();
        var obj2 = ele('form2').getValues();
        var obj3 = { ...obj1, ...obj2 };
        ajax(fd, obj3, 1, function (json) {
            setTable('dataT1', json.data);

        }, null,
            function (json) {
                ele('dataT1').clearAll();
            });
    };

    var cells =
        [{
            header: "CONFIRM ASSEMBLY COMPLETED",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_AssemblyCompleted",
        body:
        {
            id: "AssemblyCompleted_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form", scroll: false, id: $n('form1'),
                        elements: [
                            {
                                rows: [
                                    {
                                        cols: [
                                            vw1("datepicker", 'Assembly_Date', "Plan Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 250 }),
                                            vw1("text", 'WorkOrder', "Work order", { width: 250 }),
                                            {
                                                rows: [
                                                    {},
                                                    vw1('button', 'find', 'Find (ค้นหา)', {
                                                        width: 120,
                                                        on: {
                                                            onItemClick: function (id, e) {
                                                                loadData();
                                                                webix.UIManager.setFocus(ele('Part_No'));
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
                        view: "form", scroll: false, id: $n('form2'),
                        on:

                        {

                            "onSubmit": function (view, e) {

                                if (view.config.name == 'Part_No') {
                                    var obj1 = ele('form1').getValues();
                                    var obj2 = ele('form2').getValues();
                                    var obj3 = { ...obj1, ...obj2 };
                                    ajax(fd, obj3, 11, function (json) {
                                        console.log(3);
                                        loadData();
                                        ele('Part_No').setValue('');
                                    }, null,
                                        function (json) {
                                            console.log(4);
                                            ele('Part_No').setValue('');
                                            webix.UIManager.setFocus(ele('Part_No'));
                                        });
                                }

                                else if (webix.UIManager.getNext(view).config.type == 'line') {
                                    console.log(5);
                                    webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                                    view.disable();
                                }

                                else {
                                    console.log(6);
                                    webix.UIManager.setFocus(webix.UIManager.getNext(view));
                                    view.disable();

                                }
                            },

                        },
                        elements: [
                            {
                                rows: [
                                    {
                                        cols: [
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
                                                    vw1('button', 'Save', 'Save (บันทึก)', {
                                                        css: "webix_primary",
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
                                                                                    loadData();
                                                                                    ele('WorkOrder').setValue('');

                                                                                }, null,
                                                                                    function (json) {
                                                                                        loadData();
                                                                                    });
                                                                            }
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
                                        if (item.Count != 0) {
                                            item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                        }
                                        if (item.Count == item.Qty_Package) {
                                            item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                        }
                                    }
                                },
                                columns: [
                                    { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                    { id: "Assembly_Date", header: ["Assembly Date", { content: "textFilter" }], width: 140 },
                                    { id: "WorkOrder", header: ["Work Order", { content: "textFilter" }], width: 150 },
                                    { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                    { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 200 },
                                    { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 80 },
                                    { id: "Count", header: ["Completed", { content: "textFilter" }], width: 90 },
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