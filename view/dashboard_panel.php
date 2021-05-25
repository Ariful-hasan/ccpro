<script type="text/javascript">
var winPopupWidth = 400;
var winPopupHeight = 400;
var newPopupWindow;
if (screen){
	winPopupWidth = screen.width;
	winPopupHeight = screen.height;
}

function popupWindow(win)
{
	newPopupWindow = window.open(win,'newWin','toolbars=no,menubar=no,location=no,scrollbars=no,resizable=yes,width='+winPopupWidth+',height='+winPopupHeight+',left=0,top=0');
	newPopupWindow.focus();
}

function loadDB()
{
	popupWindow('<?php echo $this->url('task=report&act=dashboard');?>');
}

window.onload=loadDB;

</script>

<table class="form_table" border="0" width="350" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row">
		<td colspan="2" align="center">
			<a href="javascript:void(0);" onclick="popupWindow('<?php echo $this->url('task=report&act=dashboard');?>')" style="text-decoration:none;">Click here to access dashboard panel</a></td>
		</td>
	</tr>
</tbody>
</table>
