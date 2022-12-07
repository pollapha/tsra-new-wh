var header_userRole = function()
{
	return {
        view: "scrollview",
        scroll: "native-y",
        id:'header_userRole',
        body: 
        {
        	id:"userRole_id",
        	type:"clean",
    		rows:
    		[
                {
                    view:"form",
                    paddingY:0,
                    id:"userRole_form1",
                    elements:
                    [
                        {
                            cols:
                            [
                                { 
                                    view:"fieldset", label:"Update Role (แก้ใขบทบาท)", body:
                                    {
                                        rows:
                                        [
                                            {
                                              view:"combo",id:"userRole_roleName",width:200,name:"role",label:"Role Name",labelPosition:"top",yCount:"10",suggest:"userM/role_getRole.php?type=1",on:
                                                {
                                                    onChange: function(value)
                                                    {
                                                        
                                                    }
                                                }
                                            },
                                            {
                                                cols:
                                                [
                                                    
                                                    {view:"button",value:"Save (บันทึกข้อมูล)",css:'webix_primary',id:"userRole_save",on:
                                                        {
                                                            onItemClick:function(id, e)
                                                            {
                                                                var dataT1 = $$('userRole_T1'),objArray = dataT1.getSelectedId(true),len=objArray.length,btn=$$('userRole_save');
                                                                if(dataT1.count() > 0)
                                                                {
                                                                    if(len)
                                                                    {
                                                                        if($$('userRole_roleName').getValue() != '')
                                                                        {
                                                                            webix.confirm(
                                                                            {
                                                                                title:"<b>ข้อความจากระบบ</b>",ok:'ใช่',cancel:"ไม่",text:"คุณต้องการลบข้อมูลที่คุณเลือก<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",callback:function(result)
                                                                                {
                                                                                    if(result)
                                                                                    {
                                                                                        btn.disable();
                                                                                        var idConcat = [];
                                                                                        for(var i=-1;++i<len;)
                                                                                        {
                                                                                          idConcat[idConcat.length] = dataT1.getItem(objArray[i].row).data5;
                                                                                        }
                                                                                        $.post("userM/userRole_update.php",{obj:idConcat.join(','),param:$$('userRole_roleName').getValue(),type:1})
                                                                                        .done(function( data ) 
                                                                                        {
                                                                                            btn.enable();
                                                                                            var data = eval('('+data+')');
                                                                                            if(data.ch == 1)
                                                                                            {
                                                                                                webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'บันทึกสำเร็จ',callback:function(){}});
                                                                                                dataT1.clearAll();
                                                                                                dataT1.parse(data.data,"jsarray");
                                                                                            }
                                                                                            else if(data.ch == 2){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                                                                                            else if(data.ch == 9){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                                                                                            else if(data.ch == 10){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){window.open("login.php","_self");}});}
                                                                                        });
                                                                                    }
                                                                                }
                                                                            });
                                                                        }
                                                                        else{webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'กรุณาเลือกกฏ',callback:function(){}});}
                                                                    }
                                                                    else{webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'กรุณาเลือกข้อมูลในตาราง',callback:function(){}});}
                                                                }
                                                                else{webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'ไม่พบข้อมูลในตาราง',callback:function(){}});}
                                                            }
                                                        }
                                                    },
                                                    {view:"button",value:"Refresh (โหลดใหม่)",id:"userRole_refresh",on:
                                                        {
                                                            onItemClick:function(id, e)
                                                            {
                                                                userRole_loadData();
                                                            }
                                                        }
                                                    },
                                                    {view:"button",value:"Clear (ล้างข้อมูล)",type:"danger",id:"userRole_clear",on:
                                                        {
                                                            onItemClick:function(id, e)
                                                            {
                                                                var dataT1 = $$('userRole_T1'),role=$$('userRole_roleName');
                                                                dataT1.clearAll();
                                                                role.blockEvent();
                                                                role.setValue('');
                                                                role.unblockEvent();
                                                                role.getPopup().getBody().load("userM/userRole_getRole.php?type=1");
                                                            }
                                                        }
                                                    }
                                                ]
                                            },
                                            {
                                                paddingX:2,
                                                rows:
                                                [
                                                    {
                                                        view:"datatable", id:"userRole_T1",
                                                        columns:[
                                                            { id:"data1",header:"Image (รูป)",  template:function(obj){if (obj.data1) return "<img src='"+obj.data1+"' style='cursor:pointer; width:60px; height:60px;'>";return "";}, width:80},
                                                            { id:"data2",header:"First Name (ชื่อ)",width:200},
                                                            { id:"data3",header:"Last Name (สกุล)" , width:200},
                                                            { id:"data4",header:"Role" , width:200}
                                                        ],
                                                        autoheight:true,multiselect:true,select:"row",
                                                        autowidth:true,navigation:true,resizeColumn:true,
                                                        rowHeight:60,hover:"myhover",datatype:"jsarray"
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                },{}
                            ]
                        },
                        {
                            
                        }
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


                    window['userRole_loadData'] = function()
                    {
                        $.post("userM/userRole_data.php", {type:1})
                        .done(function( data ) 
                        {
                            var data = eval('('+data+')');
                            if(data.ch == 1)
                            { 
                                var dataT1 = $$('userRole_T1');
                                dataT1.clearAll();
                                dataT1.parse(data.data,"jsarray");
                            }
                            else if(data.ch == 2){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                            else if(data.ch == 9){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                            else if(data.ch == 10){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){window.open("login.php","_self");}});}
                        });
                    };
                    userRole_loadData();

                }
            }
        }
    };
};