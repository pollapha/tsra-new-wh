<?php
include('start.php');
include('phplib/php-html-css-js-minifier.php');
include('php/connection.php');
ob_start();
include('startPages.php');
for($i=0,$len=count($loadPage);$i<$len;$i++)
{
    echo file_get_contents($loadPage[$i]);
}
$mysqli->close();

$buffer = ob_get_clean();

// Remove comments
$buffer = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', '', $buffer);
// Remove space after colons
$buffer = str_replace(': ', ':', $buffer);
// Remove space before equal signs
$buffer = str_replace(' =', '=', $buffer);
// Remove space after equal signs
$buffer = str_replace('= ', '=', $buffer);
// Remove whitespace
$buffer = str_replace(array("\r\n\r\n", "\n\n", "\r\r", '\t', '  ', '    ', '    '), '', $buffer);

$txt = minify_js($buffer);
if($APCU_MODE)
{
	if(apcu_exists($TTV_CACHE_PAGE_JS))
	{
		apcu_delete($TTV_CACHE_PAGE_JS);
		apcu_add($TTV_CACHE_PAGE_JS, $txt);
	}
	else apcu_add($TTV_CACHE_PAGE_JS, $txt);
}
	
file_put_contents('pagesCache.js', $txt);

// file_put_contents('cache.js', minify_js(ob_get_clean()));

?>