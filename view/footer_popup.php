<?php if (!isset($fullload)){?></center>
<?php if(!empty($isAutoResize)){?>
<script type="text/javascript">
		$(function(){			
			try{			
			var wheight=$(parent.window).height()-220;	
			wheight=wheight<4330?433:wheight;	
			var bheight=$("body.popup-body").height()+5;
			var nheight=bheight>wheight?wheight:bheight;	
			
			//$("#pop-up-body").css("min-height",height);			
			 parent.$.colorbox.resize({			       
			     innerHeight:nheight
			 });   			
		
			}catch(e){}
		});
</script>
<?php }?>
<?php }else{?>
<div class="row"></div>
</div>

	</section>
</div>
</div>

<?php }?>


</body>
</html>