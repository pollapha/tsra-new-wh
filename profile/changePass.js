
var header_changepass = function()
{
    return {
        paddingX :5,
        paddingY :5,
        rows:
        [
            {
                paddingY: 1,
                cols:
                [
                    {
                        view:"form",
                        id:'changepass_form',
                        elements:
                        [
                            {view:"text", type:'password',label:'Old Password (รหัสเก่า)', labelPosition:"top",id:"changepass_pass1",name:"pass1"},
                            {view:"text", type:'password',label:'New Password (รหัสใหม่)', labelPosition:"top",id:"changepass_pass2",name:"pass2"},
                            {view:"text", type:'password',label:'New Password (รหัสใหม่)', labelPosition:"top",id:"changepass_pass3",name:"pass3"},
                            {cols:[
                                {view:"button", value:"OK (ตกลง)", type:"form",on:
                                    {
                                        onItemClick:function(id, e)
                                        {
                                            var btn = this;
                                            webix.confirm(
                                            {
                                                title:"<b>ข้อความจากระบบ</b>",ok:'ใช่',cancel:"ไม่",text:"คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",callback:function(result)
                                                {
                                                    if(result)
                                                    {
                                                        btn.disable();
                                                        var obj = $$('changepass_form').getValues();
                                                        $.post("profile/changePass_update.php",{obj:obj,type:1})
                                                        .done(function( data ) 
                                                        {
                                                            btn.enable();
                                                            var data = eval('('+data+')');
                                                            if(data.ch == 1)
                                                            {
                                                                $$('changepass_form').setValues({pass1:'',pass2:'',pass3:''});
                                                                webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'บันทึกสำเร็จ',callback:function(){}});
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
                            ]}
                        ],
                        rules:
                        {

                            $obj:function(data)
                            {
                                if(data.pass1 == '' || data.pass2 == '' || data.pass3 == '') 
                                {
                                    webix.message({ type:"error",expire:7000, text:"กรุณากรอกข้อมูลให้ครบ",align:"center"});
                                    return false;
                                }
                                else if (data.pass3 != data.pass2){
                                    webix.message({ type:"error",expire:7000, text:"คุณกรอก Password ไม่เหมื่อนกัน" });
                                    return false;
                                }
                                else return true;
                            }
                        }
                    },
                    {},{}
                ]
            }
        ]
    };
};