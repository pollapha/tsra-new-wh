var header_PrintLabel = function () {
    var menuName = "PrintLabel_", fd = "Assembly/" + menuName + "data.php";

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

    var cells =
        [{
            header: "PRINT LABEL",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_PrintLabel",
        body:
        {
            id: "PrintLabel_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form", scroll: false, id: $n('form1'),
                        on: {

                            "onSubmit": function (view, e) {

                                if (view.config.name == 'Part_No') {
                                    var obj1 = ele('form1').getValues();
                                    ajax(fd, obj1, 1, function (json) {
                                        var Part_No = ele('Part_No').getValue();
                                        var Date_Label = ele('Date').getValue();
                                        var format = webix.Date.dateToStr("%Y-%m-%d");
                                        var string = format(new Date(Date_Label));
                                        var obj = {};
                                        obj.printerName = 'LABEL';
                                        obj.copy = 1;
                                        obj.Part_No = Part_No;
                                        obj.Date = string;
                                        obj.printType = 'I';
                                        obj.warter = 'NO';
                                        var temp = window.open("print/doc/label-manual.php?printerName=" + obj.printerName + "&copy=" + obj.copy + "&Part_No=" + obj.Part_No + "&Date=" + obj.Date + "&printType=" + obj.printType + "&warter=" + obj.warter);
                                        //webix.alert({ title: "<b>ข้อความจากระบบ</b>", text: "รองรับ TXT เท่านั้น", type: 'alert-error' });
                                        ele('Part_No').setValue('');
                                        ele('Date').setValue(new Date());

                                    }, null,
                                        function (json) {
                                            ele('Part_No').setValue('');
                                            webix.UIManager.setFocus(ele('Part_No'));
                                        });



                                }
                                else if (webix.UIManager.getNext(view).config.type == 'line') {
                                    webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                                }
                                else {

                                    webix.UIManager.setFocus(webix.UIManager.getNext(view));

                                }
                            },
                        },
                        elements: [
                            {
                                rows: [
                                    {
                                        cols: [
                                            vw1("datepicker", 'Date', "Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 250 }),
                                            vw1("text", 'Part_No', "Part Number", { width: 250 }),
                                            {}
                                        ],
                                    },

                                ]
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