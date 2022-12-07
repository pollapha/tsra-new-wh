<?php
$path = $_SERVER['PHP_SELF'];
$getRootUrl = "http://".$_SERVER['HTTP_HOST'].substr($path, 0,strpos($path, '/', 1));
$imageCurrentUser = $_SESSION['xxxImage'];
$nameCurrentUser = $_SESSION['xxxFName'];
$header_menuObj = array();
function requireToVar($file){
    ob_start();
    require($file);
    return ob_get_clean();
}

$header_menuByRole = array();
foreach ($checkPageDataObject as $key => $value)
{
    if(is_bool(strpos($key,'_1_')) === true)
    {
        if(!isset($_SESSION['xxxRole']->{$key}[0])) continue;
        if($_SESSION['xxxRole']->{$key}[0] == 1)
        {
            $chRole = array();
            for($i=0,$len=count($checkPageDataObject->{$key});$i<$len;$i++)
            {
                $id = $checkPageDataObject->{$key}[$i]->id;
                $value = $checkPageDataObject->{$key}[$i]->value;
                $icon = $checkPageDataObject->{$key}[$i]->icon;
                $details = $checkPageDataObject->{$key}[$i]->details;
                if($_SESSION['xxxRole']->{$id}[0])
                {
                    $chRole[] =
                    '{
                        id: "'.$id.'",
                        value: "'.$value.'",
                        icon: "'.$icon.'",
                        details: "'.$details.'"
                    }';
                }
            }
            $header_menuByRole[] = preg_replace('/\s{2,}/', '','
            {
                id:"'.$key.'",
                value: "'.$checkPageDataObject->{$key.'_1_'}.'",
                icon: "'.$checkPageDataObject->{$key.'_icon_'}.'",
                open: 1,
                data: 
                [
                    '.join(',',$chRole).'
                ]
            }');
        }
    }
}

$header_menuByRole = join(',',$header_menuByRole);

echo 'var _getRootUrl="'.$getRootUrl.'"';
echo ', _imageCurrentUser="'.$imageCurrentUser.'"';
echo ', _nameCurrentUser="'.$nameCurrentUser.'"';
echo ', _header_menuByRole=['.$header_menuByRole.'];';

if($DEBUG_MODE)
{
    include('startPages.php');
    for($i=0,$len=count($loadPage);$i<$len;$i++)
    {
         readfile($loadPage[$i]);
    }
}
else 
{
    
    if($APCU_MODE)
    {
        if(!apcu_exists($TTV_CACHE_PAGE_JS))
        {
            $strarray = file_get_contents('pagesCache.js');
            echo $strarray;
            apcu_add($TTV_CACHE_PAGE_JS, $strarray);
        }
        else echo apcu_fetch($TTV_CACHE_PAGE_JS);
    }
    else readfile('pagesCache.js');
    
}
$mysqli->close();
?>