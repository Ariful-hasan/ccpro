<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" media="screen" href="css/multi-select.css">
<script src="js/jquery.multi-select.js" type="text/javascript"></script>

<?php
$agent_title = 'Login';
/*if ($agent->usertype == 'S') {
    $agent_title = 'Supervisor';
} else if ($agent->usertype == 'D') {
    $agent_title = 'Dashboard User';
}*/

?>
<style type="text/css">
    label.error {
        float: right;
        font-size: 12px;
        margin-top: 4px;
        width: 56%;
    }
    .form_table td.form_column_caption {
        padding-top: 14px;
        vertical-align: top;
    }
    .form_table td.form_column_caption {
        width: 215px;
    }
    .form_table input[type="password"] {
        background-color: #ffffff;
        background-image: none;
        border: 1px solid #cccccc;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;
        color: #555555;
        display: inline;
        font-size: 14px;
        height: 34px;
        line-height: 1.42857;
        padding: 6px 12px;
        transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
        width: 100%;
    }
</style>
<script type="text/javascript">
    Date.format = 'yyyy-mm-dd';
    $(document).ready(function() {
        $('#did_options').multiSelect();
        $('#select-all').click(function(){
            $('#did_options').multiSelect('select_all');
            return false;
        });
        $('#deselect-all').click(function(){
            $('#did_options').multiSelect('deselect_all');
            return false;
        });

        var pRulesArray = new Array();
        var passMaxLength = 15;
        var passMinLength = 1;
        pRulesArray = '<?php echo json_encode($passRules); ?>';
        pRulesArray = eval('('+pRulesArray+')');
        if(typeof pRulesArray.min != 'undefined' && pRulesArray.min != ""){
            passMinLength = pRulesArray.min;
        }
        if(typeof pRulesArray.max != 'undefined' && pRulesArray.max != ""){
            passMaxLength = pRulesArray.max;
        }
        $('#frm_agent').attr('autocomplete', 'off');

        //$('.date-pick').datePicker({clickInput:true,createButton:false,startDate:'1971-01-01', endDate: (new Date()).asString()});
        $.validator.addMethod(
            "regex",
            function(value, element, regexp) {
                //var re = new RegExp(/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{7,}$/);
                //return this.optional(element) || value.match(re);
                //^(?=.*\d{2})(?=.*[a-z]{2})(?=.*[A-Z]{2}).{4,10}$
                var regxErrMsg = "";
                var isPassInvalid = true;
                var allowChars = "";
                if(typeof pRulesArray.spe != 'undefined' && pRulesArray.spe != ""){
                    var strSp = pRulesArray.spe;
                    for (var i = 0; i < strSp.length; i++) {
                        allowChars += "\\"+strSp.charAt(i);
                    }
                }
                var regexCha = new RegExp('[a-zA-Z]{'+pRulesArray.cha+',}');
                var regexSpe = new RegExp('[^a-zA-Z0-9'+allowChars+']');
                var regexUpp = new RegExp('(?:[A-Z].*){'+pRulesArray.upp+'}');
                var regexLow = new RegExp('(?:[a-z].*){'+pRulesArray.low+'}');
                var regexNum = new RegExp('(?:[0-9].*){'+pRulesArray.num+'}');
                if (typeof pRulesArray.cha != 'undefined' && pRulesArray.cha != "" && !value.match(regexCha)) {
                    isPassInvalid = false;
                    regxErrMsg = 'New Password field must contain at least '+pRulesArray.cha+' character';
                } else if (typeof pRulesArray.spe != 'undefined' && pRulesArray.spe == "" && value.match(/[^a-zA-Z0-9]/gi)) {
                    isPassInvalid = false;
                    regxErrMsg = 'New Password field contain invalid character';
                } else if (typeof pRulesArray.spe != 'undefined' && pRulesArray.spe != "" && value.match(regexSpe)) {
                    isPassInvalid = false;
                    regxErrMsg = 'New Password field contain invalid character';
                } else if (typeof pRulesArray.upp != 'undefined' && pRulesArray.upp != "" && !value.match(regexUpp)){
                    isPassInvalid = false;
                    regxErrMsg = 'New Password contain at least '+pRulesArray.upp+' uppercase character';
                } else if (typeof pRulesArray.low != 'undefined' && pRulesArray.low != "" && !value.match(regexLow)){
                    isPassInvalid = false;
                    regxErrMsg = 'New Password contain at least '+pRulesArray.low+' lowercase Character';
                } else if (typeof pRulesArray.num != 'undefined' && pRulesArray.num != "" && !value.match(regexNum)){
                    isPassInvalid = false;
                    regxErrMsg = 'New Password contain at least '+pRulesArray.num+' number';
                }
                $.validator.messages['regex'] = regxErrMsg;
                return isPassInvalid;
            }
        );
        $( "#frm_agent" ).validate({
            <?php if (empty($agentid)):?>
            rules: {
                login_id: {
                    required: true,
                    minlength: 4,
                    remote: {
                        url: "<?php echo $this->url('task='.$request->getControllerName()."&act=check");?>",
                        type: "post"
                    }
                },
                password: {
                    required: true,
                    minlength: passMinLength,
                    maxlength: passMaxLength,
                    regex: ""
                },
                password_re: {
                    required: true,
                    equalTo: "#password"
                },
            },
            messages: {
                login_id: {
                    required: "Provide <?php echo $agent_title;?> ID",
                    remote: "This ID already exist"
                },
                password: {
                    required: "Provide a password",
                    minlength: "Please enter at least "+passMinLength+" characters",
                    maxlength: "Password must not more than "+passMaxLength+" characters"
                },
                password_re: {
                    required: "Repeat your password",
                    equalTo: "Enter the same password as above"
                }
            }
            <?php else: ?>
            rules: {
                password_re: {
                    equalTo: "#password"
                }
            },
            messages: {
                password_re: {
                    equalTo: "Enter the same password as above"
                }
            }
            <?php endif;?>
        });
    });

