<?php
//include('/usr/local/gplexcc/regsrvr/engine/rpc_api.php');

function search_account($accid, $clid)
{
        //echo "404|Agent not found";
		return "200|OK";
}

function caller_verified($accid, $clid, $agentid='')
{
        //echo "404|OK";
		return "200|OK";
		exit;
}

function get_api_data($param)
{
        $obj = new stdClass();
        $sec1 = new stdClass();
        $sec2 = new stdClass();
        $sec3 = new stdClass();
        $sec5 = new stdClass();

        $fld0 = new stdClass();
        $fld0->field_label = 'Date of Birth';
        $fld0->data_value = '1977-10-01';

        $fld1 = new stdClass();
        $fld1->field_label = 'Name';
        $fld1->data_value = 'Test User Test UserTest UserTest UserTest UserTest
UserTest UserTest UserTest UserTest UserTest User';

        $sec1->section_title = 'Customer Profile';
        $sec1->section_type = 'F';
        $sec1->is_editable = 'N';
        $sec1->fields = array('0'=>$fld0, '1'=>$fld1);

        $sec2->section_title = 'Disposition History';
        $sec2->section_type = 'D';
        $sec2->is_editable = 'N';

        $sec5->section_title = 'Services Through IVR';
        $sec5->section_type = 'I';
        $sec5->is_editable = 'N';

        $filter = new stdClass();
        $filter->field_label = 'First Name';
        $filter->field_key = 'fname';
        $filter->field_type = 'T';

        $filter2 = new stdClass();
        $filter2->field_label = 'Date';
        $filter2->field_key = 'sdate';
        $filter2->field_type = 'D';
        
        $sec3->section_title = 'Last 5 Transaction';
        $sec3->section_type = 'G';
        $sec3->is_editable = 'N';
        $sec3->is_searchable = 'Y';
        $sec3->search_submit_label = 'Find';
        
        $sec3->filters = array('0' => $filter, '1' => $filter2);
        
        $sec3->grid = array(
                'column' => array('Amount', 'T. Type', 'Narration', 'Transaction
 Date'),
                '0' => array('3837.42', 'D', 'Pay to Mr. Salmon Ta', '2013-11-20
'),
                '1' => array('12370.12', 'C', 'Deposit Chq # 23423', '2013-11-20
')
        );

        $sec4 = new stdClass();
        $sec4->section_title = 'TPIN';
        $sec4->section_type = 'T';
        $sec4->is_editable = 'N';
        $sec4->tpin_status = 'Y';

        $obj->error = '';
        $obj->callid = '123456';
        $obj->account_id = '123';
        $obj->crm_record_id = '1234567890';//1234567890
        $obj->template_id = 'AAA';
        $obj->page_title = 'gTalk CRM';
        $obj->caller_auth_status = 'N';
        $obj->caller_auth_msg = 'User is not authenticated by our system';
        $obj->section = array(
                'A' => $sec1,
                'E' => $sec1,
                'F' => $sec1,
                'G' => $sec1,
                'H' => $sec1,
                'C' => $sec3,
                'B' => $sec2,
                'D' => $sec4,
                'I' => $sec5
        );

        /*
        echo '<pre>';
        print_r($obj);
        echo '</pre>';
        exit;
        */
        return $obj;

        return array(
                        'template_id' => 'AAA',
                        'record_id' => '1234567890',
                        'A' => array('sfdsdfsfd' => 'Field value for 1'),
                        'B' => array(
                                'var1' => 'Field for B',
                                'var2' => '2nd Field for B'
                        )
                );
}
