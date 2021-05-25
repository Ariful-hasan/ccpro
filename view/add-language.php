
<?php if (!empty($errMsg)):?>

    <div class="alert <?php echo ($errType === 0) ? 'alert-success' : 'alert-error' ?> ">

        <?php echo $errMsg;?>

   </div>

<?php endif;?>


 <div class="col-md-12 col-sm-12">
     <div class="row">
        <form name="frm_add_language" method="post" action="<?php echo $this->url('task=language&act=add');?>">

            <div class="col-md-12 col-sm-12 col-lg-12">

                <div class="form-group form-group-sm">
                    <label for="acd_mode" class="control-label"> Language</label>
                    <?php
                        $options=GetLanguageList();
                        foreach ($lan_result as $lang){
                            if(isset($options[$lang->lang_key])){
                                unset($options[$lang->lang_key]);
                            }
                        }
                        $this->html_options('lang_key', 'acd_mode', $options,'','','','form-control');
                    ?>
                </div>

                <div class="pull-right">
                    <button class=" btn btn-success" type="submit"> <i class="fa fa-save"></i> Add Language &nbsp;</button> &nbsp;
                    <a class=" btn btn-danger" href="#" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Close </a>
                </div>
            </div>
        </form>
     </div>
 </div>