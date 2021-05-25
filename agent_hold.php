<?PHP 

$fail="404\n";

if(!$agent_id || !$param) {
    echo "$fail";
    exit;
}
include("/usr/local/gplexcc/regsrvr/engine/agent_hold.php");
if(do_hold($agent_id,$param))
   echo "200|OK";
else echo "404|";
exit;
?>
