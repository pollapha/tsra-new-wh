<?php
	$loadPage = array
	(
	    'homepage.js','404.js','profile/changePass.js','profile/profileUser.js'
	);
	$header_menuObj = array
	(
	    'history:"header_history"',
	    'profileUser:"header_profileUser"',
	    'homePage:"header_homePage"',
	    '404:"header_404"',
	    'changepass:"header_changepass"'
	);
	if($re = $mysqli->query("SELECT menu_menuUse,menu_url from tbl_menu where menu_url <> ''"))
	{
	    for($i=0,$len=$re->num_rows;$i<$len;$i++)
	    {
	        $row = $re->fetch_array(MYSQLI_NUM);
	        $loadPage[] = $row[1];
	        $header_menuObj[] = '"'.$row[0].'":"header_'.$row[0].'"';
	    }
	}
	echo 'var header_menuObj = {'.join(',',$header_menuObj).'};';
?>