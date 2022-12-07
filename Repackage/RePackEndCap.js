var header_RePackEndCap = function () {
    var menuName = "RePackEndCap_", fd = "Repackage/" + menuName + "data.php";

    function init() {
        loadData();
        loadData3();
        loadData4();
        // webix.event(ele("Serial_ID").getInputNode(), "paste", (e) => {
        //     e.preventDefault();
        //     let data = e.clipboardData.getData('text');
        //     if (data.indexOf(";") !== -1) {
        //         e.target.value += data.replace(/;/g, '');
        //     }
        // });
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
            if (json.data.header.length > 0) {
                ele('create').disable();
                ele('Palletizing_Date').disable();
                ele('Serial_Number').disable();
                ele('form1').setValues(json.data.header[0]);
                ele('form2').setValues(json.data.header[0]);
                setTable('dataT1', json.data.body);
            }

        }, null,
            function (json) {
            }, btn);

    };


    function loadData3(btn) {
        ajax(fd, {}, 3, function (json) {
            if (json.data.header.length > 0) {
                setTable('dataT3', json.data.body);
            }

        }, null,
            function (json) {
            }, btn);

    };

    function loadData4(btn) {
        ajax(fd, {}, 4, function (json) {
            var data = json.data.body;
            if (json.data.header.length > 0) {
                ele("Total_Qty").setValue(data[0].Total_Qty);
            }

        }, null,
            function (json) {
            });
    };


    // function loadData6(btn) {
    //     var obj1 = ele('form1').getValues();
    //     var obj2 = ele('form2').getValues();
    //     var obj3 = { ...obj1, ...obj2 };
    //     ajax(fd, obj3, 6, function (json) {
    //         var data = json.data;
    //         //ele("Part_No").setValue(data[0].Part_No);
    //         ele("On_hand").setValue(data[0].On_hand);

    //     }, null,
    //         function (json) {
    //             /* ele('find').callEvent("onItemClick", []); */
    //         });
    // };

    var cells =
        [{
            header: "RE-PACK (END CAP)",
            body: {
                rows: [
                ]
            }
        }];

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_RePackEndCap",
        body:
        {
            id: "RePackEndCap_id",
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
                                            vw1("datepicker", 'Palletizing_Date', "Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, required: false, width: 200 }),
                                            {
                                                rows: [
                                                    {},
                                                    vw1('button', 'create', 'Create Package ID', {
                                                        css: 'webix_primary',
                                                        width: 160,
                                                        on: {
                                                            onItemClick: function () {
                                                                var obj = ele('form1').getValues();
                                                                webix.confirm(
                                                                    {
                                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                        callback: function (res) {
                                                                            if (res) {
                                                                                ajax(fd, obj, 11, function (json) {
                                                                                    loadData();
                                                                                    webix.UIManager.setFocus(ele('Serial_ID'));
                                                                                }, null,
                                                                                    function (json) {
                                                                                    });
                                                                            }
                                                                            else {
                                                                                ele('create').enable();
                                                                            }
                                                                        }
                                                                    });
                                                            },
                                                        }
                                                    })
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
                        view: "form", scroll: false, id: $n('form2'), on:

                        {

                            "onSubmit": function (view, e) {

                                if (view.config.name == 'Serial_ID') {

                                    var obj1 = ele('form1').getValues();
                                    var obj2 = ele('form2').getValues();
                                    var obj3 = { ...obj1, ...obj2 };

                                    ajax(fd, obj3, 12, function (json) {
                                        loadData();
                                        loadData3();
                                        loadData4();

                                        webix.UIManager.setFocus(ele('Serial_ID'));

                                    }, null,
                                        function (json) {
                                            ele('Serial_ID').setValue('');
                                            webix.UIManager.setFocus(ele('Serial_ID'));
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
                                            vw1("text", 'Serial_Number', "Package ID", { width: 200, }),
                                            vw1("text", 'Serial_ID', "Package Number", {
                                                width: 200,
                                                on: {
                                                    onKeyPress: function (code, e) {
                                                        if (e.key == ';')
                                                            return false;
                                                    },
                                                }
                                            }),
                                            {},
                                            //{ view: "label", id: "On_hand", label: "On-hand : ", css: { "background": "#f4f5f9" }, width: 70 },
                                            //{ view: "label", id: $n("On_hand"), label: "", css: { "background": "#f4f5f9" }, width: 70 },
                                        ]
                                    },
                                ]
                            },
                        ]
                    },


                    {
                        padding: 3,
                        cols: [
                            {
                                view: "datatable", id: $n("dataT1"), navigation: true, select: "row", editaction: "custom",
                                resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                threeState: true, rowLineHeight: 25, rowHeight: 25, height: 300,
                                datatype: "json", headerRowHeight: 25, leftSplit: 2, editable: true,
                                scheme:
                                {
                                    $change: function (obj) {
                                        var css = {};
                                        obj.$cellCss = css;
                                    }
                                },
                                columns: [
                                    {
                                        id: $n("icon_cancel"), header: "&nbsp;", width: 40, template: function (row) {
                                            return "<span style='cursor:pointer' class='webix_icon wxi-trash'></span>";
                                        }
                                    },
                                    { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                    { id: "Palletizing_Pre_ID", header: ["Palletizing_Pre_ID", { content: "textFilter" }], width: 150, hidden: 1 },
                                    { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 150 },
                                    { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 250 },
                                    { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 350 },
                                    { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 150 },
                                    { id: "Qty_Package", header: ["Qty", { content: "textFilter" }], width: 60, },
                                    { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                ],
                                onClick:
                                {
                                    "wxi-trash": function (e, t) {
                                        var row = this.getItem(t), datatable = this;
                                        var obj = row.Palletizing_Pre_ID;
                                        console.log('obj : ', obj);
                                        msBox('ลบ', function () {
                                            ajax(fd, obj, 31, function (json) {
                                                loadData();
                                                loadData3();
                                                loadData4();
                                                ele('dataT3').hideColumn("Qty");
                                                ele('dataT3').hideColumn("icon_complete");
                                            }, null,
                                                function (json) {
                                                });
                                        }, row);
                                    },
                                },
                            },
                        ]
                    },
                    {
                        cols: [
                            {
                                view: "datatable", id: $n("dataT3"), navigation: false, select: "row", editaction: "custom",
                                resizeColumn: true, autoheight: false, multiselect: false, hover: "myhover",
                                threeState: true, rowLineHeight: 25, rowHeight: 25,
                                datatype: "json", headerRowHeight: 37, leftSplit: 2, editable: true,
                                height: 150, width: 400,
                                columns: [
                                    { id: "Palletizing_Header_ID", header: ["Palletizing_Header_ID", { content: "textFilter" }], width: 150, hidden: 1 },
                                    { id: "Part_ID", header: ["Part_ID", { content: "textFilter" }], width: 150, hidden: 1 },
                                    { id: "Part_No", header: ["Part Number"], width: 230 },
                                    // { id: "Qty_New", header: [""], width: 50, editor: "inline-text", template: "<input type='text' value='#Qty#' style='width:20px;'>" },
                                    { header: "", template: "#Qty#", width: 50 },
                                    { id: "Qty", header: "", editor: "text", width: 40 },
                                    {
                                        id: "icon_complete", header: "&nbsp;", width: 40, hidden: 1, template: function (row) {
                                            return "<span style='cursor:pointer' class='webix_icon wxi-check'></span>";
                                        }
                                    },
                                ],
                                on: {
                                    "onItemClick": function (id) {
                                        this.editRow(id);
                                    },
                                    "onEditorChange": function (id, value) {
                                        this.getItem(id.row)[id.column] = value;
                                        this.refresh(id.row);
                                        var row = this.getItem(id.row), datatable = this;
                                        obj = row.Part_ID.concat("/", row.Palletizing_Header_ID).concat("/", row.Qty);
                                        console.log('obj : ', obj);
                                        if (row.Qty != '') {
                                            ajax(fd, obj, 21, function (json) {
                                                loadData();
                                                loadData3();
                                                loadData4();
                                                ele('dataT3').editStop();
                                            }, null,
                                                function (json) {
                                                    loadData3();
                                                    ele('dataT3').editStop();
                                                });
                                        }

                                    },
                                }
                                // ready: function () {
                                //     this.attachEvent("onItemClick", function (data, prevent) {
                                //         this.editColumn("Qty");
                                //         console.log('0');
                                //         return true;
                                //     });
                                //     this.attachEvent("onBeforeEditStop", function (state) {
                                //         if (state.value != state.old) {
                                //             ele('dataT3').showColumn("icon_complete");
                                //             console.log('1');
                                //             return true;
                                //         }
                                //         else {
                                //             console.log('6');
                                //             return false;
                                //         }
                                //     });
                                //     this.attachEvent("onEditorChange", function (id, value) {
                                //         this.getItem(id.row)[id.column] = value;
                                //         this.refresh(id.row);
                                //         console.log('2');
                                //     });
                                //     this.attachEvent("onAfterEditStop", function () {
                                //         console.log('3');
                                //         ele('dataT3').showColumn("icon_complete");
                                //         ele('dataT3').hideColumn("Qty");
                                //         return true;
                                //     });
                                // },
                                // onClick:
                                // {
                                //     "wxi-check": function (e, t) {
                                //         var row = this.getItem(t), datatable = this;
                                //         var obj = row.Part_ID.concat("/", row.Palletizing_Header_ID).concat("/", row.Qty);
                                //         console.log(obj);
                                //         webix.confirm(
                                //             {
                                //                 title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                //                 callback: function (res) {
                                //                     if (res) {
                                //                         ajax(fd, obj, 21, function (json) {
                                //                             loadData3();
                                //                             loadData();
                                //                             loadData4();
                                //                             console.log('4');
                                //                             ele('dataT3').hideColumn("icon_complete");
                                //                             return false;
                                //                         }, null,
                                //                             function (json) {
                                //                                 loadData3();
                                //                                 console.log('5');
                                //                                 ele('dataT3').hideColumn("icon_complete");
                                //                                 return false;
                                //                             });
                                //                     }
                                //                 }
                                //             });
                                //     },
                                // },
                            },
                            {
                                rows: [
                                    {
                                        cols: [
                                            { view: "label", id: "Total", label: "Total : ", css: { "background": "#f4f5f9" }, width: 100 },
                                            { view: "label", id: $n("Total_Qty"), label: "", css: { "background": "#f4f5f9" }, width: 110 },

                                            vw2('button', 'save_1', 'save', 'Save (บันทึก)', {
                                                css: 'webix_primary',
                                                width: 120,
                                                on: {
                                                    onItemClick: function () {
                                                        var obj1 = ele('form1').getValues();
                                                        var obj2 = ele('form2').getValues();
                                                        var obj3 = { ...obj1, ...obj2 };
                                                        webix.confirm(
                                                            {
                                                                title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                callback: function (res) {
                                                                    if (res) {
                                                                        ajax(fd, obj3, 41, function (json) {
                                                                            ele('dataT1').clearAll();
                                                                            ele('dataT3').clearAll();
                                                                            ele("Total_Qty").setValue('');
                                                                            ele('dataT3').hideColumn("Qty");
                                                                            //ele("On_hand").setValue('');

                                                                            ele('create').enable();
                                                                            ele('Palletizing_Date').enable();
                                                                            ele('Serial_Number').enable();
                                                                            ele('Serial_Number').setValue('');
                                                                            ele('Serial_ID').setValue('');
                                                                            //ele('Part_No').setValue('');
                                                                            //ele('Customer_Code').setValue('');
                                                                            //ele('Qty').setValue('');
                                                                            ele('Palletizing_Date').setValue(new Date());
                                                                            ele('dataT3').hideColumn("Qty");
                                                                            loadData();
                                                                            loadData3();
                                                                            loadData4();
                                                                        }, null,
                                                                            function (json) {
                                                                            });
                                                                    }
                                                                }
                                                            });
                                                    }
                                                }
                                            }),
                                            {}
                                        ]
                                    }
                                ]
                            }

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