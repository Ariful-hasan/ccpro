<?php

function do_cc_request($data)
{
        require __DIR__ . '/conf.php';
	$arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
        );
	$resp = file_get_contents($gplexcc_web_url . "chat-service/index.php?$data", false, stream_context_create($arrContextOptions));
	return $resp;
}
