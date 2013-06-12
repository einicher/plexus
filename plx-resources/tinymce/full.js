var plexusFullTinyMCE = {
    mode: 'none',
    plugins: 'plexus,table',
	theme: 'advanced',
    skin: 'o2k7',
    skin_variant: 'silver',
	theme_advanced_toolbar_location: 'top',
	theme_advanced_statusbar_location : 'bottom',
	theme_advanced_resizing : true,
	theme_advanced_resize_horizontal : false,
	theme_advanced_toolbar_align: 'left',
	theme_advanced_blockformats: 'p,h1,h2,h3,h4,blockquote,pre,dt,dd',
	theme_advanced_buttons1: 'styleprops,formatselect,bold,italic,strikethrough,|,forecolor,backcolor,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,bullist,numlist,indent,outdent,|,removeformat',
	theme_advanced_buttons2: 'uploadbutton,|,imagebutton,gallerybutton,videobutton,filebutton,embedbutton,|,codebutton,|,tablecontrols',
	theme_advanced_buttons3: '',

	valid_elements: '@[class|id],a[!href|target|rel|class],h1,h2,h3,h4,p,br,img[!src|alt=|width|height],strong/b,em/i,ul,ol,li,big,small,blockquote,cite,code,samp,pre,sub,sup,del,div[!class|!style],span[!class|!style],audio,video,object,embed,param,dl,dt,dd,table[width|height|cellpadding|cellspacing|border|class],tr,th[rowspan|colspan],td[rowspan|colspan|nowrap],iframe[width|height|scrolling|frameborder|src]',
	content_css: root + 'plx-resources/tinymce/content.css?get=9',
	fix_list_elements: true,
    convert_urls : false,
    force_p_newlines : true,
    force_br_newlines : false,
    forced_root_block : 'p',
    apply_source_formatting: true,
    convert_fonts_to_spans : true,
    remove_trailing_nbsp : true,
    remove_linebreaks : false,
    entity_encoding : 'raw',
    verify_html : true,
	cleanup_on_startup: true,

	valid_styles: {
		'*': 'text-align,color,background-color'
	},

	formats: {
		alignleft: {selector: 'p,h1,h2,h3,h4,ul,ol,li,table,img', classes: 'alignLeft'},
		aligncenter: {selector: 'p,h1,h2,h3,h4,ul,ol,li,table,img', classes: 'alignCenter'},
		alignright: {selector: 'p,h1,h2,h3,h4,ul,ol,li,table,img', classes: 'alignRight'},
		alignfull: {selector: 'p,h1,h2,h3,h4,ul,ol,li,table,img', classes: 'alignJustify'},
		bold: {inline: 'strong'},
		italic: {inline: 'em'},
		strikethrough: {inline: 'del'},
		indentation: {block: 'blockquote'}
	},
	setup : function(ed) {
		ed.onClick.add(function(ed, e) {
			if (e.target.className == 'plxEditorEdit') {
				var id = jQuery(e.target).siblings('.plxEditorId').html();
				var href = plxRoot + 'PlexusEditWidget/' + id + '?embed';
				var a = document.createElement('a');
				jQuery(a).fancybox({
					href: href,
					width: 500,
					autoDimensions: false,
					centerOnScroll: true,
					overlayOpacity: 0.5,
					overlayColor: '#000',
					transitionIn: 'elastic',
					transitionOut: 'elastic',
					onComplete: function() {
						plxWidgetHtml2AjaxForm(href);
						jQuery('form.plexusForm button.remove').click(function() {
							var action = jQuery('form.plexusForm').attr('action') + '?plexusRemove';
							jQuery('form.plexusForm').attr('action', action);
						});
					}
				}).trigger('click');
			}
			if (e.target.className == 'plxEditorRemovePrompt') {
				var c = confirm('Do you really want to remove this widget?');
				if (c) {
					jQuery(e.target).parent().remove();
				}
			}
			if (e.target.className == 'plxEditorRemove') {
				jQuery(e.target).parent().remove();
			}
			if (e.target.className == 'plxEditorGallerySymbol') {
				var id = jQuery(e.target).parent().children('.plxEditorId').html();
				window.open(plxHome + 'permalink/' + id);
			}
			if (e.target.className == 'plxEditorVideoSymbol') {
				var id = jQuery(e.target).parent().children('.plxEditorId').html();
				window.open(plxHome + 'permalink/' + id);
			}
		});
	}
};

var plexusLightTinyMCE = jQuery.extend(true, {}, plexusFullTinyMCE);
plexusLightTinyMCE.theme_advanced_buttons2 = '';