</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

    <form name="frm_agent" id="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" enctype="multipart/form-data">
        <input type="hidden" name="agentid" value="<?php if (isset($agentid)) echo $agentid;?>" />

        <input type="hidden" name="status" value="<?php echo $agent->status;?>" />

        <table class="form_table table">
            <tbody>
            <tr class="form_row_head">
                <td colspan=3>External User Information</td>
            </tr>
            <tr class="form_row_alt">
                <td class="form_column_caption"><?php if (empty($agentid)):?><span class="required">*</span> <?php endif;?><?php echo $agent_title;?> ID:</td>
                <td>
                    <?php if (empty($agentid)):?>
                        <input type="text" name="login_id" size="30" maxlength="20" value="<?php echo $agent->login_id;?>" />
                    <?php else:?>
                        <b><?php echo $agent->login_id;?></b>
                    <?php endif;?>
                </td>
                <td></td>
            </tr>

            <tr class="form_row">
                <td class="form_column_caption">Name:</td>
                <td>
                    <input type="text" name="name" size="30" maxlength="30" value="<?php echo $agent->name;?>" />
                </td>
            </tr>

            <?php if (empty($agentid)):?>
                <tr class="form_row">
                    <td class="form_column_caption"><?php if (empty($agentid)):?><span class="required">*</span><?php endif;?> Password: </td>
                    <td>
                        <input type="password" id="password" name="password" size="30" maxlength="<?php echo $maxlength; ?>" value="" autocomplete="off">
                    </td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption"><?php if (empty($agentid)):?><span class="required">*</span><?php endif;?> Retype password: </td>
                    <td>
                        <input type="password" id="password_re" name="password_re" size="30" maxlength="<?php echo $maxlength; ?>" value="" autocomplete="off">
                    </td>
                </tr>
            <?php endif;?>

            <tr class="form_row_alt">
                <td class="form_column_caption">DID:</td>
                <td colspan="1">
                    <a id="select-all" href="javascript:void(0)">select all</a> / <a id="deselect-all" href="javascript:void(0)">deselect all</a>
                    <select id='did_options' name="did_options[]" multiple='multiple'>
                        <?php if (is_array($did_list)): ?>
                            <?php foreach ($did_list as $did):?>
                                <option value="<?php echo $did->did;?>" <?php echo in_array($did->did,$agent->did) ?"selected":''?>> <?php echo $did->did;?></option>
                            <?php endforeach;?>
                        <?php endif;?>
                        <!--<option value='elem_1' selected>elem 1</option>
                        <option value='elem_2'>elem 2</option>
                        <option value='elem_3'>elem 3</option>
                        <option value='elem_4' selected>elem 4</option>
                        <option value='elem_100'>elem 100</option>-->
                    </select>
                </td>
            </tr>

            <tr class="form_row">
                <td colspan="2" class="form_column_submit">
                    <input class="form_submit_button btn btn-success" type="submit" value="  <?php if (!empty($agentid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitagent"> <br><br>
                </td>
            </tr>

            </tbody>
        </table>
    </form>
