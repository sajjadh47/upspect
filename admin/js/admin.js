jQuery( document ).ready( function( $ ) {
	var diffCache = {};
	var cacheKey = '';

	// OPEN MODAL + SCAN
	$( document ).on( 'click', '.vrsndff-diff-check-btn', function( e ) {
		e.preventDefault();

		var btn   = $( this );
		var modal = $( '#vrsndff-diff-modal' );

		$( '#vrsndff-diff-sidebar-tree' ).html( '<p class="vrsndff-scanning-codebase">' + Version_Diff.scanningCodebaseTxti18n + '</p>' );
		$( '#vrsndff-diff-viewscreen' ).html( '<p class="vrsndff-file-selection-msg">' + Version_Diff.fileSelectionTxti18n + '</p>' );

		modal.show();

		$( 'body' ).addClass( 'vrsndff-modal-open' );

		$.post( Version_Diff.ajaxurl, {
			action: 'vrsndff_get_plugin_diff',
			slug: btn.data( 'slug' ),
			file: btn.data( 'file' ),
			version: btn.data( 'version' ),
			nonce: Version_Diff.nonce
		},
		function( response ) {
			if( response.success ) {
				cacheKey = response.data.cache_key;

				$( '#vrsndff-diff-sidebar-tree' ).html( response.data.tree_html );

				var firstFile = $( '#vrsndff-diff-sidebar-tree .vrsndff-diff-tree-file' ).first();
				if( firstFile.length ) {
					firstFile.click();
				}
			} else {
				$( '#vrsndff-diff-sidebar-tree' ).html( '<p class="vrsndff-failed-to-index">' + Version_Diff.failedToIndexTxti18n + '</p>' );
				$( '#vrsndff-diff-viewscreen' ).html( '<p class="vrsndff-failed-message">' + response.data + '</p>' );
			}
		} );
	} );

	// Toggle folder visibility on click
	$( document ).on( 'click', '.vrsndff-diff-tree-dir', function( e ) {
		e.stopPropagation();

		$( this ).next( '.vrsndff-diff-tree-list' ).slideToggle( 150 );
	} );

	// File selection router
	$( document ).on( 'click', '.vrsndff-diff-tree-file', function( e ) {
		e.preventDefault();

		$( '.vrsndff-diff-tree-file' ).removeClass( 'vrsndff-is-active-file' );

		$( this ).addClass( 'vrsndff-is-active-file' );

		$( '#vrsndff-diff-viewscreen' ).html( '<p class="vrsndff-loading-diff">' + Version_Diff.loadingDiffTxti18n + '</p>' );

		var fileKey = $( this ).data( 'filepath' );

		if( diffCache[ fileKey ] ) {
			$( '#vrsndff-diff-viewscreen' ).html( diffCache[ fileKey ] );
		} else {
			$.post( Version_Diff.ajaxurl, {
				action: 'vrsndff_get_file_diff',
				cache_key: cacheKey,
				filepath: fileKey,
				nonce: Version_Diff.nonce
			},
			function ( response ) {
				if ( response.success ) {
					diffCache[ fileKey ] = response.data;

					$( '#vrsndff-diff-viewscreen' ).html( response.data );
				} else {
					$( '#vrsndff-diff-viewscreen' ).html( '<p class="vrsndff-failed-message">' + response.data + '</p>' );
				}
			} );
		}
	} );

	$( document ).on( 'click', '.vrsndff-close-diff-modal-btn', function() {
		$( '#vrsndff-diff-modal' ).hide();

		$( 'body' ).removeClass( 'vrsndff-modal-open' );

		$.post( Version_Diff.ajaxurl, {
			action: 'vrsndff_delete_temporary_files',
			cache_key: cacheKey,
			nonce: Version_Diff.nonce
		} );
	} );
} );