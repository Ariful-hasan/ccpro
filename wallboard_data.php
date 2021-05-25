<?php
echo '{"DataFrames":{"LiveCallStatus_AllSkills":{"head":"Live Call Status","title":"All Skills","display_type":"list","duration":10,"cell_color":"#123123","cell_bgcolor":"#999999","data":[[{"label":"Calls in Queue"},{"label":"-"}],[{"label":"Calls in Service"},{"label":"-"}],[{"label":"Maximum Hold Time"},{"label":"-"}],[{"label":"Maximum Talk Time"},{"label":"-"}],[{"label":"Available Agents"},{"label":"-"}],[{"label":"Agents AUX Out"},{"label":"-"}]]},"CallSummary_From00Hour":{"head":"Call Summary","title":"From 00 Hour","display_type":"list","duration":16,"cell_color":"#123123","cell_bgcolor":"#999999","data":[[{"label":"Calls Offered"},{"label":"303"}],[{"label":"Calls Answered"},{"label":"217"}],[{"label":"Calls Abandoned"},{"label":"86"}],[{"label":"AVG Hold Time"},{"label":"0:16"}],[{"label":"AVG Service Time"},{"label":"1:33"}],[{"label":"Service Level"},{"label":"36%"}]]},"CallSummary_Last4Hours":{"head":"Call Summary","title":"Last 4 Hours","display_type":"list","duration":16,"cell_color":"#123123","cell_bgcolor":"#999999","data":[[{"label":"Calls Offered"},{"label":"76"}],[{"label":"Calls Answered"},{"label":"61"}],[{"label":"Calls Abandoned"},{"label":"15"}],[{"label":"AVG Hold Time"},{"label":"0:13"}],[{"label":"AVG Service Time"},{"label":"0:58"}],[{"label":"Service Level"},{"label":"30.3%"}]]},"CallSummary_CurrentHour":{"head":"Call Summary","title":"Current Hour","display_type":"list","duration":16,"cell_color":"#123123","cell_bgcolor":"#999999","data":[[{"label":"Calls Offered"},{"label":"10"}],[{"label":"Calls Answered"},{"label":"6"}],[{"label":"Calls Abandoned"},{"label":"4"}],[{"label":"AVG Hold Time"},{"label":"0:30"}],[{"label":"AVG Service Time"},{"label":"2:41"}],[{"label":"Service Level"},{"label":"40%"}]]},"HourlyServiceLevel_AllSkill":{"head":"Hourly Service Level","title":"All Skill","display_type":"list","duration":16,"cell_color":"#123123","cell_bgcolor":"#999999","data":[[{"label":"Current Hour"},{"label":"25%"}],[{"label":"Last Hour"},{"label":"26.7%"}],[{"label":"Hour - 11"},{"label":"11.1%"}],[{"label":"Hour - 10"},{"label":"71.4%"}],[{"label":"Hour - 09"},{"label":"33.3%"}],[{"label":"Hour - 08"},{"label":"-"}]]},"OutboundCalls_Currentcallsbydisposition":{"head":"Outbound Calls","title":"Current calls by disposition","display_type":"list","duration":16,"cell_color":"#123123","cell_bgcolor":"#999999","data":[[{"label":"Total Calls"},{"label":"-"}]]}},"Notices":[{"title":"gPlex","text":"Wallboard has been launched.","img":"blank.png","duration":30},{"title":"Employee","text":"Agent of the week is Mila.","img":"1001.png","duration":30}],"ScrollText":["Powered by gPlex Contact Center.","Welcome to Genuity Systems Ltd."],"Videos":{"loop":"True","wait_time":10,"data":[{"url":"genu.flv","source":"Local"}]},"time":'.time().'}';
exit;

include('/usr/local/gplexcc/regsrvr/engine/wallboard.php');
generate_json_file();
$file = file_get_contents("./dashvar/wall_var.txt");
if($page) {
      $response = json_decode($file);
      $time = $response->time;
      $response = $response->DataFrames->$page;
      $response->time = $time;
      $file = json_encode($response);
}
echo $file;
exit;
