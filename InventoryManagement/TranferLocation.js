var header_TranferLocation = function()
{
	var menuName="TranferLocation_",fd = "InventoryManagement/"+menuName+"data.php";

    function init()
    {
        
    };

    function ele(name)
    {
        return $$($n(name));
    };

    function $n(name)
    {
        return menuName+name;
    };
    
    function focus(name)
    {
        setTimeout(function(){ele(name).focus();},100);
    };
    
    function setView(target,obj)
    {
        var key = Object.keys(obj);
        for(var i=0,len=key.length;i<len;i++)
        {
            target[key[i]] = obj[key[i]];
        }
        return target;
    };

    function vw1(view,name,label,obj)
    {
        var v = {view:view,required:true,label:label,id:$n(name),name:name,labelPosition:"top"};
        return setView(v,obj);
    };

    function vw2(view,id,name,label,obj)
    {
        var v = {view:view,required:true,label:label,id:$n(id),name:name,labelPosition:"top"};
        return setView(v,obj);
    };


    function setTable(tableName, data) {
        if (!ele(tableName)) return;
        ele(tableName).clearAll();
        ele(tableName).parse(data);
        ele(tableName).filterByAll();
    };

    function loadData(btn) {
        var obj = ele('form1').getValues();
        ajax(fd, obj, 3, function (json) {
            setTable('dataT1', json.data);
        }, btn);
    };

    var cells =
        [{
            header: "TRANSFER LOCATION",
            body: {
                rows: [
                ]
            }
        }];

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_TranferLocation",
        body: 
        {
        	id:"TranferLocation_id",
        	type:"clean",
    		rows:
    		[
                { view: "tabview", cells: cells, multiview: { fitBiggest: true } },
                {
                    view: "form",
                    id: $n("form1"),
                    on:
                    {

                        "onSubmit": function (view, e) {

                            if (view.config.name == 'Serial_ID') {
                                var obj = ele('form1').getValues();
                                ajax(fd, obj, 1, function (json) {
                                    loadData();
                                    ele('Location_Code').show();
                                    webix.UIManager.setFocus(ele('Location_Code'));
                                }
                                    , null,

                                    function (json) {
                                        ele('Serial_ID').setValue('');
                                        ele('Location_Code').setValue('');
                                        ele('dataT1').clearAll();
                                        view.enable();
                                        webix.UIManager.setFocus(ele('Serial_ID'));
                                    });

                            }
                            if (view.config.name == 'Location_Code') {
                                var obj = ele('form1').getValues();
                                ajax(fd, obj, 2, function (json) {
                                    loadData();
                                    ele('save').show();
                                    webix.UIManager.setFocus(ele('Location_Code'));
                                }
                                    , null,

                                    function (json) {
                                        ele('Location_Code').setValue('');
                                        ele('dataT1').clearAll();
                                        ele('save').hide();
                                        webix.UIManager.setFocus(ele('Location_Code'));
                                    });
                            }

                            else if (webix.UIManager.getNext(view).config.type == 'line') {

                                webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));

                                view.disable();

                            }

                            else {

                                webix.UIManager.setFocus(webix.UIManager.getNext(view));

                            }

                        }

                    },
                    elements:
                        [
                            {
                                cols:
                                    [
                                        {
                                            cols: [
                                                vw1("text", 'Serial_ID', "Package Number", { width: 200,
                                                    required: true, suggest: fd + "?type=6",
                                                    width: 250,
                                                }),
                                                vw1("text", 'Location_Code', "Location Code", {
                                                    required: true, suggest: fd + "?type=5",
                                                    width: 250,
                                                    hidden:1
                                                    },
                                                ),
                                            ]

                                        },
                                        {
                                            rows: [
                                                {},
                                                vw1('button', 'save', 'Save (บันทึก)', {
                                                    type: 'form',
                                                    width: 120,
                                                    hidden: 1,
                                                    on: {
                                                        onItemClick: function () {
                                                            var obj = ele('form1').getValues();
                                                            console.log(obj);
                                                            ajax(fd, obj, 41, function (json) {
                                                                loadData();
                                                                ele('Serial_ID').setValue('');
                                                                ele('Location_Code').setValue('');
                                                                ele('save').hide();
                                                                ele('Location_Code').hide();
                                                                ele('Serial_ID').enable();
                                                                webix.UIManager.setFocus(ele('Serial_ID'));
                                                            }, null,
                                                                function (json) {
                                                                    ele('Serial_ID').setValue('');
                                                                    ele('Location_Code').setValue('');
                                                                    ele('save').hide();
                                                                    ele('Location_Code').hide();
                                                                    ele('dataT1').clearAll();
                                                                    ele('Serial_ID').enable();
                                                                    webix.UIManager.setFocus(ele('Serial_ID'));
                                                                });
                                                            webix.UIManager.setFocus(ele('Serial_ID'));
                                                        }
                                                    }
                                                }),
                                            ]
                                        },
                                        {}

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
                                            $change: function (obj) {
                                                var css = {};
                                                obj.$cellCss = css;
                                            }
                                        },
                                        columns: [
                                            { id: "NO", header: "No.", css: "rank", width: 50, sort: "int" },
                                            { id: "Serial_ID", header: ["Package Number", { content: "textFilter" }], width: 160 },
                                            { id: "Part_No", header: ["Part Number", { content: "textFilter" }], width: 250 },
                                            { id: "Part_Name", header: ["Part Name", { content: "textFilter" }], width: 350 },
                                            { id: "Type", header: ["Part Type", { content: "textFilter" }], width: 160 },
                                            { id: "Qty", header: ["Qty", { content: "textFilter" }], width: 130 },
                                            { id: "Area", header: ["Area", { content: "textFilter" }], width: 150 },
                                            { id: "Location_Code", header: ["Location Code", { content: "textFilter" }], width: 150 },
                                        ],
                                        onClick:
                                        {
                                        },
                                        on: {
                                            // "onEditorChange": function (id, value) {
                                            // }
                                            "onItemClick": function (id) {
                                                this.editRow(id);
                                            }
                                        }
                                    },
                                ]
                            },

                        ]
                }
            ],on:
            {
                onHide:function()
                {
                    
                },
                onShow:function()
                {

                },
                onAddView:function()
                {
                	init();
                }
            }
        }
    };
};