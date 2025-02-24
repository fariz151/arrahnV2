jQuery.noConflict();
window.addEvent("domready",function(){
	var parent = 'li:first';
	if(jQuery(".row-fluid").length){
		parent = 'div.control-group:first';
	}
    jQuery("#jform_params_asset-lbl").parents(parent).remove();
	jQuery('#module-sliders li > .btn-group').each(function(){
		if(jQuery(this).find('input').length != 2 ) return;
		if(this.id.indexOf('advancedparams') ==0) return;
		jQuery(this).hide();
		var group = this;
		var el = jQuery(group).find('input:checked');	
		var switchClass ='';

		if(el.val()=='' || el.val()=='0' || el.val()=='no' || el.val()=='false'){
			switchClass = 'no';
		}else{
			switchClass = 'yes';
		}
		var switcher = new Element('div',{'class' : 'switcher-'+switchClass});
		
		var name = this.id.replace('jform_params_','');
		var parent = 'li:first';
		if(jQuery(this).parent().hasClass('controls')){
			parent = 'div.control-group:first';
		}
		jQuery('.'+name + '_' + (switchClass=='yes'?'no':'yes')).parents(parent).hide();
		switcher.inject(group, 'after');
		switcher.addEvent("click", function(){
			var el = jQuery(group).find('input:checked');	
			if(el.val()=='' || el.val()=='0' || el.val()=='no' || el.val()=='false'){
				switcher.setProperty('class','switcher-yes');
				jQuery('.'+name + '_no' ).parents(parent).hide();
				jQuery('.'+name + '_yes' ).parents(parent).show();
			}else {
				switcher.setProperty('class','switcher-no');
				jQuery('.'+name + '_yes' ).parents(parent).hide();
				jQuery('.'+name + '_no' ).parents(parent).show();
			}
			jQuery(group).find('input:not(:checked)').attr('checked',true);
		});
	})

	jQuery('.jt_color').ColorPicker({
		color: '#0000ff',
		onShow: function (colpkr) {
			jQuery(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			jQuery(colpkr).fadeOut(500);
			return false;
		},
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).val("#"+hex);
			//jQuery(el).css('background',jQuery(el).val())
			jQuery(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			jQuery(this).ColorPickerSetColor(this.value);
		}
	})
	.bind('keyup', function(){
		jQuery(this).ColorPickerSetColor(this.value);
	});
	jQuery(".pane-sliders select").each(function(){
		if(this.id.indexOf('advancedparams') ==0) return;
		if(jQuery(this).is(":visible")) {
		//jQuery(this).css("width",parseInt(jQuery(this).width())+20);
		if(jQuery(this).attr('multiple')){
			jQuery(this).css("width","65%");
		}else{
			jQuery(this).css("width",parseInt(jQuery(this).width())+20);
		}
		jQuery(this).chosen()
		};
	})	
	jQuery(".chzn-container").click(function(){
		jQuery(".panel .pane-slider,.panel .panelform").css("overflow","visible");	
	})
	jQuery(".panel .title").click(function(){
		jQuery(".panel .pane-slider,.panel .panelform").css("overflow","hidden");		
	})	
	
	// Group element
	jQuery(".jt_control").each(function(){ 
		if(jQuery(this).parents(parent).css('display')=='none' ) return;
		jQuery(this).change(function(){
			var name = this.id.replace('jform_params_','');
			jQuery(this).find('option').each(function(){
					jQuery('.'+name+'_'+this.value).each(function(){
						jQuery(this).parents(parent).hide();
					})
				})
				
				jQuery('.'+name+'_'+jQuery(this).find('option:selected').val()).each(function(){
					jQuery(this).parents(parent).fadeIn(500);
			})
		});
		jQuery(this).change();
	});
})


