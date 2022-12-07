var header_add_user = function()
{
	return {
        view: "scrollview",
        scroll: "native-y",
        id:'header_add_user',
        body: 
        {
        	id:"add_user_id",
            type:"line",
    		rows:
    		[
                {
                    type:"line",
                    cols:
                    [
                        {
                            rows:
                            [
                                {  
                                    view:"form",
                                    paddingY:0,
                                    id:"add_user_form1",
                                    elements:
                                    [
                                        {
                                            cols:
                                            [
                                                { 
                                                    
                                                    rows:
                                                    [
                                                        {
                                                            cols:
                                                            [
                                                                {view:"text",id:"add_user_userName",name:"userName",label:"User Name (ชื่อเข้าใช้งาน)",labelPosition:"top",width:180},
                                                                {view:"text",id:"add_user_fName",name:"fName",label:"First Name (ชื่อ)",labelPosition:"top"},
                                                                {view:"text",id:"add_user_lName",name:"lName",label:"Last Name (สกุล)",labelPosition:"top"},
                                                                {
                                                                  view:"combo",id:"add_user_roleName",width:200,name:"role",label:"Role Name",labelPosition:"top",yCount:"10",suggest:"userM/role_getRole.php?type=1",on:
                                                                    {
                                                                        onChange: function(value)
                                                                        {
                                                                            
                                                                        }
                                                                    }
                                                                },
                                                            ]
                                                        },
                                                        {
                                                            cols:
                                                            [
                                                                
                                                                {view:"button",value:"Save (บันทึกข้อมูล)",css:'webix_primary', id:"add_user_save",on:
                                                                    {
                                                                        onItemClick:function(id, e)
                                                                        {
                                                                            if($$('add_user_form1').validate())
                                                                            {
                                                                                var btn = this;
                                                                                webix.confirm(
                                                                                {
                                                                                    title:"<b>ข้อความจากระบบ</b>",ok:'ใช่',cancel:"ไม่",text:"คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",callback:function(result)
                                                                                    {
                                                                                        if(result)
                                                                                        {
                                                                                            btn.disable();
                                                                                            $.post("userM/addUser_insert.php",{obj:$$('add_user_form1').getValues(),type:1})
                                                                                            .done(function( data ) 
                                                                                            {
                                                                                                btn.enable();
                                                                                                var data = eval('('+data+')'),dataT1 = $$('add_user_T1');
                                                                                                if(data.ch == 1)
                                                                                                {
                                                                                                    webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'บันทึกสำเร็จ',callback:function(){}});
                                                                                                    dataT1.clearAll();
                                                                                                    dataT1.parse(data.data,"jsarray");
                                                                                                    $$('add_user_clear').callEvent("onItemClick", []);
                                                                                                }
                                                                                                else if(data.ch == 2){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                                                                                                else if(data.ch == 9){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                                                                                                else if(data.ch == 10){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){window.open("login.php","_self");}});}
                                                                                            });
                                                                                        }
                                                                                    }
                                                                                });
                                                                            }
                                                                            else webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'กรุณาป้อนข้อมูล<font color="#ce5545"><b>ในช่องสีแดง</b></font>ให้ครบ',callback:function(){$$('cancel_grn_grn').focus();}});
                                                                        }
                                                                    }
                                                                },
                                                                {view:"button",value:"Refresh (โหลดใหม่)",id:"add_user_refresh",on:
                                                                    {
                                                                        onItemClick:function(id, e)
                                                                        {
                                                                            add_user_loadData();
                                                                        }
                                                                    }
                                                                },
                                                                {view:"button",value:"Clear (ล้างข้อมูล)",type:"danger",id:"add_user_clear",on:
                                                                    {
                                                                        onItemClick:function(id, e)
                                                                        {
                                                                            var dataT1 = $$('add_user_T1'),role=$$('add_user_roleName');
                                                                            role.blockEvent();
                                                                            role.setValue('');
                                                                            role.unblockEvent();
                                                                            $$('add_user_userName').setValue('');
                                                                            $$('add_user_fName').setValue('');
                                                                            $$('add_user_lName').setValue('');
                                                                            role.getPopup().getBody().load("userM/add_user_getRole.php?type=1");
                                                                            $$('add_user_roleName2').setValue('');
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
                                                                    view:"datatable", id:"add_user_T1",
                                                                    scheme:{
                                                                        $change:function(item){
                                                                            if (parseInt(item.data7) == 0)
                                                                                item.$css = "my_disabled";
                                                                        }
                                                                    },
                                                                    columns:[
                                                                        { id:"data0",header:"#",width:50},
                                                                        { id:"data1",header:"Image (รูป)",  template:function(obj){if (obj.data1) return "<img src='"+obj.data1+"' style='cursor:pointer; width:60px; height:60px;'>";return "";}, width:80},
                                                                        { id:"data2",header:"User Name",width:150},
                                                                        { id:"data3",header:["First Name (ชื่อ)",{content:"textFilter"}],width:200},
                                                                        { id:"data4",header:["Last Name (สกุล)",{content:"textFilter"}] , width:200},
                                                                        { id:"data5",header:["Role",{content:"textFilter"}], width:200},
                                                                        { id:"data8",header: "&nbsp;",width: 35,
                                                                          template: "<span style='color:#777777;cursor:pointer;' class='mdi mdi-pencil'></span>"
                                                                        },
                                                                        { id:"data9",header: "&nbsp;",width: 35,
                                                                          template: "<span style='color:#777777;cursor:pointer;' class='mdi mdi-key'></span>"
                                                                        }
                                                                    ],
                                                                    autoheight:true,multiselect:true,select:"row",
                                                                    autowidth:true,navigation:true,resizeColumn:true,
                                                                    rowHeight:60,hover:"myhover",datatype:"jsarray",
                                                                    onClick:
                                                                    {
                                                                      "mdi-pencil":function(e,t)
                                                                      {
                                                                        $$('add_user_win_edituser').show();
                                                                        $$('add_user_win_form1').setValues($$('add_user_T1').getItem(t));
                                                                      },
                                                                      "mdi-key":function(e,t)
                                                                      {
                                                                        var userID = $$('add_user_T1').getItem(t).data6;
                                                                        webix.confirm(
                                                                        {
                                                                          title: "กรุณายืนยัน",ok:"ใช่", cancel:"ไม่",text:"คุณต้องการ <b>Reset Password</b><br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                          callback:function(res)
                                                                          {
                                                                            if(res)
                                                                            {
                                                                                $.post("userM/addUser_update.php",{obj:{data6:userID},type:3})
                                                                                .done(function( ddd ) 
                                                                                {
                                                                                  var dataT1 = $$("add_user_T1"),data=eval('('+ddd+')');
                                                                                  if(data.ch == 1)
                                                                                  {
                                                                                    webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
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
                                                                    }
                                                                }
                                                            ]
                                                        }
                                                    ]
                                                },{}
                                            ]
                                        },
                                        {
                                            
                                        }
                                    ],
                                    rules:
                                    {
                                        userName:webix.rules.isNotEmpty,
                                        fName:webix.rules.isNotEmpty,
                                        lName:webix.rules.isNotEmpty,
                                        role:webix.rules.isNotEmpty
                                    }
                                }
                            ]
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
                    window['add_user_loadData'] = function()
                    {
                        $.post("userM/addUser_data.php", {type:1})
                        .done(function( data ) 
                        {
                            var data = eval('('+data+')');
                            if(data.ch == 1)
                            { 
                                var dataT1 = $$('add_user_T1');
                                dataT1.clearAll();
                                dataT1.parse(data.data,"jsarray");
                            }
                            else if(data.ch == 2){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                            else if(data.ch == 9){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                            else if(data.ch == 10){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){window.open("login.php","_self");}});}
                        });
                    };
                    add_user_loadData();

                    webix.ui(
                    {
                        view:"window",id:"add_user_win_edituser",modal:1,
                        head:"Edit User (แก้ใขข้อมูล)",top:50,position:"center",
                        body:
                        {
                          view:"form", scroll:false,id:"add_user_win_form1",width:700,
                          elements:
                          [
                            {
                              cols:
                              [
                                {
                                  rows:
                                  [
                                    {view:"text",id:"add_user_fName_e",name:"data3",label:"First Name (ชื่อ)",labelPosition:"top"},
                                    {view:"text",id:"add_user_lName_e",name:"data4",label:"Last Name (สกุล)",labelPosition:"top"},
                                    {view:"text",labelAlign:"right",label:'id',id:"add_user_id_e",name:"data6",value:"",hidden:1},
                                    {view:"checkbox", label:"Active",id:"add_user_active_e",name:"data7"}
                                  ]
                                }
                              ]
                            },
                            {
                              cols:
                              [
                                {},
                                {
                                  view:"button", value:"OK",type:"form", width:150, css:'webix_primary' ,click:function()
                                  {
                                    if($$('add_user_win_form1').validate())
                                    {
                                      webix.confirm(
                                      {
                                        title: "กรุณายืนยัน",ok:"ใช่", cancel:"ไม่",text:"คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                        callback:function(res)
                                        {
                                          if(res)
                                          {
                                            $.post("userM/addUser_update.php", {obj:$$('add_user_win_form1').getValues(),type:2})
                                            .done(function( ddd ) 
                                            {
                                              var dataT1 = $$("add_user_T1"),data=eval('('+ddd+')');
                                              if(data.ch == 1)
                                              {
                                                $$('add_user_clear_e').callEvent("onItemClick", []);
                                                /*webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'บันทึกสำเร็จ',callback:function(){}});*/
                                                webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
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
                                    else
                                    {
                                       webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'กรุณาป้อนข้อมูล<font color="#ce5545"><b>ในช่องสีแดง</b></font>ให้ครบ',callback:function(){}});
                                    }
                                    
                                  }
                                },
                                {
                                  view:"button", value:"Cancel",type:"danger",id:"add_user_clear_e", width:150,on:
                                  {
                                    onItemClick:function(id)
                                    {
                                        $$('add_user_win_edituser').hide();
                                        $$('add_user_fName_e').setValue('');
                                        $$('add_user_lName_e').setValue('');
                                    }
                                  }
                                }
                              ]
                            }
                          ],
                          rules:
                          {
                          }
                        }
                    });

                }
            }
        }
    };
};