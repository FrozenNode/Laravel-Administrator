/*
 * @file change event plugin for CKEditor
 * Copyright (C) 2011 Alfonso Martínez de Lizarrondo
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 */

 // Keeps track of changes to the content and fires a "change" event
CKEDITOR.plugins.add( 'onchange',
{
	init : function( editor )
	{
//		// Test:
//		editor.on( 'change', function(e) { console.log( e ) });

		var timer,
			theMutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver,
			observer;
// http://dvcs.w3.org/hg/domcore/raw-file/tip/Overview.html#mutation-observers
// http://hacks.mozilla.org/2012/05/dom-mutationobserver-reacting-to-dom-changes-without-killing-browser-performance/

		// Avoid firing the event too often
		function somethingChanged()
		{
			// don't fire events if the editor is readOnly as they are false detections
			if (editor.readOnly)
				return;

			if (timer)
				return;

			timer = setTimeout( function() {
				timer = 0;
				editor.fire( 'change' );
			}, editor.config.minimumChangeMilliseconds || 100);
		}
		// Kill the timer on editor destroy
		editor.on( 'destroy', function() { if ( timer ) clearTimeout( timer ); timer = null; });

		// in theory this block should be enabled only for browsers that don't support MutationObservers,
		// but it doesn't seem to fire correctly in all the situations. Maybe in the future...
		{
			// Set several listeners to watch for changes to the content
			editor.on( 'saveSnapshot', function( evt )
			{
				if ( !evt.data || !evt.data.contentOnly )
					somethingChanged();
			});

			var undoCmd = editor.getCommand('undo');
			undoCmd && undoCmd.on( 'afterUndo', somethingChanged);
			var redoCmd = editor.getCommand('redo');
			redoCmd && redoCmd.on( 'afterRedo', somethingChanged);

			editor.on( 'afterCommandExec', function( event )
			{
				if ( event.data.name == 'source' )
					return;

				if ( event.data.command.canUndo !== false )
					somethingChanged();
			} );
		}

		if ( theMutationObserver )
		{
			observer = new theMutationObserver( function( mutations ) {
				somethingChanged();
			} );
		}

		// Changes in WYSIWYG mode
		editor.on( 'contentDom', function()
			{
				if ( observer )
				{
					// A notification is fired right now, but we don't want it so soon
					setTimeout( function() {
						observer.observe( editor.document.getBody().$, {
							attributes: true,
							childList: true,
							characterData: true
						  });
					}, 100);
				}

				editor.document.on( 'keydown', function( event )
					{
						// Do not capture CTRL hotkeys.
						if ( event.data.$.ctrlKey ||event.data.$.metaKey )
							return;

						var keyCode = event.data.$.keyCode;
						// Filter movement keys and related
						if (keyCode==8 || keyCode == 13 || keyCode == 32 || ( keyCode >= 46 && keyCode <= 90) || ( keyCode >= 96 && keyCode <= 111) || ( keyCode >= 186 && keyCode <= 222) || keyCode == 229)
							somethingChanged();
					});

					// Firefox OK
				editor.document.on( 'drop', somethingChanged);
					// IE OK
				editor.document.getBody().on( 'drop', somethingChanged);
			});

		// Detect changes in source mode
		editor.on( 'mode', function( e )
			{
				if ( editor.mode != 'source' )
					return;

				var textarea = (editor.textarea || editor._.editable);
				textarea.on( 'keydown', function( event )
					{
						// Do not capture CTRL hotkeys.
						if ( !event.data.$.ctrlKey && !event.data.$.metaKey )
							somethingChanged();
					});

				textarea.on( 'drop', somethingChanged);
				textarea.on( 'input', somethingChanged);
				if (CKEDITOR.env.ie)
				{
					textarea.on( 'cut', somethingChanged);
					textarea.on( 'paste', somethingChanged);
				}
			});

	} //Init
} );
