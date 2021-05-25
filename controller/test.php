<?php

class Test extends Controller{

    function actionLdap(){
        $ldap_dn = "cn=read-only-admin,dc=example,dc=com";
        $password = "password";
        $ldap_conn = ldap_connect("ldap.forumsys.com");
        $filter = "(uid=newton)";
//        $hostname = "10.156.2.27";
//        $username = "contactcenter@onebank.com.bd";
//        $password = "cc@2020";
//        $ldap_conn = ldap_connect($hostname);

        var_dump($ldap_conn);
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = ldap_bind($ldap_conn, $ldap_dn, $password);
        if ($bind){
            GPrint("Bind Successfull!");
            $search_result = ldap_search($ldap_conn, "dc=example,dc=com", $filter);
            $entries = ldap_get_entries($ldap_conn, $search_result);
            GPrint($entries);
        }else {
            GPrint("Not bind successfully!");
        }
        ldap_close($ldap_conn);
    }

    function validateEmail($string){
        $pattern = '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i';
        preg_match_all($pattern, $string, $matches);
        return $matches[0][0];
    }

    function getValidEmail($emails_arr, $return_type="string") {
        $response = '';
        if (!empty($emails_arr)){
            $cc_arr = is_array($emails_arr) ? $emails_arr : explode(',',$emails_arr);
            if (!empty($cc_arr)){
                $str = (strtolower($return_type)=='string') ? '' : [];
                if (strtolower($return_type)=='string'){
                    foreach ($cc_arr as $cckey){
                        $email = $this->validateEmail($cckey);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $str .= $email.',';
                        }
                    }
                    $response= rtrim($str,',');
                }elseif (strtolower($return_type)=='array'){
                    foreach ($cc_arr as $cckey){
                        $email = $this->validateEmail($cckey);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $str[]= $email;
                        }
                    }
                    $response= $str;
                }
            }
        }
        return $response;
    }

    function findIndex($needle, $n, $array){
        for($i=0; $i<count($array); $i++){
            if ($array[$i] == $needle){
                $n++;
                if ($n > 1) {
                    unset($array[$i]);
                    return $array;
                }
            }
        }
        return null;
    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function getPhoneNumberFromString($reference, $subject, $body){
        $str = "";
        $number = null;
        if (empty($reference) && strlen($subject) > 0){
            $str = $subject;
            goto findNumber;
        } else {
            $str = $body;
            goto findNumber;
        }

        findNumber:
            $str = strip_tags($str);
            $str = str_replace("\xc2\xa0",' ',$str);
            preg_match_all('/[0-9]{5}[\-][0-9]{6}|[\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6}|[0-9]{11}|[\+][0-9]{13}|[\+][0-9]{7}[\-][0-9]{6}|[\+][0-9]{3}[\-][0-9]{8}/', $str, $matches);
            $number = $matches[0][0];

        return $number;
    }

    function getXML2Array($data){
        $xml = simplexml_load_string($data);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }

    function timeDiff($start, $end = null)
    {
        if (!$end) {
            $end = microtime(true);
        }

        $diff = $end - $start;
        $sec = intval($diff);
        $micro = $diff - $sec;
        GPrint("HH:mm:ss:ml");
        return strftime('%T', mktime(0, 0, $sec)) . str_replace('0.', '.', sprintf('%.3f', $micro));
    }

    function isFileExists ($path) {
        //$base_path = "F://XAMPP 7.2.25/htdocs/ccpro/";
        $base_path = base_url();
        if (file_exists($base_path.$path))
            return true;

        while ($ary = explode("/",$path)) {
            unset($ary[0]);
            $path = implode("/", $ary);
            if (file_exists($base_path.$path))
                return $path;
        }
        return false;
    }

    function closetags($html) {
        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);

        $closedtags = $result[1];
        $len_opened = count($openedtags);

        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
    }

    function separateSkillCrmQuery ($url) {
        if (empty($url))
            return null;

        $res = explode(":", $url);
        if ($res[0] == "MSQL" && strpos(strtoupper($res[2]), 'SKILL_CRM') !== false) {
            return $url;
        }
        return null;
    }

    function actionHello(){
        include('model/MCcSettings.php');
        include('model/MReportNew.php');
        include('model/MEmail.php');
        include('model/MCustomerJourney.php');
        include('lib/DateHelper.php');
        include('model/MSkill.php');
        include_once('model/MPredictiveDial.php');



        $html_string = '<html lang=\\\"en\\\"><head><br \/><meta http-equiv=\\\"Content-Type\\\" content=\\\"text\/html; charset=utf-8\\\"><meta content=\\\"text\/html; charset=Windows-1252\\\"><meta content=\\\"IE=edge\\\"><meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1.0\\\"><meta name=\\\"robots\\\" content=\\\"no index\\\"><style><br \/>
<br \/><\/style><\/head><body style=\\\"margin:0 auto; padding:0px\\\"><div style=\\\"background-color:#ffff99; width:100%; padding:1pt; line-height:12pt; font-family:Calibri; color:black; border:1pt solid #9c6500\\\"><span style=\\\"color:#ff0000; background-color:#ffff99\\\">[THIS IS AN EXTERNAL EMAIL]<\/span><br>CAUTION: Do not click links or open attachments unless you recognize the sender and know the content is safe.<\/div><div><div itemscope=\\\"\\\" itemtype=\\\"https:\/\/schema.org\/EmailMessage\\\"><div itemprop=\\\"action\\\" itemscope=\\\"\\\" itemtype=\\\"https:\/\/schema.org\/ViewAction\\\"><link itemprop=\\\"url\\\" href=\\\"https:\/\/notifications.google.com\/g\/p\/AD-FnEzQbkM0d8tyVFEyWy6cdrLcDrEJHl72DTVb3lH4NzmAXqBeTyELyIG_5V3EeteUmOqQ3VGzyvMqEMSDxA7-N8RAAkyzv_adqHMkvshH8WtNpm1Xi5rj74gYSFebd-v84FlpfYrGHGbScb5dkwVnrHo6SoLFELeL\\\"><meta itemprop=\\\"name\\\" content=\\\"Play Console Help Center\\\"><\/div><\/div><p align=\\\"center\\\" style=\\\"padding-top:0; font-size:0px; line-height:0px; color:#ffffff\\\">Be aware of the following tax changes<\/p><table role=\\\"presentation\\\" align=\\\"center\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" width=\\\"100%\\\"><tbody><tr><td align=\\\"center\\\" valign=\\\"top\\\"><table role=\\\"presentation\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" bgcolor=\\\"#ffffff\\\" width=\\\"1024\\\" align=\\\"center\\\" style=\\\"max-width:1024px; width:100%\\\"><tbody><tr><td align=\\\"center\\\" valign=\\\"top\\\" bgcolor=\\\"#ffffff\\\" background=\\\"https:\/\/services.google.com\/fh\/files\/emails\/play_dev_dark_mode_116.png\\\" width=\\\"1px\\\" style=\\\"background:url(https:\/\/services.google.com\/fh\/files\/emails\/play_dev_dark_mode_116.png); background-repeat:repeat-x; background-size:100%; width:1px\\\"><table role=\\\"presentation\\\" width=\\\"100%\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\"><tbody><tr><td class=\\\"view_as\\\" align=\\\"center\\\" valign=\\\"middle\\\" style=\\\"font-family:Roboto,Google Sans,Helvetica,Arial,sans-serif; font-size:10px; color:#424242; line-height:18px; padding:10px 0px 9px 0; border-bottom:#F5F5F5 1px solid\\\"><a target=\\\"_blank\\\" href=\\\"https:\/\/apc01.safelinks.protection.outlook.com\/?url=https%3A%2F%2Fnotifications.google.com%2Fg%2Fvib%2FAD-FnExEr0vrpJiCxNyJMOaXrz--MISDbmd753ZGqA7VQk1Toj6N37Jm9dYRLR6drA65EqTJtHqLA0uFSjb67hIAxr2ZQmOX51tPOOaiN4UBCtPVZWGOt9k194BuUf6jSLGxlG4XfYzWD2jh6n9l17coBZ301pCuR8KUS9HbgOIgIx3f-tDvHiJVuAPZFr5f1BCnw3t-&amp;data=04%7C01%7C123%40robi.com.bd%7Ce5801aaf227145f80a3908d8ed561956%7C255b709dce46478eb485e237f988c923%7C1%7C0%7C637520301338517420%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C1000&amp;sdata=zQMAXmYV%2BwnR0gXq6OcJw0oed1%2BNv7tI8V211XmnLF8%3D&amp;reserved=0\\\" originalsrc=\\\"https:\/\/notifications.google.com\/g\/vib\/AD-FnExEr0vrpJiCxNyJMOaXrz--MISDbmd753ZGqA7VQk1Toj6N37Jm9dYRLR6drA65EqTJtHqLA0uFSjb67hIAxr2ZQmOX51tPOOaiN4UBCtPVZWGOt9k194BuUf6jSLGxlG4XfYzWD2jh6n9l17coBZ301pCuR8KUS9HbgOIgIx3f-tDvHiJVuAPZFr5f1BCnw3t-\\\" shash=\\\"Ev5u8glQJLAwxRqS4ZDzKYrrFu8CKmgv0Txn1F\/QJrYJyJcTu+uH561Me4yQ6XPoEJFFbPNbimMKuXwantVxvDcBDwbTuxWMs3lcU4e1L+W0PP5eTiMbdq9NQwBk3vzOJv8mEFtnrIN4d418ngs7cnI2k5JYCncqRwrLPgt2m6c=\\\" target=\\\"_blank\\\" style=\\\"text-decoration:none; color:#424242; font-size:10px\\\">View as webpage<\/a><\/td><\/tr><tr><td align=\\\"center\\\" valign=\\\"top\\\" width=\\\"100%;\\\"><table role=\\\"presentation\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" width=\\\"647\\\" style=\\\"max-width:647px; width:100%\\\"><tbody><tr><td class=\\\"\\\" align=\\\"left\\\" valign=\\\"top\\\"><table role=\\\"presentation\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" width=\\\"100%\\\"><tbody><tr><td align=\\\"left\\\" valign=\\\"top\\\"><table role=\\\"presentation\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\"><tbody><tr><td align=\\\"left\\\" valign=\\\"top\\\" class=\\\"ol-pd-logo pd-l-30 logo115 logopadd\\\" width=\\\"155\\\" height=\\\"auto\\\" style=\\\"padding-top:24px; padding-bottom:23px\\\"><img aria-hidden=\"true\" src=\"https:\/\/services.google.com\/fh\/files\/emails\/logo_web_play_dev_updated_dark.png\" width=\"155\" height=\"auto\" class=\"logo109\" title=\"Google Play\" alt=\"\" style=\"display:block\"><\/td><\/tr><\/tbody><\/table><\/td><td align=\\\"right\\\" valign=\\\"top\\\"><table role=\\\"presentation\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\"><tbody><tr><td align=\\\"right\\\" class=\\\"pd-r-30 pd-t-28 pd-b-21\\\" valign=\\\"top\\\" style=\\\"font-family:Roboto,Google Sans,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:21px; padding:33px 0px 30px 0px\\\">Developer update <\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><tr><td height=\\\"25\\\" align=\\\"right\\\" valign=\\\"top\\\" bgcolor=\\\"#546E7A\\\" style=\\\"border-collapse:collapse; line-height:25px\\\"><img src=\"https:\/\/services.google.com\/fh\/files\/emails\/spacer_11.gif\" width=\"100%\" height=\"25\" border=\"0\" alt=\"\" style=\"display:block\"><\/td><\/tr><tr><td align=\\\"left\\\" valign=\\\"top\\\"><table role=\\\"presentation\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" align=\\\"center\\\" width=\\\"647\\\" style=\\\"max-width:647px; width:100%\\\"><tbody><tr><td align=\\\"left\\\" valign=\\\"top\\\" class=\\\"pd-lr-30\\\" style=\\\"padding-top:32px; padding-bottom:1px\\\"><table role=\\\"presentation\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" width=\\\"647\\\" align=\\\"left\\\" style=\\\"max-width:647px; width:100%\\\"><tbody><tr><td align=\\\"left\\\" valign=\\\"top\\\"><table role=\\\"presentation\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" border=\\\"0\\\"><tbody><tr><td width=\\\"100%\\\" align=\\\"left\\\" valign=\\\"top\\\"><table role=\\\"presentation\\\" width=\\\"100%\\\" cellpadding=\\\"0\\\" cellspacing=\\\"0\\\" border=\\\"0\\\"><tbody><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Hello Google Play Developer,<\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:20px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Please be aware of the following tax changes which will go into effect in <strong>April and May 2021.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\"><strong>Tax changes for Google Play purchases in British Columbia, Canada:<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Due to the expansion of the tax legislations in British Columbia, Canada, Google will be responsible for determining, charging, and remitting 7% Provincial Sales Tax (PST) for Google Play Store paid app and in-app purchases made in British Columbia, effective <strong>April 1, 2021.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Even if you\u2019re not located in British Columbia, this change in tax laws will still apply for purchases made by British Columbia customers.<\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:20px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Google will send the applicable taxes for paid app and in-app purchases made by customers in British Columbia to the appropriate authority, so you won\u2019t need to calculate and remit PST for British Columbia separately for these customers\u2019 purchases. <strong>No other action is required on your part.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\"><strong>Tax changes for Google Play developers outside of Mexico:<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Due to the expansion of the tax legislation in Mexico, Google will be responsible for determining, charging, and remitting 16% Value-Added Tax (VAT) for Google Play Store paid app and in-app purchases made in Mexico where the developer is located outside of Mexico, effective <strong>April 1, 2021.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Google will send the applicable taxes for paid app and in-app purchases made by customers in Mexico to the appropriate authority, so you won\u2019t need to calculate and send the taxes separately for these customers\u2019 purchases. <strong>No action will be required on your part.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:20px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Note: If you\\\'re located in Mexico, you\\\'re still responsible for determining, charging, and remitting the taxes.<\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\"><strong>Tax changes for Google Play purchases in Bangladesh:<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Due to the expansion of the tax legislation in Bangladesh, Google will be determining, charging, and remitting 15% Value-Added Tax (VAT) for sale of apps and in-app purchases on Google Play Store by users in Bangladesh on or after <strong>May 1, 2021.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Even if you\u2019re not located in Bangladesh, this change in tax laws will still apply for purchases made by Bangladesh users.<\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:20px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Google will send the applicable taxes for paid app and in-app purchases made by users in Bangladesh to the appropriate authority, so you won\u2019t need to calculate and remit VAT for Bangladesh separately for these users\u2019 purchases. <strong>No other action is required on your part.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\"><strong>Impact on subscription prices<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:20px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Please note that beginning on the aforementioned date, VAT will be calculated and charged using existing prices for subscription products. Depending on how you\\\'ve previously calculated taxes for subscriptions, prices for existing subscriptions might be affected. However, you can publish and start selling new subscription products with a different price point or change existing prices for subscriptions products. Learn more about how to <a href=\\\"https:\/\/apc01.safelinks.protection.outlook.com\/?url=https%3A%2F%2Fnotifications.google.com%2Fg%2Fp%2FAD-FnExEZqpO0gYeUACr9rgc3D75-JAdGX4NbcHZXBO6wTcmrm7EAvjWIkHxNXJNv9o5RUxTL1uw3P5Lr_X2Uq2src4UFDozkqN1YeTJz4g8HF366geOWMjKGTHEDCmTIoJ9PyijS7M0QemaI-rClO-vYoLNc3rqa_u9YpLl_0JasUawxJpdQOKRhoLFzN_3EufzEnCftVYsduh1dvldBJAdNWJ6k7hP&amp;data=04%7C01%7C123%40robi.com.bd%7Ce5801aaf227145f80a3908d8ed561956%7C255b709dce46478eb485e237f988c923%7C1%7C0%7C637520301338527422%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C1000&amp;sdata=zZKhey1Pu604Oxx%2FLL%2BIFt9VwRCtsX9Bx%2FZgZUxaxWY%3D&amp;reserved=0\\\" originalsrc=\\\"https:\/\/notifications.google.com\/g\/p\/AD-FnExEZqpO0gYeUACr9rgc3D75-JAdGX4NbcHZXBO6wTcmrm7EAvjWIkHxNXJNv9o5RUxTL1uw3P5Lr_X2Uq2src4UFDozkqN1YeTJz4g8HF366geOWMjKGTHEDCmTIoJ9PyijS7M0QemaI-rClO-vYoLNc3rqa_u9YpLl_0JasUawxJpdQOKRhoLFzN_3EufzEnCftVYsduh1dvldBJAdNWJ6k7hP\\\" shash=\\\"bh\/ZRRP9+zILc3uV++TIu4OsCqZX11RCbtdr9MEjgzL1VdUMA5NNMAbvWtKK1AKBX+WzO0lK\/02N2lTD+kVOz\/zRusu1PdHUOakJZRxcW\/wr0qf82hx8OgRkBCbxa5ZffsLirAWlIIMKzFlIRYAtaKrqpyPkX2g8viwtPP6n0aw=\\\" target=\\\"_blank\\\" style=\\\"text-decoration:none; color:#004dcf\\\">change the price of a subscription<\/a>.<\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\"><strong>Tax changes for Google Play developers outside of Oman:<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Due to the expansion of the tax legislation in Oman, Google will be responsible for determining, charging, and remitting 5% Value-Added Tax (VAT) for Google Play Store paid app and in-app purchases made in Oman where the developer is located outside of Oman, effective <strong>April 16, 2021.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Google will send the applicable taxes for paid app and in-app purchases made by customers in Oman to the appropriate authority, so you won\u2019t need to calculate and send the taxes separately for these customers\u2019 purchases. <strong>No action will be required on your part.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:20px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Note: If you\\\'re located in Oman, you\\\'re still responsible for determining, charging, and remitting the taxes.<\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\"><strong>Tax changes for Google Play purchases in Kenya:<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Due to the expansion of the tax legislation in Kenya, Google will be responsible for determining, charging, and remitting 16% Value-Added Tax (VAT) for Google Play Store paid app and in-app purchases made in Kenya, effective <strong>April 8, 2021.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Even if you\u2019re not located in Kenya, this change in tax laws will still apply for purchases made by Kenya customers.<\/p><\/td><\/tr><tr><td class=\\\"\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Google will send the applicable taxes for paid app and in-app purchases made by customers in Kenya to the appropriate authority, so you won\u2019t need to calculate and remit VAT for Kenya separately for these customers\u2019 purchases. <strong>No other action is required on your part.<\/strong><\/p><\/td><\/tr><tr><td class=\\\"pd-b-20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:16px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">Google will also begin using VAT-inclusive pricing in Kenya, which means that prices shown on Google Play will include all taxes, regardless of where your business is located. This change will apply to all paid apps and in-app purchases made by customers in Kenya.<\/p><\/td><\/tr><tr><td class=\\\"\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:0px; padding-bottom:25px\\\"><p style=\\\"margin:0; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; color:#424242; line-height:22px; padding-right:20px\\\">For more information on tax rates visit the <a href=\\\"https:\/\/apc01.safelinks.protection.outlook.com\/?url=https%3A%2F%2Fnotifications.google.com%2Fg%2Fp%2FAD-FnEzusbos_ZIs0_n97U3ybIex7EsJWhgj9b9NZ1LpfoEGoKMT7q3V8P0-wcpVoirHgXyJ9KvLHCwuB8f-we_QD3aEbTDy4QldnBcEPMds0Bof2KzUA4mQQ2tA5s5VmmTVHD6gWHskRZQc5wl7yIxx7gVE21Nik_rvyq9kgTb6gkDfHX134YsSdMTexarbMZU-&amp;data=04%7C01%7C123%40robi.com.bd%7Ce5801aaf227145f80a3908d8ed561956%7C255b709dce46478eb485e237f988c923%7C1%7C0%7C637520301338537415%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C1000&amp;sdata=CIq6jF7jCFWpnh2AY8SvxZWErx78R1RoKiNKRAkIjr8%3D&amp;reserved=0\\\" originalsrc=\\\"https:\/\/notifications.google.com\/g\/p\/AD-FnEzusbos_ZIs0_n97U3ybIex7EsJWhgj9b9NZ1LpfoEGoKMT7q3V8P0-wcpVoirHgXyJ9KvLHCwuB8f-we_QD3aEbTDy4QldnBcEPMds0Bof2KzUA4mQQ2tA5s5VmmTVHD6gWHskRZQc5wl7yIxx7gVE21Nik_rvyq9kgTb6gkDfHX134YsSdMTexarbMZU-\\\" shash=\\\"SLV8ToUWNGEdViVNEDHJTPtEgDIBvFIH7rNegstzUe2OfvtmU0I+elPWUWpVKH\/Z9uo6TAO3SLiGMDLw2L86Li3HiikJinxYUwsWI8SWcvWd8AyGmHUQPlDvztNSY4ur7BCkP1obi1WFU9ynlktiAOmnMeyK18I6q7QnQ68Whtc=\\\" target=\\\"_blank\\\" style=\\\"text-decoration:none; color:#004dcf\\\">Google Play Console Help Center<\/a>. If you have any other questions, please consult your tax advisor.<\/p><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><tr><td align=\\\"center\\\" valign=\\\"top\\\" class=\\\"pad_t_34\\\" style=\\\"padding-top:15px; border-top:#f1f3f4 2px solid\\\"><table role=\\\"presentation\\\" width=\\\"647\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" style=\\\"max-width:647px; width:100%\\\"><tbody><tr><td align=\\\"left\\\" valign=\\\"top\\\" class=\\\"dblock w-100 pd-b-38\\\" style=\\\"padding-bottom:32px\\\"><table role=\\\"presentation\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\"><tbody><tr><td class=\\\"lh16\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; line-height:28px; color:#424242\\\">Thank you,<\/td><\/tr><tr><td class=\\\"pad_t_8\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"font-family:Roboto,Helvetica,Arial,sans-serif; font-size:20px; line-height:30px; color:#424242\\\">The Google Play team<\/td><\/tr><tr><td class=\\\"pad_t_20\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"font-family:Roboto,Helvetica,Arial,sans-serif; font-size:14px; line-height:22px; color:#424242; padding-top:20px\\\">Connect with us<\/td><\/tr><tr><td class=\\\"padtop14\\\" align=\\\"left\\\" valign=\\\"top\\\" style=\\\"padding-top:10px\\\"><table role=\\\"presentation\\\" width=\\\"100%\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\"><tbody><tr><td align=\\\"left\\\"><table role=\\\"presentation\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" align=\\\"left\\\"><tbody><tr><td width=\\\"28\\\" align=\\\"center\\\"><a href=\\\"https:\/\/apc01.safelinks.protection.outlook.com\/?url=https%3A%2F%2Fnotifications.google.com%2Fg%2Fp%2FAD-FnEyl0gyUUgAAAyUIVDWU52jhbOlkN1IXWwNmAayZQrKJn3zE_ykPNhOTWFMW5PzJOuNrW1EKqvqfytM88z4pGdfR75WYRn0u0dzYN9QCZJJIdJVibfESCqo6TAprXep8hfE-RN2dePMXzB1cT72SDqTUAxkjuWOcxqjR-zxqHSxe&amp;data=04%7C01%7C123%40robi.com.bd%7Ce5801aaf227145f80a3908d8ed561956%7C255b709dce46478eb485e237f988c923%7C1%7C0%7C637520301338537415%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C1000&amp;sdata=frlSfVGoIU9%2F1WoyR4Uc75dpLn%2F%2Ft8cVf4ZIlFQyd%2Bc%3D&amp;reserved=0\\\" originalsrc=\\\"https:\/\/notifications.google.com\/g\/p\/AD-FnEyl0gyUUgAAAyUIVDWU52jhbOlkN1IXWwNmAayZQrKJn3zE_ykPNhOTWFMW5PzJOuNrW1EKqvqfytM88z4pGdfR75WYRn0u0dzYN9QCZJJIdJVibfESCqo6TAprXep8hfE-RN2dePMXzB1cT72SDqTUAxkjuWOcxqjR-zxqHSxe\\\" shash=\\\"MoqVOEUBKYO4XAcMlO0gCUzPE1nE9rbT8MKoTCnFcPDg0Xe20FwlcMlv9ujvDokLid2W3lvlYhBv3VPW\/Gnh\/RPoea360Mi9K\/ouAklpCAr9ErLBpMcXDkL9PeGv79bmLDIPVwnRjKFtTyzvlFVik3TO9JR7Do7E8\/qfFBN7sjI=\\\" target=\\\"_blank\\\" style=\\\"text-decoration:none\\\"><img src=\"https:\/\/services.google.com\/fh\/files\/emails\/help_icon_image_updated_msa.png\" width=\"28\" alt=\"Help\" title=\"Help\" style=\"display:block\"><\/a><\/td><td width=\\\"13\\\" aria-hidden=\\\"true\\\"><img src=\"https:\/\/services.google.com\/fh\/files\/emails\/spacer_11.gif\" width=\"10\" height=\"1\" border=\"0\" style=\"display:block\"><\/td><td width=\\\"28\\\" align=\\\"center\\\"><a href=\\\"https:\/\/apc01.safelinks.protection.outlook.com\/?url=https%3A%2F%2Fnotifications.google.com%2Fg%2Fp%2FAD-FnEzBS72PBDKd3NqZp-Ylg4EJftg4xQhyqHUDcF3Db7mSXOkmMOWvxPUzL9dz0qAiGXZ6W2qKoHLQ3E82CL-m8wJoh0dAr-HJnJAC4vKqvfCb_IK5wLqE2Pv221XWIi_HP0leLM_3YrnseQ&amp;data=04%7C01%7C123%40robi.com.bd%7Ce5801aaf227145f80a3908d8ed561956%7C255b709dce46478eb485e237f988c923%7C1%7C0%7C637520301338547413%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C1000&amp;sdata=jx2LSpxlgaeNrIEqCItSzuOKiiXadeApOCfFioqhfnc%3D&amp;reserved=0\\\" originalsrc=\\\"https:\/\/notifications.google.com\/g\/p\/AD-FnEzBS72PBDKd3NqZp-Ylg4EJftg4xQhyqHUDcF3Db7mSXOkmMOWvxPUzL9dz0qAiGXZ6W2qKoHLQ3E82CL-m8wJoh0dAr-HJnJAC4vKqvfCb_IK5wLqE2Pv221XWIi_HP0leLM_3YrnseQ\\\" shash=\\\"bBjqLwC88w7RKHHOt3CJsjgM0Wd4NWWqLEgi7eub2AE9iaFt8Z2NXAxpGVm0kLsUigAD1kk838oJRcEFT0GFmX0Bdn31lnVzdxp9ikR3GPVCXq\/OBEZ2n7aEQiWdQw4OBqF0hmIR3cpKW3fS9aMmNcyMhbjQaIguzwtj8duAJuE=\\\" target=\\\"_blank\\\" style=\\\"text-decoration:none\\\"><img src=\"https:\/\/services.google.com\/fh\/files\/emails\/google_play_icon_msa.png\" width=\"28\" alt=\"Play Icon\" title=\"Play Icon\" style=\"display:block\"><\/a><\/td><td width=\\\"13\\\" aria-hidden=\\\"true\\\"><img src=\"https:\/\/services.google.com\/fh\/files\/emails\/spacer_11.gif\" width=\"10\" height=\"1\" border=\"0\" style=\"display:block\"><\/td><td width=\\\"28\\\" align=\\\"center\\\"><a href=\\\"https:\/\/apc01.safelinks.protection.outlook.com\/?url=https%3A%2F%2Fnotifications.google.com%2Fg%2Fp%2FAD-FnEzUg4ePM2tK3efJHWf-_gACOgdzuY5yDUofbyUreChPqxctXiUCPpq-T6GRmqnTI_xSb5udH3h5fvJUU34Qh_SEgZM0rjcyTTUg0pkZSpv-CI-pjrtdy8xz_jAdM9iIwR90ir2-OsreM0fGTss_v93JhTbHsRd-Gu7oPkLEoIzGy0nKrZFqRfvUBKUwQkQKZOx3Bs5tWAnw2ZGKnZHva79gu3A74-GWCA&amp;data=04%7C01%7C123%40robi.com.bd%7Ce5801aaf227145f80a3908d8ed561956%7C255b709dce46478eb485e237f988c923%7C1%7C0%7C637520301338547413%7CUnknown%7CTWFpbGZsb3d8eyJWIjoiMC4wLjAwMDAiLCJQIjoiV2luMzIiLCJBTiI6Ik1haWwiLCJXVCI6Mn0%3D%7C1000&amp;sdata=Lhffo7rS5QHaTdFe9FNvFG6pZiufXou%2FCk41l9aJp2w%3D&amp;reserved=0\\\" originalsrc=\\\"https:\/\/notifications.google.com\/g\/p\/AD-FnEzUg4ePM2tK3efJHWf-_gACOgdzuY5yDUofbyUreChPqxctXiUCPpq-T6GRmqnTI_xSb5udH3h5fvJUU34Qh_SEgZM0rjcyTTUg0pkZSpv-CI-pjrtdy8xz_jAdM9iIwR90ir2-OsreM0fGTss_v93JhTbHsRd-Gu7oPkLEoIzGy0nKrZFqRfvUBKUwQkQKZOx3Bs5tWAnw2ZGKnZHva79gu3A74-GWCA\\\" shash=\\\"ExhI\/mK8lDrMqkN5JdU6skrGEVjA\/C+0A8YAzR5SFQg4WuKrf4AuLO5RpAd7mJ3NPbNiDgTxyjHXQP7T7PsKJsVF6HTJYcSNDR92IAFSoTbtWdQtwll\/Y91DK6GVhLmWD3M31W9wP5V1Ii0uwvdDow7X0IjyU6g5U4oMshlQKSw=\\\" target=\\\"_blank\\\" style=\\\"text-decoration:none\\\"><img src=\"https:\/\/services.google.com\/fh\/files\/emails\/google_play_play_academy_logo.png\" width=\"28\" alt=\"Play Academy\" title=\"Play Academy\" style=\"display:block\"><\/a><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><tr><td class=\\\"pd-t-40 pd-lr-30\\\" bgcolor=\\\"#F1F3F4\\\" align=\\\"center\\\" valign=\\\"top\\\" style=\\\"padding-top:32px; padding-bottom:33px\\\"><table role=\\\"presentation\\\" border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" width=\\\"647\\\" align=\\\"center\\\" style=\\\"max-width:647px; width:100%\\\"><tbody><tr><td class=\\\"pd-0 text-left\\\" align=\\\"center\\\" class=\\\"\\\" valign=\\\"top\\\" style=\\\"color:#424242; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:12px; line-height:16px; padding-left:25px; padding-right:25px; padding-top:0px\\\">\u00a9 2021 <a href=\\\"#\\\" style=\\\"text-decoration:none; color:#424242\\\">Google LLC 1600 Amphitheatre Parkway, Mountain View, CA 94043, USA<\/a> <\/td><\/tr><tr><td class=\\\"pd_otlk text-left\\\" align=\\\"center\\\" valign=\\\"top\\\" style=\\\"color:#424242; font-family:Roboto,Helvetica,Arial,sans-serif; font-size:12px; line-height:16px; padding-top:16px\\\">This message was sent to <a href=\\\"#\\\" target=\\\"_blank\\\" style=\\\"text-decoration:none; color:#424242\\\">123@robi.com.bd<\/a> to inform you about important updates on your Google Play developer account.<\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><\/td><\/tr><\/tbody><\/table><img alt=\"\" height=\"1\" width=\"3\" src=\"https:\/\/notifications.google.com\/g\/img\/AD-FnEywblQDL0GOSXXSlzBsjJ7kIpR5qA4xeSBEoqbyGNOIDJY.gif\"> <\/div><\/body><\/html>';

        $tidy = new tidy();
        $htmlBody = $tidy->repairString($html_string, array(
            'output-xhtml' => true,
            'show-body-only' => true,
        ), 'utf8');

        $dom = new DOMDocument();
        $dom->loadHTML($html_string);
//        $dom->saveHTML();
//        $test = $dom->documentElement;
        $test = $dom->getElementsByTagName("body");
        GPrint($test);
//        $body = $dom->getElementsByTagName('head')->item(0);
//        GPrint($body);
//        $output = $dom->saveHTML();
//        GPrint($output);

//        preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html_string, $matches);
//        GPrint($matches);
        die;




        $test = strpos("arif@genusys.us", "arif");
        if ( $test !== false)
         var_dump($test);die;


        $email_model = new MEmail();

        $file = "F://XAMPP 7.2.25/htdocs/ccpro/content/4582884818.txt";
        if (file_exists($file)) {
            $handle = fopen($file, "r");
            if ($handle) {
                $sql = "insert into email_messages set ";
                $n=0;
                while (($line = fgets($handle)) !== false) {

                    if (strpos($line, '***************************') === false) {
                        $val = explode(":", $line);
                        $val[1] = str_replace("\n", "", trim($val[1]));
                        if (!empty($val[1]))
                            $sql .= trim($val[0])."='".$val[1]."', ";
//                        GPrint($sql);

                        if (trim($val[0]) == "mail_body" && $n ==57){
                            $str = $val[0]."='".$val[1]."', ";
//                            var_dump($val[0]);
                            print_r($val[1]);
//                            var_dump($str);
                        }
//                        GPrint($val[0]);
//                        GPrint($val[1]);
                    } else {
                        $n++;
                        $sql = rtrim(trim($sql), ",").";";


                        if (1 || $n <= 57) {
//                            GPrint($sql);
                            $result = $email_model->test($sql);
//                            var_dump($result);
                        }
                        $sql = "insert into email_messages set ";
                    }
//                    GPrint($line);die;
                }
                fclose($handle);
            } else {
                // error opening the file.
            }
        }
        else
            GPrint('not exists');
        die;

        $data['ip'] = "192.168.10.60";
        $data['port'] = "8090";
        $data['skills'] = $skills;
        $data['pageTitle'] = 'Email Agent Dashboard';
        $data['suffix'] = strtolower(UserAuth::getDBSuffix());
        $this->getTemplate()->display_only('test',$data);
    }

    function getSimpleXML2Array($xml, $xpath) {
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml);
        $xml = new SimpleXMLElement($response);
        $body = $xml->xpath("//$xpath")[0];
        return json_decode(json_encode((array)$body), TRUE);
    }

    function getXml2Object($result){
        $p = xml_parser_create();
        xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
        xml_parse_into_struct($p, $result, $vals, $index);
        xml_parser_free($p);

        //GPrint($vals);

        $obj = new stdClass();
        foreach ($vals as $key => $value){
            $tag_name = $value['tag'];
            $tag_value = !empty($value['value']) ? $value['value'] : "";
            $obj->$tag_name = !empty($tag_value) && !is_array($tag_value) ? $tag_value : "";
        }
        return $obj;
    }
    function obj2array($obj, $i = 0) {
        // global $_tmp_ROW;
        if (! $i)
            $_tmp_ROW = array ();
        foreach ( $obj as $key => $val ) {
            if (is_object ( $val )) {
                obj2array ( $val, 1 );
            } else {
                $key = trim ( $key );
                $val = trim ( $val );
                $_tmp_ROW [] = $val;
                if (! is_numeric ( $key ) && strlen ( $key ) > 0)
                    $_tmp_ROW [$key] = $val;
            }
        }
        return $_tmp_ROW;
    }


    function getFileTypeIcon($extension){
        $extension = strtoupper($extension);
        if ($extension=='PNG' || $extension=='JPEG' || $extension=='JPG'){
            return '<img src="../../dist/img/photo1.png" alt="Attachment">';
        }
        elseif($extension=='PLAIN' || $extension=='TXT'){
            return '<i class="fa fa-file-word-o" aria-hidden="true"></i>';
        }
        elseif ($extension=='X-ZIP-COMPRESSED'){
            return '<i class="fa fa-file-archive-o" aria-hidden="true"></i>';
        }
        elseif($extension=='PDF'){
            return '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>';
        }else {
            return '<i class="fa fa-file-o" aria-hidden="true"></i>';
        }
    }

    function get_phone_number_from_email($text){
        if (strlen($text) > 0){
            var_dump(nl2br($text));
            $text = strip_tags($text);
            //$text = str_replace("\xc2\xa0",' ',$text);
            //$email_regexp = "[_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*(\\.[A-Za-z]{2,3})";
            preg_match_all('/[0-9]{5}[\-][0-9]{6}| [\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6} | [w][0-9]{11}| [\+][0-9]{13}| [\+][0-9]{7}[\-][0-9]{6}| [\+][0-9]{3}[\-][0-9]{8}| [\w][\+][0-9]{13}/', $text, $matches);
            //preg_match_all('/[0-9]{5}[\-][0-9]{6}| [\+][0-9]{3}[\s][0-9]{4}[\-][0-9]{6} | [0-9]{11}| [\+][0-9]{13}| [\+][0-9]{7}[\-][0-9]{6}| [\+][0-9]{3}[\-][0-9]{8}/', $text, $matches);
            echo 'number</br>';var_dump($text);echo '</br>';
            echo 'number</br>';var_dump($matches);echo '</br>';
            $matches = $matches[0];
            return $matches;
        }
        return '';
    }

    function Dir($dir, $file=null){
        if ($file) unlink($dir . $file);
        $scanned_directory = array_diff(scandir($dir), array('..', '.'));
        $is_deleted = false;
        if (empty($scanned_directory)) {
            if (rmdir($dir)){
                $is_deleted = true;
            }
            $links = explode("/", $dir);
            if (!empty($links)) {
                foreach ($links as $link) {
                    if ($link == '') {
                        echo 'empty element';
                        array_pop($links);
                    }
                }
            }
            if ($is_deleted) array_pop($links);
            $links = implode("/", $links);
            $this->Dir($links);
        }
    }

    function parse_email_address($email)
    {
        $name = '';
        $user_name = '';
        $eadd = '';

        $lt_pos = strpos($email, '<');

        if ($lt_pos !== false) {
            $name = substr($email, 0, $lt_pos);
            $name = trim($name);
            $name = trim($name, '"');
            $eadd = substr($email, $lt_pos+1);
            $eadd = rtrim($eadd, '>');
        } else {
            $eadd = trim($email);
        }

        $user_name = $name;
        if (empty($name)) {
            $at_pos = strpos($eadd, '@');
            if ($at_pos !== false) {
                $user_name = substr($eadd, 0, $at_pos);
            } else {
                $user_name = $eadd;
            }
        }

        return array('name' => $name, 'user' => $user_name, 'email' => $eadd);
    }
    function getValidEmail_2($emails_arr, $return_type="string"){
        $response = '';
        if (!empty($emails_arr)){
            $cc_arr = is_array($emails_arr) ? $emails_arr : explode(',',$emails_arr);
            if (!empty($cc_arr)){
                $str = (strtolower($return_type)=='string') ? '' : [];
                if (strtolower($return_type)=='string'){
                    foreach ($cc_arr as $cckey){
                        $email = $this->validateEmail($cckey);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $str .= $email.',';
                        }
                    }
                    $response= rtrim($str,',');
                }elseif (strtolower($return_type)=='array'){
                    foreach ($cc_arr as $cckey){
                        $email = $this->validateEmail($cckey);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $str[]= $email;
                        }
                    }
                    $response= $str;
                }
            }
        }
        return $response;
    }

    function actionWebchatQueryBuilder() {
        $dates = ['11/06/2019', '11/08/2019', '11/11/2019', '11/13/2019', '11/13/2019', '11/22/2019', '11/29/2019'];
        $callid = ['1573019067211900000', '1573182218200660000', '1573470911203780000', '1573660711210900000', '1573661246203470000', '1574412239200730000', '1575106342211640000'];
        $file = "temp/webchat-query.txt";
        if (file_exists($file))
        {
            file_put_contents($file, '');
            $file = fopen($file, "a");
            foreach ($dates as $key => $value)
            {
                $valid_date = date("Y-m-d", strtotime($value));
                $valid_callid = $callid[$key];

                $select_query = $this->createSelectQuery($valid_date, $valid_callid);
                GPrint($select_query);
                fwrite($file, $select_query.PHP_EOL);
                $update_query = $this->createUpdateQuery($valid_date, $valid_callid);
                GPrint($update_query);
                fwrite($file, $update_query.PHP_EOL.PHP_EOL);
            }
            fclose($file);
        } else {
            echo "File not exists";
        }

    }
    function createSelectQuery($date, $callid) {
        $sdate = $date." 00:00:00";
        $edate = $date." 23:59:59";
        $sql = "SELECT * FROM log_skill_inbound ";
        $sql .= " WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
        return $sql .= " AND callid='{$callid}' and service_time > 7000;";
    }
    function createUpdateQuery($date, $callid){
        $sdate = $date." 00:00:00";
        $edate = $date." 23:59:59";
        $sql = "UPDATE log_skill_inbound ";
        $sql .= " SET STATUS = 'A', service_time = 0, agent_id = ''";
        $sql .= " WHERE call_start_time BETWEEN '{$sdate}' AND '{$edate}' ";
        return $sql .= " AND callid = '{$callid}' AND service_time > 7000";
    }

    private function getValidEmailAddress($string){
        $pattern = '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i';
        preg_match_all($pattern, $string, $matches);
        return $matches[0][0];
    }
}