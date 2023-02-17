var detectModeEl = null;

function initEditorForColourSwitch() {
	$('.sceditor-container iframe').each(function() {
		$editor_head = $(this).contents().find('head');
		if ($editor_head && $editor_head.length) {
			let media = '';
			if (colourmode != 'detect') {
				media = ' media="max-width: 1px"';
			}

			let head_style_elem = '';
			if (!$editor_head.find('#colourmodeswitcher_editor_style_element_detect').length) {
				head_style_elem +=
					'<style id="colourmodeswitcher_editor_style_element_detect"'+media+'>'+"\n"+
					'	@import url("'+dark_editor_ss_url+'") (prefers-color-scheme: dark) or (prefers-dark-interface);'+"\n"+
					'</style>';
			}

			if (colourmode == 'dark') {
				if (!$editor_head.find('#colourmodeswitcher_editor_style_element_dark').length) {
					head_style_elem += '<link rel="stylesheet" href="'+dark_editor_ss_url+'" id="colourmodeswitcher_editor_style_element_dark">';
				}
			}

			$editor_head.append(head_style_elem);
		}
	});
}

function removeDarkSS() {
	let darkModeEl = document.getElementById('colourmodeswitcher_style_element_dark');
	if (darkModeEl) {
		darkModeEl.remove();
	}
	$('.sceditor-container iframe').each(function() {
		console.log($(this));
		$editor_head = $(this).contents().find('head');
		if ($editor_head && $editor_head.length) {
			let $darkModeEditorEl = $editor_head.find('#colourmodeswitcher_editor_style_element_dark');
			if ($darkModeEditorEl && $darkModeEditorEl.length) {
				$darkModeEditorEl.remove();
			}
		}
	});
}

function addDarkSS() {
	$('head').append('<link rel="stylesheet" href="'+dark_ss_url+'" id="colourmodeswitcher_style_element_dark" />');
	$('.sceditor-container iframe').each(function() {
		$editor_head = $(this).contents().find('head');
		if ($editor_head && $editor_head.length) {
			$editor_head.append('<link rel="stylesheet" href="'+dark_editor_ss_url+'" id="colourmodeswitcher_editor_style_element_dark">');
		}
	});
}

function disableDetectSS() {
	if (!detectModeEl.hasAttribute('media')) {
		detectModeEl.setAttribute('media', 'max-width: 1px');
	}
	$('.sceditor-container iframe').each(function() {
		$editor_head = $(this).contents().find('head');
		if ($editor_head && $editor_head.length) {
			let $detectModeEditorEl = $editor_head.find('#colourmodeswitcher_editor_style_element_detect');
			if ($detectModeEditorEl.length) {
				$detectModeEditorEl.attr('media', 'max-width: 1px');
			}
		}
	});
}

function enableDetectSS() {
	if (detectModeEl.hasAttribute('media')) {
		detectModeEl.removeAttribute('media');
	}
	$('.sceditor-container iframe').each(function() {
		$editor_head = $(this).contents().find('head');
		if ($editor_head && $editor_head.length) {
			let $detectModeEditorEl = $editor_head.find('#colourmodeswitcher_editor_style_element_detect');
			if ($detectModeEditorEl.length) {
				$detectModeEditorEl.removeAttr('media');
			}
		}
	});
}

function persistColourMode(mode) {
	if (mybb_uid == 0) {
		Cookie.set('colourmode', mode);
	} else	$.get(rootpath+'/xmlhttp.php?action=setcolourmode&colourmode='+encodeURIComponent(mode));
}

$(function() {
	detectModeEl = document.getElementById('colourmodeswitcher_style_element_detect')

	setTimeout(initEditorForColourSwitch, 1);

	$('#colourmode_light').on('click', function(ev) {
		removeDarkSS();
		disableDetectSS();
		document.getElementById('colourmode_detect').className = '';
		document.getElementById('colourmode_dark'  ).className = '';
		this.className = 'active_colourmode_icon';
		persistColourMode('light');
		ev.preventDefault();
	});
	$('#colourmode_dark').on('click', function(ev) {
		disableDetectSS();
		addDarkSS();
		document.getElementById('colourmode_light' ).className = '';
		document.getElementById('colourmode_detect').className = '';
		this.className = 'active_colourmode_icon';
		persistColourMode('dark');
		ev.preventDefault();
	});
	$('#colourmode_detect').on('click', function(ev) {
		enableDetectSS();
		removeDarkSS();
		document.getElementById('colourmode_light').className = '';
		document.getElementById('colourmode_dark' ).className = '';
		this.className = 'active_colourmode_icon';
		persistColourMode('detect');
		ev.preventDefault();
	});
});