

	function plxAjaxForm(link)
	{
		jQuery('form.plexusForm').attr('action', link);
		jQuery('form.plexusForm').submit(function(e) {
			jQuery.post($(this).attr('action'), jQuery(this).serialize(), function(data) {
				if (data == 'CLOSE') {
					jQuery.fancybox.close();
				} else {
					jQuery('#fancybox-inner').html(data);
					plxAjaxForm(link);
				}
			});
			return false;
	    });
	}

	function plxWidgetHtml2AjaxForm(link, editor)
	{
		jQuery('form.plexusWidgetForm').attr('action', link);
		jQuery('form.plexusWidgetForm').ajaxForm({
			beforeSerialize: function($form, options) { 
				if (!editor) {
					jQuery('form.plexusWidgetForm textarea.plexusFormWysiwyg').each(function(){
						tinyMCE.execCommand('mceRemoveControl', false, jQuery(this).attr('id'));
					});
				}
			},
			dataType: 'json',
			success: function(data, statusText) {
				if (data.status == 'OKS') {
					jQuery.fancybox.close();
					jQuery('#' + data.dock).html(data.content);
				} else if (data.status == 'OK') {
					if (editor) {
						tinyMCE.execCommand('mceInsertContent', false, '<div class="widget">' + data.widget + '</div>');
						tinyMCE.execCommand('mceRemoveControl', false, editor);
						tinyMCE.execCommand('mceAddControl', false, editor);
					} else {
						jQuery('#plexusDock' + data.dock).load(plxRoot + 'plxAjax/getDock/' + data.dock + '/' + data.page + '?options=' + data.options + '&ajax=' + plxRoot);
					}
					jQuery.fancybox.close();
				} else if (data.status == 'CLOSE') {
					jQuery.fancybox.close();
				} else {
					jQuery('#fancybox-inner').html(data.content);
					plxWidgetHtml2AjaxForm(link, editor);
				}
			}
	    });
	}

	function plexusFancyboxAjaxForm(link)
	{
		jQuery('form.plexusFancyboxAjaxForm').attr('action', link);
		jQuery('form.plexusFancyboxAjaxForm').ajaxForm({
			success: function(data) {
				if (data == 'OK' || data == 'CLOSE') {
					jQuery.fancybox.close();
				} else {
					jQuery('#fancybox-inner').html(data);
					plexusFancyboxAjaxForm(link);
				}
			}
		});
	}

