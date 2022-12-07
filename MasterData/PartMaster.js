var header_PartMaster = function () {
    var menuName = "PartMaster_", fd = "MasterData/" + menuName + "data.php";

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
            setTable('dataT1', json.data);

        }, null,
            function (json) {
                //ele('find').callEvent("onItemClick", []);
            }, btn);

    };


    //add
    webix.ui(
        {
            view: "window", id: $n("win_add"), modal: 1,
            head: "Add (เพิ่มข้อมูล)", top: 50, position: "center",
            body:
            {
                view: "form", scroll: false, id: $n("win_add_form"), width: 600,
                elements:
                    [
                        {
                            cols:
                                [
                                    {
                                        rows:
                                            [
                                                {
                                                    cols: [
                                                        {
                                                            view: "list",
                                                            id: "mytemplate",
                                                            type: "uploader",
                                                            autoheight: true,
                                                            borderless: true,
                                                        },
                                                    ]
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'Part_No', 'Part Number', { labelPosition: "top" }),
                                                        vw1('text', 'Part_Name', 'Part Name', { labelPosition: "top" }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'Mat_SAP1', 'Mat SAP1(FG)', { labelPosition: "top", required: false }),
                                                        vw1('text', 'Mat_SAP3', 'Mat SAP3', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'Model', 'Model', { labelPosition: "top", required: false }),
                                                        vw1('text', 'Color', 'Color', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'Customer_Code', 'Customer', {
                                                            labelPosition: "top", required: false, suggest: fd + "?type=5",
                                                        }),
                                                        vw1('richselect', 'Side', 'Side', {
                                                            required: false,
                                                            labelPosition: "top",
                                                            value: '1', options: [
                                                                { id: '1', value: "--- Select ---" },
                                                                { id: 'RH', value: "RH" },
                                                                { id: 'LH', value: "LH" },
                                                                { id: 'FRONT_RH', value: "FRONT_RH" },
                                                                { id: 'FRONT_LH', value: "FRONT_LH" },
                                                                { id: 'REAR_RH', value: "REAR_RH" },
                                                                { id: 'REAR_LH', value: "REAR_LH" },
                                                            ]
                                                        }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'Packing', 'Packing', { labelPosition: "top", required: false }),
                                                        //vw1('text', 'Package_Code', 'Packing', { labelPosition: "top", required: false }),
                                                        vw1('text', 'Mat_GI', 'Mat GI', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('richselect', 'Type', 'Type', {
                                                            required: false,
                                                            labelPosition: "top",
                                                            value: '1', options: [
                                                                { id: '1', value: "--- Select ---" },
                                                                { id: 'End Cap', value: "End Cap" },
                                                                { id: 'Wheel lip', value: "Wheel lip" },
                                                            ]
                                                        }),
                                                        vw1('richselect', 'Part_Type', 'Part Type', {
                                                            required: true,
                                                            labelPosition: "top",
                                                            value: '1', options: [
                                                                { id: '1', value: "--- Select ---" },
                                                                { id: 'Finish good', value: "Finish good" },
                                                                { id: 'Assembly part', value: "Assembly part" },
                                                                { id: 'Sub material', value: "Sub material" },
                                                            ]
                                                        }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'SNP_Per_Rack', 'SNP/Rack', { labelPosition: "top", required: false }),
                                                        vw1('text', 'SNP_Per_Box', 'SNP/Box', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'SNP_Per_Bag', 'SNP/Bag', { labelPosition: "top", required: false }),
                                                        vw1('text', 'Width_Part', 'Width Part', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('text', 'Length_Part', 'Length Part', { labelPosition: "top", required: false }),
                                                        vw1('text', 'Height_Part', 'Height Part', { labelPosition: "top", required: false }),
                                                        vw1('text', 'Weight_Part', 'Weight Part', { labelPosition: "top", required: false, hidden: 1 }),
                                                    ],
                                                },

                                            ]
                                    }
                                ]
                        },
                        {
                            cols:
                                [
                                    {},
                                    vw1('button', 'save', 'Save (บันทึก)', {
                                        css: "webix_primary", width: 120,
                                        on: {
                                            onItemClick: function () {
                                                webix.confirm(
                                                    {
                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                        callback: function (res) {
                                                            if (res) {
                                                                var obj = ele('win_add_form').getValues();
                                                                console.log(obj);
                                                                ajax(fd, obj, 11, function (json) {
                                                                    setTable('dataT1', json.data);
                                                                    ele('win_add').hide();
                                                                    ele('Part_No').setValue('');
                                                                    ele('Part_Name').setValue('');
                                                                    ele('Side').setValue('1');
                                                                    ele('Mat_SAP1').setValue('');
                                                                    ele('Mat_SAP3').setValue('');
                                                                    ele('Mat_GI').setValue('');
                                                                    ele('Model').setValue('');
                                                                    ele('Color').setValue('');
                                                                    ele('Customer_Code').setValue('');
                                                                    //ele('Package_Code').setValue('1');
                                                                    ele('Packing').setValue('');
                                                                    ele('SNP_Per_Rack').setValue('');
                                                                    ele('SNP_Per_Box').setValue('');
                                                                    ele('Weight_Part').setValue('');
                                                                    ele('Type').setValue('1');
                                                                    ele('Part_Type').setValue('1');
                                                                    //ele("upload").files.data.clearAll();
                                                                })
                                                            }
                                                        }
                                                    });
                                            }
                                        }
                                    }),
                                    vw1('button', 'cancel', 'Cancel (ยกเลิก)', {
                                        type: 'danger', width: 130,
                                        on: {
                                            onItemClick: function () {
                                                ele('win_add').hide();
                                                ele('Part_No').setValue('');
                                                ele('Part_Name').setValue('');
                                                ele('Side').setValue('1');
                                                ele('Mat_SAP1').setValue('');
                                                ele('Mat_SAP3').setValue('');
                                                ele('Mat_GI').setValue('');
                                                ele('Model').setValue('');
                                                ele('Color').setValue('');
                                                ele('Customer_Code').setValue('');
                                                //ele('Package_Code').setValue('1');
                                                ele('Packing').setValue('');
                                                ele('SNP_Per_Rack').setValue('');
                                                ele('SNP_Per_Box').setValue('');
                                                ele('Weight_Part').setValue('');
                                                ele('Type').setValue('1');
                                                ele('Part_Type').setValue('1');
                                                // ele("upload").files.data.clearAll();
                                            }
                                        }
                                    }),
                                ]
                        }
                    ],
                rules:
                {
                }
            }
        });


    //edit
    webix.ui(
        {
            view: "window", id: $n("win_edit"), modal: 1,
            head: "Edit (แก้ไขข้อมูล)", top: 50, position: "center",
            body:
            {
                view: "form", scroll: false, id: $n("win_edit_form"), width: 600,
                elements:
                    [
                        {
                            cols:
                                [
                                    {
                                        rows:
                                            [
                                                {
                                                    cols: [
                                                        vw1('text', 'Part_ID', 'Part ID.', { labelPosition: "top", hidden: 1 }),
                                                        vw2('text', 'Part_No_edit', 'Part_No', 'Part Number', { labelPosition: "top", disabled: true }),
                                                        vw2('text', 'Part_Name_edit', 'Part_Name', 'Part Name', { labelPosition: "top" }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'Mat_SAP1_edit', 'Mat_SAP1', 'Mat SAP1', { labelPosition: "top", required: false }),
                                                        vw2('text', 'Mat_SAP3_edit', 'Mat_SAP3', 'Mat SAP3', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'Model_edit', 'Model', 'Model', { labelPosition: "top", required: false }),
                                                        vw2('text', 'Color_edit', 'Color', 'Color', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'Customer_Code_edit', 'Customer_Code', 'Customer', {
                                                            labelPosition: "top", required: false, suggest: fd + "?type=5",
                                                        }),
                                                        vw2('richselect', 'Side_edit', 'Side', 'Side', {
                                                            required: false,
                                                            labelPosition: "top",
                                                            value: '', options: [
                                                                { id: 'RH', value: "RH" },
                                                                { id: 'LH', value: "LH" },
                                                                { id: 'FRONT_RH', value: "FRONT_RH" },
                                                                { id: 'FRONT_LH', value: "FRONT_LH" },
                                                                { id: 'REAR_RH', value: "REAR_RH" },
                                                                { id: 'REAR_LH', value: "REAR_LH" },
                                                            ]
                                                        }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'Packing_edit', 'Packing', 'Packing', { labelPosition: "top", required: false }),
                                                        // vw2('text', 'Package_Code_edit', 'Package_Code', 'Packing', { labelPosition: "top", required: false }),
                                                        vw2('text', 'Mat_GI_edit', 'Mat_GI', 'Mat GI', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('richselect', 'Type_edit', 'Type', 'Type', {
                                                            required: false,
                                                            labelPosition: "top",
                                                            value: '', options: [
                                                                { id: 'End Cap', value: "End Cap" },
                                                                { id: 'Wheel lip', value: "Wheel lip" },
                                                            ]
                                                        }),
                                                        vw2('richselect', 'Part_Type_edit', 'Part_Type', 'Part Type', {
                                                            labelPosition: "top", required: true,
                                                            value: '', options: [
                                                                { id: 'Finish good', value: "Finish good" },
                                                                { id: 'Assembly part', value: "Assembly part" },
                                                                { id: 'Sub material', value: "Sub material" },
                                                            ]
                                                        }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'SNP_Per_Rack_edit', 'SNP_Per_Rack', 'SNP/Rack', { labelPosition: "top", required: false }),
                                                        vw2('text', 'SNP_Per_Box_edit', 'SNP_Per_Box', 'SNP/Box', { labelPosition: "top", required: false })

                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'SNP_Per_Bag_edit', 'SNP_Per_Bag', 'SNP/Bag', { labelPosition: "top", required: false }),
                                                        vw2('text', 'Width_Part_edit', 'Width_Part', 'Width Part', { labelPosition: "top", required: false }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw2('text', 'Length_Part_edit', 'Length_Part', 'Length Part', { labelPosition: "top", required: false }),
                                                        vw2('text', 'Height_Part_edit', 'Height_Part', 'Height Part', { labelPosition: "top", required: false }),
                                                        vw2('text', 'Weight_Part_edit', 'Weight_Part', 'Weight Part', { labelPosition: "top", required: false, hidden: 1 }),
                                                    ],
                                                },
                                                {
                                                    cols: [
                                                        vw1('richselect', 'Status', 'Status', {
                                                            labelPosition: "top", required: false,
                                                            value: 'ACTIVE', options: [
                                                                { id: 'ACTIVE', value: "ACTIVE" },
                                                                { id: 'INACTIVE', value: "INACTIVE" },
                                                            ]
                                                        }),
                                                        {}
                                                    ],
                                                },
                                            ]
                                    }
                                ]
                        },
                        {
                            cols:
                                [
                                    {},
                                    vw2('button', 'save_edit', 'save', 'Save (บันทึก)', {
                                        css: "webix_primary", width: 120,
                                        on: {
                                            onItemClick: function () {
                                                webix.confirm(
                                                    {
                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                        callback: function (res) {
                                                            if (res) {
                                                                var obj = ele('win_edit_form').getValues();
                                                                console.log(obj);
                                                                ajax(fd, obj, 21, function (json) {
                                                                    setTable('dataT1', json.data);
                                                                    ele('win_edit').hide();
                                                                    // $$('mytemplate_edit').hide();
                                                                    // $$('hidden').show();
                                                                    // $$("mytemplate_edit").clearAll();
                                                                })
                                                            }
                                                        }
                                                    });
                                            }
                                        }
                                    }),

                                    vw1('button', 'cancel_edit', 'Cancel (ยกเลิก)', {
                                        type: 'danger', width: 130,
                                        on: {
                                            onItemClick: function () {
                                                ele('win_edit').hide();
                                            }
                                        }
                                    }),
                                ]
                        }
                    ],
                rules:
                {
                }
            },

        });

    //add
    webix.ui(
        {
            view: "window", id: $n("win_add_img"), modal: 1,
            head: "", top: 50, position: "center", close: true,
            body:
            {
                view: "form", scroll: false, id: $n("win_add_img_form"), width: 600,
                elements:
                    [
                        {
                            cols:
                                [
                                    {
                                        rows:
                                            [
                                                {
                                                    cols: [
                                                        vw2('text', 'Part_ID_img', 'Part_ID', 'Part ID.', { labelPosition: "top", hidden: 1 }),
                                                        vw2('text', 'Part_No_show', 'Part_No', 'Part Number', { labelPosition: "top", width: 200 }),
                                                        vw2('text', 'Part_Name_show', 'Part_Name', 'Part Name', { labelPosition: "top" }),
                                                    ]
                                                },
                                                {
                                                    cols: [
                                                        vw1("uploader", 'upload', "Choose File", {
                                                            link: "mytemplate", autosend: false,
                                                            accept: "image/jpeg, image/png",
                                                            width: 150, hidden: false, multiple: false, on:
                                                            {
                                                                onBeforeFileAdd: function (upload) {
                                                                    var type = upload.type.toLowerCase();
                                                                    if (type == "jpg" || type == "png") {
                                                                        var file = upload.file;
                                                                        ele('upload').value = file;
                                                                    }
                                                                    else {
                                                                        webix.alert({ title: "<b>ข้อความจากระบบ</b>", text: "ไม่รองรับ", type: 'alert-error' });
                                                                        return false;
                                                                    }
                                                                },
                                                                onAfterFileAdd: function () {
                                                                    ele("upload").files.data.each(function (obj, index) {
                                                                        var formData = new FormData();
                                                                        formData.append("upload", obj.file);
                                                                        console.log('formData', formData);
                                                                        ele("save_img").show();
                                                                        $.ajax({
                                                                            type: 'POST',
                                                                            cache: false,
                                                                            contentType: false,
                                                                            processData: false,
                                                                            url: fd + '?type=12',
                                                                            data: formData,
                                                                            success: function (data) {
                                                                            }
                                                                        });
                                                                    });
                                                                }
                                                            },
                                                        },
                                                        ),
                                                        {
                                                            view: "list",
                                                            id: "mytemplate",
                                                            type: "uploader",
                                                            autoheight: true,
                                                            borderless: true,
                                                        },

                                                        vw2('button', 'save_img', 'save', 'Save (บันทึก)', {
                                                            width: 120, hidden: 1,
                                                            css: "webix_primary",
                                                            on: {
                                                                onItemClick: function () {
                                                                    webix.confirm(
                                                                        {
                                                                            title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                            callback: function (res) {
                                                                                if (res) {
                                                                                    var obj = ele('win_add_img_form').getValues();
                                                                                    console.log(obj);
                                                                                    ajax(fd, obj, 13, function (json) {
                                                                                        setTable('dataT1', json.data);
                                                                                        ele('win_add_img').hide();
                                                                                        ele('save_img').hide();
                                                                                        ele("upload").files.data.clearAll();
                                                                                    })
                                                                                }
                                                                            }
                                                                        });
                                                                }
                                                            }
                                                        }),
                                                    ]
                                                },
                                            ]
                                    }
                                ]
                        },

                    ],
                rules:
                {
                }
            }
        });

    var cells =
        [{
            header: "PART MASTER",
            body: {
                rows: [
                ]
            }
        }];


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_PartMaster",
        body:
        {
            id: "PartMaster_id",
            type: "clean",
            rows:
                [
                    { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                    {
                        view: "form", scroll: false, id: $n('form1'),
                        elements: [
                            {
                                cols:
                                    [
                                        vw1('button', 'add', 'Add (เพิ่มข้อมูล)', {
                                            width: 150,
                                            css: "webix_primary",
                                            on: {
                                                onItemClick: function () {
                                                    console.log(ele('win_add').show());
                                                }
                                            }
                                        }),
                                        {},
                                        vw1('button', 'find', 'Find (ค้นหา)', {
                                            width: 120,
                                            on: {
                                                onItemClick: function (id, e) {
                                                    console.log(ele("form1").getValues());
                                                    var obj = ele('form1').getValues();

                                                    ajax(fd, obj, 1, function (json) {
                                                        //webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'บันทึกสำเร็จ', callback: function () { } });
                                                        setTable('dataT1', json.data);
                                                    }, null,
                                                        function (json) {
                                                            /* ele('find').callEvent("onItemClick", []); */
                                                        });
                                                }
                                            }
                                        }),
                                    ]
                            },
                            {
                                cols: [
                                    {
                                        view: "datatable", id: $n("dataT1"), navigation: true, select: "row", editaction: "custom",
                                        resizeColumn: true, autoheight: false, multiselect: true, hover: "myhover",
                                        threeState: true, rowLineHeight: 25, rowHeight: 100,
                                        datatype: "json", headerRowHeight: 25, leftSplit: 4, editable: true,
                                        pager: "pagerA",
                                        scheme:
                                        {
                                            $change: function (obj) {
                                                var css = {};
                                                obj.$cellCss = css;
                                            }
                                        },
                                        columns: [
                                            {
                                                id: "icon_edit", header: "&nbsp;", width: 40, template: function (row) {
                                                    return "<span style='cursor:pointer' class='webix_icon wxi-pencil'></span>";
                                                }
                                            },
                                            { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                            {
                                                id: "Picture", header: "Picture", width: 150, template: function (obj) {
                                                    if (obj.Picture != null) {
                                                        return "<div class='webix_el_button' align='right'><span style='cursor:pointer' class='webix_icon mdi mdi-pencil'></span></div> <img src='" + obj.Picture + "' style='cursor:pointer; width:120px; height:auto;'>";
                                                    }
                                                    else {
                                                        return "<div class='webix_el_button' align='center' style='padding:50px'><button class='webix_secondary webix_button' style='width:120px'></i>Upload Picture</button></div>";
                                                    }
                                                }
                                            },
                                            { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 210 },
                                            { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 330 },
                                            { id: "Customer_Code", header: ["Customer", { content: "textFilter" }], width: 100 },
                                            { id: "Side", header: ["Side", { content: "textFilter" }], width: 100 },
                                            { id: "Mat_SAP1", header: ["Mat SAP1", { content: "textFilter" }], width: 100 },
                                            { id: "Mat_SAP3", header: ["Mat SAP3", { content: "textFilter" }], width: 100 },
                                            { id: "Model", header: ["Model", { content: "textFilter" }], width: 80 },
                                            { id: "Color", header: ["Color", { content: "textFilter" }], width: 180 },
                                            { id: "Type", header: ["Type", { content: "textFilter" }], width: 100 },
                                            { id: "SNP_Per_Rack", header: ["SNP/Rack", { content: "textFilter" }], width: 100 },
                                            { id: "SNP_Per_Box", header: ["SNP/Box", { content: "textFilter" }], width: 100 },
                                            { id: "SNP_Per_Bag", header: ["SNP/Box", { content: "textFilter" }], width: 100 },
                                            { id: "Weight_Part", header: ["Weight Part", { content: "textFilter" }], width: 100 },
                                            { id: "Dimansion", header: ["Dimansion", { content: "textFilter" }], width: 130 },
                                            { id: "Packing", header: ["Packing", { content: "textFilter" }], width: 100 },
                                            { id: "Mat_GI", header: ["Mat GI", { content: "textFilter" }], width: 100 },
                                            { id: "Part_Type", header: ["Part Type", { content: "textFilter" }], width: 150 },
                                            { id: "Status", header: ["Status", { content: "textFilter" }], width: 100 },
                                            //{ id: "Creation_Date", header: ["Creation Date", { content: "textFilter" }], width: 150 },

                                        ],
                                        onClick:
                                        {
                                            "wxi-pencil": function (e, t) {
                                                console.log(ele('win_edit').show());
                                                var row = this.getItem(t);
                                                console.log(row);
                                                console.log(ele('win_edit_form').setValues(row));
                                            },
                                            "webix_secondary": function (e, t) {
                                                console.log(ele('win_add_img').show());
                                                var row = this.getItem(t);
                                                console.log(row);
                                                console.log(ele('win_add_img_form').setValues(row));
                                            },
                                            "mdi-pencil": function (e, t) {
                                                console.log(ele('win_add_img').show());
                                                var row = this.getItem(t);
                                                console.log(row);
                                                console.log(ele('win_add_img_form').setValues(row));
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
                            {
                                view: "pager", id: "pagerA",
                                animate: true,
                                size: 40,
                                group: 5
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