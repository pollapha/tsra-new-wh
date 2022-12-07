var header_Dashboard = function () {
    var menuName = "Dashboard_", fd = "Assembly/" + menuName + "data.php";

    function init() {
        loadData();
        loadData2();
        loadData3();
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

    var multiple_dataset = [
        { Location_Code: "A01", Side: "Front_RH", Assembled_Qty: "400", Qty: "300" },
    ];


    function loadData(btn) {
        var obj = ele('form1').getValues();
        ajax(fd, obj, 1, function (json) {
            var data = json.data;

            setTable('dataT1', json.data);
            setChart('chart1', data);
        }, null,
            function (json) {
            });
    };

    function loadData2(btn) {
        var obj = ele('form1').getValues();
        ajax(fd, obj, 2, function (json) {
            var data = json.data;
            ele("Total_Plan").setValue(data[0].Total_Plan);
            ele("Total_Actual").setValue(data[0].Total_Actual);
            ele("Total_Balance").setValue(data[0].Total_Balance);
            ele("Success").setValue(data[0].Success);
            ele("Failure").setValue(data[0].Failure);
        }, null,
            function (json) {
            });
    };


    function loadData3(btn) {
        var obj = ele('form1').getValues();
        ajax(fd, obj, 3, function (json) {
            var data_success = transpose(json.data.success);
            var data_failure = transpose(json.data.failure);

            Array.prototype.push.apply(data_success, data_failure);
            console.log(data_success);
            setChart('chart2', data_success);
        });
    };


    // var dataset = [
    //     { Percent: "75%", Goal: "Success", color: "#30b358" },
    //     { Percent: "25%", Goal: "Failure", color: "#e8591c" },
    // ];

    var dataset = [
        {
            "Percent": "75%",
            "Percent1": "Percent",
            "Goal": "Success",
            "color": "#30b358",
            "color1": "color"
        },
        {
            "Percent": "25%",
            "Percent1": "Percent",
            "Goal": "Failure",
            "color": "#e8591c",
            "color1": "color"
        },
    ];

    function transpose(data) {
        var group1 = _.chain(data)
            .groupBy("Goal")
            .map((value, key) => ({ Goal: key, Data_List: value }))
            .value();
        var data = [];
        for (let i = 0, len = group1.length; i < len; i++) {
            let obj = { NO: i + 1, Goal: group1[i].Goal };
            for (let i2 = 0, len2 = group1[i].Data_List.length; i2 < len2; i2++) {
                obj[group1[i].Data_List[i2].color1] = group1[i].Data_List[i2].color;
                obj[group1[i].Data_List[i2].Percent1] = group1[i].Data_List[i2].Percent;
            }
            data.push(obj);
        }
        return data;
    };
    console.log(transpose(dataset));


    function setTable(tableName, data) {
        if (!ele(tableName)) return;
        ele(tableName).clearAll();
        ele(tableName).parse(data);
        ele(tableName).filterByAll();
    };

    function setChart(tableName, data) {
        if (!ele(tableName)) return;
        ele(tableName).clearAll();
        ele(tableName).parse(data);
    };

    var cells =
        [{
            header: "TSRA-ASSEMBLY DASHBOARD",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_Dashboard",
        body:
        {
            id: "Dashboard_id",
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
                                            vw1("datepicker", 'Assembly_Date', "Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 250 }),
                                            {
                                                rows: [
                                                    {},
                                                    {
                                                        cols: [
                                                            vw1('button', 'refresh', 'Find (ค้นหา)', {
                                                                width: 120,
                                                                on: {
                                                                    onItemClick: function (id, e) {
                                                                        var obj = ele('form1').getValues();

                                                                        ajax(fd, obj, 1, function (json) {
                                                                            loadData();
                                                                            loadData2();
                                                                            loadData3();
                                                                        }, null,
                                                                            function (json) {
                                                                            });
                                                                    }
                                                                }
                                                            }),
                                                        ]
                                                    },
                                                ]
                                            },
                                            {},
                                            {}
                                        ]
                                    },
                                ]
                            },
                        ]
                    },
                    {
                        padding: 3,
                        height: 200,
                        cols: [
                            {
                                view: "datatable", id: $n("dataT1"), navigation: true, select: "row", editaction: "custom",
                                resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                threeState: true, rowLineHeight: 25, rowHeight: 25,
                                datatype: "json", headerRowHeight: 25, leftSplit: 1, editable: true,
                                scheme:
                                {
                                    $change: function (obj) {
                                        var css = {};
                                        obj.$cellCss = css;
                                    }
                                },
                                columns: [
                                    { id: "NO", header: "No.", css: "rank", width: 40, sort: "int" },
                                    { id: "Side", header: ["Part Type",], width: 100 },
                                    { id: "Part_No", header: ["Part Number",], width: 180 },
                                    { id: "Part_Name", header: ["Part Name",], width: 280 },
                                    { id: "Qty", header: ["Plan",], width: 70 },
                                    { id: "Assembled_Qty", header: ["Actual",], width: 70 },
                                    { id: "Balance", header: ["Balance",], width: 70 },
                                    //percent
                                    { id: "Ratio", header: ["Ratio",], width: 70 },
                                    { id: "On_hand", header: ["On hand",], width: 80 },
                                ],
                            },
                        ]
                    },
                    {
                        cols: [
                            { view: "label", id: "Total", label: "Grand Total", align: "left", width: 620, css: { "background": "#f4f5f9" } },
                            { view: "label", id: $n("Total_Plan"), label: "", width: 70, align: "left", css: { "background": "#f4f5f9" } },
                            { view: "label", id: $n("Total_Actual"), label: "", width: 70, align: "left", css: { "background": "#f4f5f9" } },
                            { view: "label", id: $n("Total_Balance"), label: "", width: 70, align: "left", css: { "background": "#f4f5f9" } },
                            { css: { "background": "#f4f5f9" }, width: 70 },
                            { css: { "background": "#f4f5f9" } },
                        ]
                    },
                    {
                        cols: [
                            { width: 620, css: { "background": "#f4f5f9" } },
                            { width: 70, css: { "background": "#f4f5f9" } },
                            { width: 70, css: { "background": "#f4f5f9" } },
                            { view: "label", label: "Success", align: "left", width: 70, css: { "background": "#f4f5f9" } },
                            { view: "label", id: $n("Success"), label: "", width: 60, align: "center", css: { "background": "#30b358" } },
                            { css: { "background": "#f4f5f9" } },
                        ]
                    },
                    {
                        cols: [
                            { width: 620, css: { "background": "#f4f5f9" } },
                            { width: 70, css: { "background": "#f4f5f9" } },
                            { width: 70, css: { "background": "#f4f5f9" } },
                            { view: "label", label: "Failure", align: "left", width: 70, css: { "background": "#f4f5f9" } },
                            { view: "label", id: $n("Failure"), label: "", width: 60, align: "center", css: { "background": "#e8591c" } },
                            { css: { "background": "#f4f5f9" } },
                        ]
                    },
                    {
                        cols: [
                            {
                                view: "chart",
                                id: $n("chart1"),
                                width: 600,
                                height: 250,
                                type: "bar",
                                barWidth: 50,
                                radius: 2,
                                xAxis: {
                                    template: "#Side#",
                                },
                                yAxis: {
                                    start: 0,
                                    step: 100,
                                },
                                legend: {
                                    values: [{ text: "Actual", color: "#30b358" }, { text: "Plan", color: "#e8591c" }],
                                    valign: "middle",
                                    align: "top",
                                    width: 90,
                                    layout: "x"
                                },
                                series: [
                                    {
                                        value: "#Assembled_Qty#",
                                        label: "#Assembled_Qty#",
                                        color: "#30b358",
                                        item: {
                                            borderColor: "#e8591c",
                                            color: "#ffffff"
                                        },
                                        tooltip: {
                                            template: "#Assembled_Qty#"
                                        }
                                    },
                                    {
                                        value: "#Qty#",
                                        label: "#Qty#",
                                        type: 'line',
                                        color: "#e8591c",
                                        tooltip: {
                                            template: "#Qty#"
                                        },
                                        line: {
                                            color: "#e8591c",
                                            width: 2,
                                            shadow: false
                                        },
                                    },
                                ],
                                data: multiple_dataset
                            },
                            {
                                view: "chart",
                                id: $n("chart2"),
                                type: "pie",
                                width: 414,
                                value: "#Percent#",
                                color: "#color#",
                                label: "#Goal#",
                                pieInnerText: "#Percent#",
                                shadow: 0,
                                data: dataset,
                                legend: {
                                    values: [{ text: "Success", color: "#30b358" }, { text: "Failure", color: "#e8591c" }],
                                    valign: "middle",
                                    align: "top",
                                    width: 90,
                                    layout: "x"
                                },
                            },
                            {}
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