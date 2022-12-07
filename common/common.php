<?php

function getPartID($mysqli,$data)
{
	$sql = "SELECT BIN_TO_UUID(Part_ID,true) as ID from tbl_part_master where Part_No='$data' limit 1;";
	
    $re1 = sqlError($mysqli,__LINE__,$sql);
    if($re1->num_rows == 0) closeDBT($mysqli,2,'ERROR LINE '.__LINE__."<br>ไม่พบข้อมูล Part ".$data);
    return  $re1->fetch_array(MYSQLI_ASSOC)['ID'];
}

function getCustomerID($mysqli,$data)
{
	$sql = "SELECT BIN_TO_UUID(Customer_ID,true) as ID from tbl_customer_master where Customer_Code='$data' limit 1;";
	
    $re1 = sqlError($mysqli,__LINE__,$sql);
    if($re1->num_rows == 0) closeDBT($mysqli,2,'ERROR LINE '.__LINE__."<br>ไม่พบข้อมูล Customer ".$data);
    return  $re1->fetch_array(MYSQLI_ASSOC)['ID'];
}

function getPartName($mysqli,$data)
{
    $part = explode('|', $data);
			//var_dump(explode('|', $Part_No));
			$data = $part[0];

	$sql = "SELECT Part_Name from tbl_part_master where Part_No='$data' limit 1;";
	
    $re1 = sqlError($mysqli,__LINE__,$sql);
    if($re1->num_rows == 0) closeDBT($mysqli,2,'ERROR LINE '.__LINE__."<br>ไม่พบข้อมูล Part".$data);
    return  $re1->fetch_array(MYSQLI_ASSOC)['Part_Name'];
}

function selectColumnFromArray($dataAr,$columnAr)
{
    $returnData = array();
    for($i=0,$len=count($dataAr);$i<$len;$i++)
    {
        $ar = array();
        for($i2=0,$len2=count($columnAr);$i2<$len2;$i2++)
        {
            $ar[$columnAr[$i2]] = $dataAr[$i][$columnAr[$i2]];
        }
        $returnData[] = $ar;
    }
    return $returnData;
}

function group_by($key, $data) 
{
    $result = array();

    foreach($data as $val) {
        if(array_key_exists($key, $val)){
            $result[$val[$key]][] = $val;
        }else{
            
        }
    }

    return $result;
}


?>
