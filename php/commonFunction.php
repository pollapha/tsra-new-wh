<?php

function getSuggestPartMaster($mysqli,$filler)
{

}

function getSuggestSupplierMaster($mysqli,$line,$filler)
{
    $sql = "SELECT bin_to_uuid(Supplier_ID,true) id,concat(Supplier_Code,' | ',Supplier_Name) `value`
    from tbl_supplier_master where Supplier_Code like '%$filler%' or Supplier_Name like '%$filler%' limit 10";
    return sqlError($mysqli,$line,$sql,1);
}

function getSupplierCodeFromSuggestSupplierMaster($suggest)
{    
    $suggestAr = explode(' | ',$suggest);
    return $suggestAr[0];
}

?>