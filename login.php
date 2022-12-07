<?php

if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('start.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="shortcut icon" href="images/favicon.ico"> 
        <link rel="stylesheet" href="codebase/all.min.css" type="text/css" media="screen" charset="utf-8">
        <script src="codebase/all.min.js"></script>
		<style>
			#areaA, #areaB{
				margin: 30px;
			}
		</style>
		<title>LOGIN</title>
	</head>
	<body>

		<form>
			<div id="areaA"></div>
			<div id="areaB"></div>
		</form>
	

		<script type="text/javascript" charset="utf-8">
		webix.ui({
				view:"window",
				height:250,
			    width:400,
			    top:100,
			    head:"<b>Sign In</b>",
			    position:"center",
				body:
				{
					rows:
					[
						{
							view:"form",id:"form",
							elements: 
							[
								{
									cols:
									[
										{},
										{
    									    view: "label",
    									    label: "<img class=\'photo\' src=\'images/predator-logo.gif\' height=\'40\' />",width:144,
    									},{}
									]
								},
								{ view:"text", label:'User Name',labelWidth:85, name:"login",id:"user" },
								{ view:"text", label:'Password',labelWidth:85,type:'password', name:"email",id:"pass"},
								{ view:"button",type:'form', value: "Submit", click:btnClick}
							],
							rules:{
								"email":webix.rules.isNotEmpty,
								"login":webix.rules.isNotEmpty
							}
						}
					]
				}
		}).show();

		webix.UIManager.addHotKey("Enter", function(e) { 
			var obj = webix.UIManager.getNext(e);
			webix.UIManager.setFocus(obj);
		    return false; 
		}, $$('user'));
		webix.UIManager.addHotKey("Enter", function(e) { 
			btnClick();
		    return false; 
		}, $$('pass'));

		function btnClick () 
		{
			if ($$("form").validate())
			{
				var user=$$("user").getValue(),pass=$$("pass").getValue();
				$.post( "chPass.php", { user:user, pass:pass})
  					.done(function( data ) {
  					var data = eval('(' + data + ')');
         		 	if(data.ch == 1)
         		 	{
         		 	  window.open(window.location.href.replace('login.php',''),"_self");
         		 	}
         		 	else
         		 	{
						webix.alert({type:"alert-error",title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data});
         		 	}
  				});
				
			}
		}
		</script>
	</body>
</html>