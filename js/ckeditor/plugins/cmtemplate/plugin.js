var _tstamp = '';
//var _skillid = '';
CKEDITOR.plugins.add( 'cmtemplate', {
    icons: 'script_add',
    init: function( editor ) {
        // Plugin logic goes here...
		//editor.addCommand( 'abbrDialog', new CKEDITOR.dialogCommand( 'abbrDialog' ) );
		editor.addCommand( 'my_command', {
			exec : function( editor ) {
				//here is where we tell CKEditor what to do.
				//alert('1');
				//editor.insertHtml( 'This text is inserted when clicking on our new button from the CKEditor toolbar' );
				_tstamp = '';
				$.colorbox({iframe:true, width:"90%", height:"90%", href:"index.php?task=email&act=chattemplates&sid="+editor.config._skill_id, onClosed:function(){
					//console.log('============');
					if (_tstamp.length == 10) {
						//editor.insertHtml(_tstamp);
						//editor.setData( "" );
						
						$.ajax({
							type: "POST",
							url: "index.php?task=email&act=chattemplatetext",
							data: { tid: _tstamp, skillid: editor.config._skill_id }
						})
							.done(function( msg ) {
								editor.setData(msg);
								//insertHtml
							});
					}
				}});
			}
		});
		
		editor.ui.addButton( 'Abbr', {
			label: 'Insert Email Template',
			command: 'my_command',
			icon: this.path + 'icons/email_add.png',
			toolbar: 'insert'
		});
		
		
    }
});