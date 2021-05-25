<link rel="stylesheet" href="css/form.css" type="text/css">

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form action="<?php echo $this->url('task='.$request->getControllerName()."&act=settslmethod");?>" method="post">
<table class="form_table table" border="0" width="630" align="center" cellpadding="6" cellspacing="0">
	<tr class="form_row_head">
		<td colspan=2>Service level calculation (% within service level)</td>
	</tr>
  	<tr class="form_row">
		<td class="form_column_caption" width="18%">Method A : </td>
		<td>
			<input type="radio" name="sl_method" id="sl_method_a" value="A"<?php if ($sl_method == 'A') echo ' checked'?> disabled="disabled" />
            <label for="sl_method_a">( Ans. within service Level / Calls offered ) x 100</label>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Method B : </td>
		<td>
			<input type="radio" name="sl_method" id="sl_method_b" value="B"<?php if ($sl_method == 'B') echo ' checked'?> disabled="disabled" />
            <label for="sl_method_b">( Ans. within service Level / ( Calls answered + Abd. calls aft. threshold ) ) x 100</label>
		</td>
	</tr>
<?php /*
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
        	<input class="form_submit_button btn btn-success" type="submit" name="save" value="Save" />
		</td>
	</tr>
*/ ?>
</table>
</form>
