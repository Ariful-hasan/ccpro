<?php

if ( !empty( $filePath ) && file_exists( $filePath ) ) {
    $fh = fopen( $filePath, 'r' );
    ?>
<div class="pull-right" style="margin:-10px 0 2px 0;">
<!--<a id='sipDownloader' class='btn btn-xs btn-success' href='--><?php //echo $sipDlUrl; ?><!--' > <i class='fa fa-download'></i> </a> &nbsp;&nbsp;-->
<!--<a id='sipPrinter' class='btn btn-xs btn-success' href='#' onclick="javascript:printSipElem();"> <i class='fa fa-print'></i> </a>-->
</div>
<div id="sipFileContents" readonly="readonly" class="form-control" style="height: 500px; overflow: auto;"><?php

    if ( !empty( $skillinfo ) && $skillinfo->qtype == 'C' ) {

        if ( !empty( $chatDetailLog ) ) {
            echo "Client Name: <strong>$chatDetailLog->client_name</strong> <br/>";
            echo "Phone: <strong>$chatDetailLog->contact_number</strong>";

//            echo "Email: <strong>$chatDetailLog->email</strong> ";

//            echo "<br/>Service: <strong>$chatDetailLog->service_name</strong> ";
            if ( !empty( $chatDetailLog->title ) ) {
                echo "<br/>Disposition: <strong>$chatDetailLog->title</strong> ";
            }

            if ( !empty( $chatDetailLog->note ) ) {
                echo "<br/>Note: <strong>$chatDetailLog->note</strong>";
            }

            echo "<br/><br/>";
        }

        $i = 0;
        $msgId = [];
        $message = [];
        $countChunk = 0;
        $totalChunk = 0;
        $isChunkUsed = false;
        $line_ts = '';

        while ( !feof( $fh ) ) {
            $line = fgets( $fh );

// GPrint($line);
            if ( $line[0] != '#' ) {
                //$line = ;
                $pos = strpos( $line, ':' );

                if ( $pos !== false ) {
                    $l1 = substr( $line, 0, $pos );
                    $l2 = substr( $line, $pos + 1 );
                    $msgArray = explode( "|", base64_decode( $l2 ) );

// GPrint($msgArray);

// GPrint($msgId);

                    if ( count( $msgArray ) > 1 ) {
                        $isChunkUsed = true;
                        if ( !in_array( $msgArray[0], $msgId ) || $msgArray[2] == 0 ) {
                            $countChunk = 0;
                            $totalChunk = $msgArray[2];
                            if ( !in_array( $msgArray[0], $msgId ) && $msgArray[1] != 'Lg==' ) {
                                $message[$l1][$msgArray[0]] = $msgArray[1];
                                array_push( $msgId, $msgArray[0] );
                            }

                            //                                echo 'if step 2 <br>';
                        } else {

                            if ( $msgArray[2] >= 1 && $msgArray[3] == 0 ) {
                                array_push( $msgId, $msgArray[0] );
                                $countChunk = 0;
                                $totalChunk = $msgArray[2];
                                $message[$l1][$msgArray[0]] = $msgArray[1];
                                //                                    echo 'elseif step 2 <br>';
                            } else {
                                $countChunk = $msgArray[3];
                                $totalChunk = $msgArray[2];
                                $message[$l1][$msgArray[0]] .= $msgArray[1];
                                //                                    echo 'step 3.= <br>';
                            }

                        }

                    } else {
                        $isChunkUsed = false;
                        $message[$l1][$msgArray[0]] = $msgArray[0];
                    }

// GPrint($isChunkUsed);

// GPrint($countChunk);

// GPrint($totalChunk);
                    if ( $isChunkUsed ) {
                        if ( $countChunk == $totalChunk ) {
                            $_msg = isset( $message[$l1][$msgArray[0]] ) ? base64_decode( $message[$l1][$msgArray[0]] ) : '';
                            if ( $_msg == '.' || $_msg == '' ) {
                                $line = '';
                            } else { $line = '<strong>' . $l1 . ' : </strong>' . $_msg . "<br>";}

                        } else {
                            continue;
                        }

                    } else {
                        if ( $message[$l1][$msgArray[0]] == '.' || $message[$l1][$msgArray[0]] == '' ) {
                            $line = '';
                        } else {
                            $line = '<strong>' . $l1 . ' : </strong>' . $message[$l1][$msgArray[0]] . "<br>";
                        }

                    }

                }

            } else {
                if ( $isChunkUsed ) {
                    if ( $countChunk == $totalChunk ) {
                        $line_ts = date( "Y-m-d H:i:s", substr( $line, 2, 10 ) ) . "\n";
                        if ( $i > 0 ) {
                            $line_ts = "\n" . $line_ts;
                        }

                        $i++;
                    } else {
                        continue;
                    }

                } else {
                    $line_ts = date( "Y-m-d H:i:s", substr( $line, 2, 10 ) ) . "\n";
                    if ( $i > 0 ) {
                        $line_ts = "\n" . $line_ts;
                    }

                    $i++;
                }

                $line = '';
            }

            if ( !empty( $line ) ) {
                echo nl2br( $line_ts . $line );
                $line_ts = '';
                $line = '';
            }

        }

    } else {
        $line = fread( $fh, filesize( $filePath ) );
        echo nl2br( htmlspecialchars( $line, ENT_SUBSTITUTE ) );
    }

    ?></div>
<iframe id="printf" name="printf" style="display: none;"></iframe>
<script type="text/javascript">
var pageTitle = "<?php echo $pageTitle; ?>";
function printSipElem()
{
        if(typeof pageTitle === 'undefined' || pageTitle == ""){
                pageTitle = "SIP Log";
        }
        var data = $("#sipFileContents").html();
        var mywindow = window.frames['printf'];
        mywindow.document.write('<html><head><title>'+pageTitle+'</title>');
        mywindow.document.write('</head><body onload=window.print()>');
        mywindow.document.write(data);
        mywindow.document.write('</body></html>');
        mywindow.document.close();

    return true;
}
/*
function printSipElem()
{
        if(typeof pageTitle === 'undefined' || pageTitle == ""){
                pageTitle = "SIP Log";
        }
        var data = $("#sipFileContents").html();
    var mywindow = window.open('', pageTitle, 'height=300,width=400');
    mywindow.document.write('<html><head><title>'+pageTitle+'</title>');
    /*optional stylesheet //mywindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
    mywindow.document.write('</head><body>');
    mywindow.document.write(data);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10

    mywindow.print();
    mywindow.close();

    return true;
}
*/
window.onbeforeunload = function(){};
</script>
<?php
fclose( $fh );
} else {
    echo "File doesn't exist:" . $filePath;

}
