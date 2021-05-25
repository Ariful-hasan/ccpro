<?php
$debug = false;
if ($debug){
    error_reporting(E_ALL);
}
define('HOST_NAME',"192.168.10.64");
define('PORT',"8090");
$null = NULL;

$GLOBALS['email_data'] = [];
$GLOBALS['email_start_time'] = [];
$mainobj = new EmailDataHandler();

$socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socketResource, 0, PORT);
socket_listen($socketResource);

$clientSocketArray = array($socketResource);
while (true) {
    $newSocketArray = $clientSocketArray;
    socket_select($newSocketArray, $null, $null, 0, 10);

    if (in_array($socketResource, $newSocketArray)) {
        $newSocket = socket_accept($socketResource);
        $clientSocketArray[] = $newSocket;

        $header = socket_read($newSocket, 1024);
        $mainobj->doHandshake($header, $newSocket, HOST_NAME, PORT);

        socket_getpeername($newSocket, $client_ip_address);
        $connectionACK = $mainobj->newConnectionACK($client_ip_address);

        $mainobj->send($connectionACK);

        $newSocketIndex = array_search($socketResource, $newSocketArray);
        unset($newSocketArray[$newSocketIndex]);
    }

    foreach ($newSocketArray as $newSocketArrayResource) {
        while(socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1){
            $now = time();
            $socketMessage = $mainobj->unseal($socketData);
            $messageObj = json_decode($socketMessage);
            if (is_object($messageObj)){
                $messageObj->tstamp = $now;
            }
            $chat_box_message = $mainobj->createEmailMessage($messageObj, $GLOBALS['email_data'], $GLOBALS['email_start_time']);
            var_dump($chat_box_message['result']);

            if (!empty($chat_box_message['result'])){
                unset($GLOBALS['email_data']);
                $GLOBALS['email_data'] = $chat_box_message['global'];

                unset($GLOBALS['email_start_time']);
                $GLOBALS['email_start_time'] = $chat_box_message['stime'];
            }
            $mainobj->send($chat_box_message['result']);
            break 2;
        }

        $socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
        if ($socketData === false) {
            socket_getpeername($newSocketArrayResource, $client_ip_address);
            $connectionACK = $mainobj->connectionDisconnectACK($client_ip_address);
            $mainobj->send($connectionACK);
            $newSocketIndex = array_search($newSocketArrayResource, $clientSocketArray);
            unset($clientSocketArray[$newSocketIndex]);
            var_dump("UNSET");
        }
    }
}
echo 'socket close';socket_close($socketResource);


class EmailDataHandler {

    function createEmailMessage($dataObj, $arr, $start_info) {
        if (!empty($dataObj->method) && $dataObj->method=="PING"){
            if (empty($arr[$dataObj->email_id])){
                $arr[$dataObj->email_id] = $dataObj;
            } else {
                unset($arr[$dataObj->email_id]);
                $arr[$dataObj->email_id] = $dataObj;
            }

            if (empty($start_info[$dataObj->email_id])){
                $start_info[$dataObj->email_id] = $dataObj->tstamp;
            }

        } elseif (!empty($dataObj->method) && $dataObj->method=="CLOSE") {
            unset($arr[$dataObj->email_id]);
            unset($start_info[$dataObj->email_id]);
        }

        $arr = $this->clearOldData($arr, $dataObj->tstamp);
        $start_info = $this->getValidTimeData($arr, $start_info);
        $result = $this->calculateData($arr, $start_info);
        $result = $this->seal(json_encode($result));

        return array("result"=>$result, "global"=>$arr, "stime"=>$start_info);
    }
    function getValidTimeData($validData, $timeData){
        if (!empty($validData) && !empty($timeData)) {
            foreach ($validData as $key){
                if (!array_key_exists($key->email_id, $timeData)){
                    unset($timeData[$key->email_id]);
                }
            }
        }
        return $timeData;
    }


    function calculateData($store, $start_info){
        $response = [];
        if ($store){
            foreach ($store as $key){
                if (empty($response['AGENT'][$key->agent_id])){
                    $response['AGENT'][$key->agent_id] = 1;
                } else {
                    $response['AGENT'][$key->agent_id] += 1;
                }

                if (empty($response['SKILL'][$key->skill_id])){
                    $response['SKILL'][$key->skill_id] = 1;
                } else {
                    $response['SKILL'][$key->skill_id] += 1;
                }
                /*
                 * Update pull time
                 * */
                $response['PT'][$key->agent_id][$key->email_id] = $key->tstamp - $start_info[$key->email_id];
            }
        }
        return $response;
    }

    function clearOldData($store, $now){
        if (!empty($store)){
            $current_data = [];
            foreach ($store as $key){
                if (($key->tstamp >= $now-10) && ($key->tstamp <= $now)){
                    $current_data[$key->email_id] = $key;
                }
            }
            if ($current_data){
                unset($store);
                $store = $current_data;
            }
        }
        return $store;
    }

    function send($message) {
        global $clientSocketArray;
        $messageLength = strlen($message);
        echo 'messages';var_dump($message);
        foreach($clientSocketArray as $clientSocket)
        {
            @socket_write($clientSocket,$message,$messageLength);
        }
        return true;
    }

    function unseal($socketData) {
        $length = ord($socketData[1]) & 127;
        if($length == 126) {
            $masks = substr($socketData, 4, 4);
            $data = substr($socketData, 8);
        }
        elseif($length == 127) {
            $masks = substr($socketData, 10, 4);
            $data = substr($socketData, 14);
        }
        else {
            $masks = substr($socketData, 2, 4);
            $data = substr($socketData, 6);
        }
        $socketData = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $socketData .= $data[$i] ^ $masks[$i%4];
        }
        return $socketData;
    }

    function seal($socketData) {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($socketData);

        if($length <= 125)
            $header = pack('CC', $b1, $length);
        elseif($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
        elseif($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);
        return $header.$socketData;
    }

    function doHandshake($received_header,$client_socket_resource, $host_name, $port) {
        $headers = array();
        $lines = preg_split("/\r\n/", $received_header);
        foreach($lines as $line)
        {
            $line = chop($line);
            if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
            {
                $headers[$matches[1]] = $matches[2];
            }
        }

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host_name\r\n" .
            "WebSocket-Location: ws://$host_name:$port/demo/shout.php\r\n".
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        socket_write($client_socket_resource,$buffer,strlen($buffer));
    }

    function newConnectionACK($client_ip_address) {
        $message = 'New agent ' . $client_ip_address.' joined';
        $messageArray = array('message'=>$message,'message_type'=>'pull-connection-ack');
        $ACK = $this->seal(json_encode($messageArray));
        return $ACK;
    }

    function connectionDisconnectACK($client_ip_address) {
        $message = 'Agent ' . $client_ip_address.' disconnected';
        $messageArray = array('message'=>$message,'message_type'=>'pull-connection-ack');
        $ACK = $this->seal(json_encode($messageArray));
        return $ACK;
    }
}