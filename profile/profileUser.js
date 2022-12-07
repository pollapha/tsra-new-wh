
var header_profileUser = function()
{
    return {
        view: "scrollview",
        scroll: "native-y",
        id:'header_profileUser',
        body: 
        {
            id:"header_id",
            type:"clean",
            rows:
            [
                {
                    cols:
                    [
                        {
                            view:"form",
                            id:'profileUser_form',
                            elements:
                            [
                                {
                                    rows:
                                    [
                                        {
                                            view:"datatable", id:"profileUser_T1",
                                            columns:[
                                                { id:"data1",header:"Image (รูป)",  template:function(obj){if (obj.data1) return "<img src='"+obj.data1+"' style='cursor:pointer; width:80px; height:100px;'>";return "";}, width:100},
                                                { id:"data2",header:"First Name (ชื่อ)",width:200},
                                                { id:"data3",header:"Last Name (สกุล)" , width:200}
                                            ],
                                            autoheight:true,
                                            autowidth:true,
                                            rowHeight:80,
                                            on:
                                            {
                                                onItemClick:function(id)
                                                {
                                                    if(id.column == "data1") $$("profileUser_upload").fileDialog({ rowid : id.row });
                                                }
                                            }
                                        }
                                    ]
                                }
                            ]
                        },{},{}
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

                    $.post("profile/profileUser_data.php", {type:1})
                    .done(function( data ) 
                    {
                        var data = eval('('+data+')');
                        if(data.ch == 1)
                        { 
                            var dataT1 = $$('profileUser_T1');
                            dataT1.clearAll();
                            dataT1.parse(data.data,"jsarray");
                        }
                        else if(data.ch == 2){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                        else if(data.ch == 9){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                        else if(data.ch == 10){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){window.open("login.php","_self");}});}
                    });

                    webix.ui({
                        id:"profileUser_upload",
                        view:"uploader",name:"uploader",multiple:false,autosend:true,
                        upload:"profile/profileUser_uploadImage.php",
                        on:{
                            onBeforeFileAdd:function(item){
                                var type = item.type.toLowerCase();
                                if (type != "jpg" && type != "png"){
                                    webix.message("Only PNG or JPG images are supported");
                                    return false;
                                }
                            },
                            onFileUpload:function(item){
                            },
                            onFileUploadError:function(item){
                                 webix.alert("Error during file upload");
                            },
                            onUploadComplete:function(data)
                            {
                                var dataT1 = $$('profileUser_T1'),image;
                                dataT1.clearAll();
                                dataT1.parse(data.sname,"jsarray");
                                $$('person_template').parse({id:data.sname[0][1].split('/')[2],name:data.sname[0][4]});
                            }
                        },
                        apiOnly:true
                    });

                }
            }
        }
    };
};

