(function() {
	tinymce.create('tinymce.plugins.PlexusPlugin', {
		init : function(ed, url) {
			var t = this, dialect = ed.getParam('bbcode_dialect', 'punbb').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t['_' + dialect + '_bbcode2html'](o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t['_' + dialect + '_bbcode2html'](o.content);

				if (o.get)
					o.content = t['_' + dialect + '_html2bbcode'](o.content);
			});

			ed.addCommand('insertInlineCodeTag', function() {
				var code = tinymce.DOM.encode(tinyMCE.activeEditor.selection.getContent({format: 'text'}));
				code = code.replace(/\n/g, '<br />');
				tinyMCE.execCommand('mceReplaceContent', false, '<code>'+code+'</code>')
			});

			ed.addCommand('upload', function() {
				plxSelectorLoad(plxRoot + 'plx-api/editor/upload?editorId=' + ed.id + '&ajax=' + plxRoot);
			});

			ed.addCommand('insertImage', function() {
				plxSelectorLoad(plxRoot + 'plx-api/editor/image?editorId=' + ed.id + '&ajax=' + plxRoot);
			});

			ed.addCommand('insertGallery', function() {
				plxSelectorLoad(plxRoot + 'plx-api/editor/gallery?editorId=' + ed.id + '&ajax=' + plxRoot);
			});

			ed.addCommand('insertVideo', function() {
				plxSelectorLoad(plxRoot + 'plx-api/editor/video?editorId=' + ed.id + '&ajax=' + plxRoot);
			});

			ed.addCommand('insertFile', function() {
				plxSelectorLoad(plxRoot + 'plx-api/editor/file?editorId=' + ed.id + '&ajax=' + plxRoot);
			});

			ed.addCommand('embedWidget', function() {
				plxSelectorLoad(plxRoot + 'plx-api/editor/embeddings?editorId=' + ed.id + '&contentId=' + plxId + '&ajax=' + plxRoot);
			});

			ed.addButton('codebutton', {
				title: 'Code (inline)',
				cmd: 'insertInlineCodeTag',
				image: url + '/codebutton.png'
			});

			ed.addButton('uploadbutton', {
				title: 'Upload',
				cmd: 'upload',
				image: url + '/uploadbutton.png'
			});

			ed.addButton('imagebutton', {
				title: 'Insert Image',
				cmd: 'insertImage',
				image: url + '/imagebutton.png'
			});

			ed.addButton('gallerybutton', {
				title: 'Insert Gallery',
				cmd: 'insertGallery',
				image: url + '/gallerybutton.png'
			});

			ed.addButton('videobutton', {
				title: 'Insert Video',
				cmd: 'insertVideo',
				image: url + '/videobutton.png'
			});

			ed.addButton('filebutton', {
				title: 'Insert File',
				cmd: 'insertFile',
				image: url + '/filebutton.png'
			});

			ed.addButton('embedbutton', {
				title: 'Embed Widget',
				cmd: 'embedWidget',
				image: url + '/embedbutton.png'
			});
		},

		// HTML -> BBCode in PunBB dialect
		_punbb_html2bbcode : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};

			rep(/<div class=\"plxEditorWidget\">[\s\S]*?<div class=\"plxEditorId\">(.*?)<\/div>[\s\S]*?<\/div>/gi , "<div class=\"widget\">$1</div>");
			rep(/<div class=\"plxEditorVideo\">[\s\S]*?<div class=\"plxEditorId\">(.*?)<\/div>[\s\S]*?<\/div>/gi, "<div class=\"video\">$1</div>");
			rep(/<div class=\"plxEditorGallery\">[\s\S]*?<div class=\"plxEditorId\">(.*?)<\/div>[\s\S]*?<\/div>/gi, "<div class=\"gallery\">$1</div>");

			return s; 
		},

		// BBCode -> HTML from PunBB dialect
		_punbb_bbcode2html : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};

			// example: [b] to <strong>
			rep(/<div class=\"widget\">(.*?)<\/div>/gi,"<div class=\"plxEditorWidget\"><div class=\"plxEditorEdit\">Edit</div> <div class=\"plxEditorRemovePrompt\">Remove</div> <div class=\"plxEditorId\">$1</div></div>");
			rep(/<div class=\"video\">(.*?)<\/div>/gi,"<div class=\"plxEditorVideo\"><div class=\"plxEditorVideoSymbol\" title=\"Video\">Video</div> <div class=\"plxEditorRemove\">Remove</div> <div class=\"plxEditorId\">$1</div></div>");
			rep(/<div class=\"gallery\">(.*?)<\/div>/gi,"<div class=\"plxEditorGallery\"><div class=\"plxEditorGallerySymbol\" title=\"Gallery\">Gallery</div> <div class=\"plxEditorRemove\">Remove</div> <div class=\"plxEditorId\">$1</div></div>");

			return s; 
		}	
	});

	// Register plugin
	tinymce.PluginManager.add('plexus', tinymce.plugins.PlexusPlugin);
})();

function plxSelectorLoad(url)
{
	jQuery.fancybox({
		href: url,
		overlayOpacity: 0.66,
		overlayColor: '#000',
		ajax: {
			dataFilter: function(data, type) {
				data = eval('(' + data + ')');
				return data.data;
			}
		}
	});
}