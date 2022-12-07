var header_UploadPart = function () {
    var menuName = "UploadPart_", fd = "MasterData/" + menuName + "data.php";

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
        console.log(ele("form1").getValues());
        var obj = ele('form1').getValues();

        ajax(fd, obj, 1, function (json) {
            setTable('dataT1', json.data);
        }, null,
            function (json) {
                /* ele('find').callEvent("onItemClick", []); */
            }, btn);
    };

    function exportExcel(btn) {
        var dataT1 = ele("dataT1"), obj = {}, data = [];
        if (dataT1.count() == 0) {
            webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูลในตาราง', callback: function () { } });
        }

        for (var i = -1, len = dataT1.config.columns.length; ++i < len;) {
            obj[dataT1.config.columns[i].id] = dataT1.config.columns[i].header[0].text;
        }
        delete obj.icon_edit;
        var objKey = Object.keys(obj);
        var f = [];
        for (var i = -1, len = objKey.length; ++i < len;) {
            f.push(objKey[i]);
        }

        var col = [];
        for (var i = -1, len = f.length; ++i < len;) {
            col[col.length] = obj[f[i]];
        }
        data[data.length] = col;
        if (dataT1.count() > 0) {
            btn.disable();
            dataT1.eachRow(function (row) {
                var r = dataT1.getItem(row), rr = [];
                for (var i = -1, len = f.length; ++i < len;) {
                    rr[rr.length] = r[f[i]];
                }
                data[data.length] = rr;
            });

            var worker = new Worker('js/workerToExcel.js?v=1');
            worker.addEventListener('message', function (e) {
                saveAs(e.data, 'ABT' + new Date().getTime() + ".xlsx");
                btn.enable();
                webix.message({ expire: 7000, text: "Export สำเร็จ" });
            }, false);
            worker.postMessage({ 'cmd': 'start', 'msg': data });
        }
        else { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: "ไม่พบข้อมูลในตาราง", callback: function () { } }); }
    };

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_UploadPart",
        body:
        {
            id: "UploadPart_id",
            type: "clean",
            rows:
                [
                    {
                        view: "form", paddingY: 0, scroll: false, id: $n('form1'),
                        elements: [
                            {
                                cols:
                                    [
                                        vw1('button', 'find', 'Find (ค้นหา)', {
                                            width: 150,
                                            on: {
                                                onItemClick: function (id, e) {
                                                    loadData();
                                                }
                                            }
                                        }),
                                        vw1("uploader", 'Upload_DN', "Upload", {
                                            link: "mytemplate", autosend: false,
                                            width: 150, hidden: false, multiple: false, on:
                                            {
                                                onBeforeFileAdd: function (file) {
                                                    var type = file.type.toLowerCase();
                                                    if (type == "xlsx") {
                                                        //ele("Upload_DN").disable();
                                                        ele("save_file").show();
                                                    }
                                                    else {
                                                        webix.alert({ title: "<b>ข้อความจากระบบ</b>", text: "รองรับ TXT เท่านั้น", type: 'alert-error' });
                                                        return false;
                                                    }

                                                },
                                            },
                                        }),
                                        {
                                            view: "list",
                                            id: "mytemplate",
                                            type: "uploader",
                                            autoheight: true,
                                            borderless: true,
                                        },

                                        vw1("button", 'save_file', "Save files (บันทึกไฟล์)", {
                                            hidden: 1,
                                            type: 'form', width: 200,
                                            click: function () {
                                                ele("Upload_DN").files.data.each(function (obj, index) {
                                                    var formData = new FormData();
                                                    formData.append("upload", obj.file);
                                                    if ($$("mytemplate") == null) {
                                                        ele("save_file").hide();
                                                    }
                                                    $.ajax({
                                                        type: 'POST',
                                                        cache: false,
                                                        contentType: false,
                                                        processData: false,
                                                        url: fd + '?type=41',
                                                        data: formData,
                                                        success: function (data) {
                                                            webix.confirm(
                                                                {
                                                                    title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                    callback: function (res) {
                                                                        if (res) {
                                                                            loadData();
                                                                        }
                                                                        var json = JSON.parse(data);
                                                                        ele("Upload_DN").files.data.clearAll();
                                                                        webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: json.mms, callback: function () { } });
                                                                        ele("Upload_DN").enable();
                                                                        ele("save_file").hide();
                                                                    }
                                                                });



                                                        }
                                                    });
                                                });
                                            }
                                        }),
                                        vw1("button", 'btnExport', "Export (โหลดเป็นไฟล์เอ๊กเซล)", {
                                            width: 200, on:
                                            {
                                                onItemClick: function () {
                                                    exportExcel(this);
                                                }
                                            }
                                        })
                                    ]
                            },
                            {
                                cols: [
                                    {
                                        view: "datatable", id: $n("dataT1"), navigation: true, select: "row", editaction: "custom",
                                        resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                        threeState: true, rowLineHeight: 25, rowHeight: 25,
                                        datatype: "json", headerRowHeight: 25, leftSplit: 4, editable: true,
                                        scheme:
                                        {
                                            $change: function (obj) {
                                                var css = {};
                                                obj.$cellCss = css;
                                            }
                                        },
                                        columns: [
                                            {
                                                id: $n("icon_del"), header: "&nbsp;", width: 40, template: function (row) {
                                                    if (row.Is_Header == "YES") {
                                                        return "<span style='cursor:pointer' class='webix_icon fa-trash'></span>";
                                                    }
                                                    else {
                                                        return '';
                                                    }
                                                }
                                            },
                                            { id: "No", header: "", css: { "text-align": "right" }, editor: "", width: 40 },
                                            { id: "Receive_Date", header: ["Receive Date", { content: "textFilter" }], width: 150 },
                                            { id: "Serial_ID", header: ["Serial ID.", { content: "textFilter" }], width: 150 },
                                            { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 150 },
                                            { id: "Qty", header: ["Qty", { content: "textFilter" }], width: 200 },
                                            { id: "Receive_Status", header: ["Receive Status", { content: "textFilter" }], width: 120 },
                                            { id: "Creation_Date", header: ["Creation Date", { content: "textFilter" }], width: 150 },

                                        ],
                                        onClick:
                                        {
                                            "fa-trash": function (e, t) {
                                                var row = this.getItem(t), datatable = this;
                                                var obj = row.DN_Number;
                                                console.log('obj : ', obj);
                                                msBox('ลบ', function () {
                                                    ajax(fd, obj, 31, function (json) {
                                                        loadData();
                                                        webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ลบสำเร็จ', callback: function () { } });

                                                    }, null,
                                                        function (json) {
                                                        });
                                                }, row);
                                            },
                                        },
                                        on: {
                                            // "onEditorChange": function (id, value) {
                                            // }
                                            "onItemClick": function (id) {
                                                this.editRow(id);
                                            }
                                        }
                                    },
                                ],
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