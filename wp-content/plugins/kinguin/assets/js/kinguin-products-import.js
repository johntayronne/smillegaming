class ImportProducts {


	constructor() {
		this.setupContainer               = document.getElementById( "kinguin-import-setup" );
		this.importButton                 = this.setupContainer.querySelector( '.start-import' );
		this.importButton.onclick         = () => this.createCacheDir();
		this.ajaxUrl                      = kinguin.ajax_url;
		this.totalPages                   = parseInt( kinguin.total );
		this.progress                     = 0;
		this.progressDB                   = 0;
		this.limit                        = 0;
        this.progressCacheProductsUpdate  = false;
        this.progressImportProductsUpdate = false;
		this.cachedFiles                  = kinguin.cachedFiles;
		this.importIsRunning              = false;
		this.notice                       = kinguin.notice;
	}



	/**
	 * Create cache dir.
	 */
	createCacheDir() {
		let parent = this;
		jQuery.ajax({
			type: 'POST',
			url: parent.ajaxUrl,
			dataType: 'json',
			tryCount : 0,
			retryLimit : 3,
			async: true,
			data: {
				action: 'set_cache',
                nonce:  kinguin.ajax_nonce,
			},
			beforeSend: function() {
				parent.setupContainer.querySelector( '.import-setup__begin' ).style.display   = "none";
				parent.setupContainer.querySelector( '.import-setup__process' ).style.display = "block";
				parent.importButton.disabled = true;
			},
			error : function(xhr, textStatus, errorThrown ) {
				if ( textStatus == 'timeout' ) {
					this.tryCount++;
					if ( this.tryCount <= this.retryLimit ) {
						jQuery.ajax( this );
						return;
					}
					return;
				}
			}
		}).done( function( data ){

			let status           = parent.setupContainer.querySelector( '.status_cache_dir' );
				status.innerHTML = 'Done';
				status.classList.add( 'success' );

			parent.cacheProducts( kinguin.page );
		});
	}



	/**
	 * Import products from Kinguin API to cache files.
	 */
	cacheProducts( page ) {
		let parent = this;
		jQuery.ajax({
			type: 'POST',
			url: parent.ajaxUrl,
			dataType: 'json',
			tryCount: 0,
			retryLimit: 3,
			async: true,
			data: {
				action: 'import_products_to_cache',
                nonce:  kinguin.ajax_nonce,
				page:   page
			},
			error: function (xhr, textStatus, errorThrown) {
				if (textStatus == 'timeout') {
					this.tryCount++;
					if ( this.tryCount <= this.retryLimit ) {
						jQuery.ajax( this );
						return;
					}
					return;
				}
			}
		}).done( function( response ) {
			if ( response.success === true ) {

				let statusContainer = parent.setupContainer.querySelector( '.status_cache' );

                parent.limit = response.data.limit;
				parent.totalPages = response.data.of;            // Set total pages to import from Kinguin.
				parent.cachedFiles.push( response.data.file );   // Add file to array of existing files.

				// Update progress position once, only at the beginning.
                if ( page > 1 && parent.progressCacheProductsUpdate == false ) {
                    parent.progress = parseInt( page );
                    parent.progressCacheProductsUpdate = true;
                } else {
                    // Progress update.
                    parent.progress++;
                }
                parent.setProgressBar();

				let show_total = '';
				if(page < parent.totalPages) {
                    show_total = parent.totalPages;
				} else {
                    show_total = page;
				}
                statusContainer.innerHTML = page + ' of ' + show_total;

				// Create next file.
				if ( page < parent.totalPages ) {
					parent.cacheProducts( parseInt( page ) + 1 );
				}

				// Import products to WooCommerce.
				if ( parent.importIsRunning === false ) {
					parent.importProducts();
				}

				// Set Done after completed.
				if ( page == parent.totalPages ) {
					statusContainer.classList.add( 'success' );
					statusContainer.innerHTML = 'Done';
                    parent.ProcessStart();
				}
			}
		});
	}



	/**
	 * Import products to WooCommerce
	 */
	importProducts() {
		let parent = this;
		if ( parent.cachedFiles.length > 0 ) {
			jQuery.ajax({
				type: 'POST',
				url: parent.ajaxUrl,
				dataType: 'json',
				tryCount: 0,
				retryLimit: 3,
				data: {
					action:      'import_products_to_woocommerce',
                    nonce:       kinguin.ajax_nonce,
					file:        parent.cachedFiles[0],
					total_pages: parent.totalPages,
				},
				beforeSend: function() {
					parent.importIsRunning = true;
				},
				error: function( xhr, textStatus, errorThrown ) {

					parent.importIsRunning = false;
                    parent.setupContainer.querySelector( '.kinguin-dynamic-text' ).style.display = "none";

                    if (textStatus === 'timeout') {
                        this.tryCount++;
                        if ( this.tryCount <= this.retryLimit ) {
                            jQuery.ajax( this );
                            return;
                        }
                        parent.setupContainer.querySelector( '.import-setup__process' ).classList.add( 'error' );
                        parent.setupContainer.querySelector( '.progress' ).innerHTML = 'An error has occurred';
                        return;
                    }

                    if (textStatus === 'error') {
                        parent.setupContainer.querySelector( '.import-setup__process' ).classList.add( 'error' );
                        parent.setupContainer.querySelector( '.progress' ).innerHTML = 'An error has occurred';
                        return;
                    }
				}
			}).done( function( response ) {
                let progressContainer = parent.setupContainer.querySelector( '.progress' );
                let progressCount = parent.setupContainer.querySelector( '.status_cache' );

                if( parent.totalPages === 0 ) {
                    // nothing found
                    parent.setupContainer.querySelector( '.kinguin-dynamic-text' ).style.display = "none";
                    let empty_response = document.createElement('div');
                    empty_response.classList.add( 'kinguin-empty-response' );
                    empty_response.innerHTML = '<b style="color:red; font-size:1.1em">Nothing found</b>';
                    progressContainer.innerHTML = '';
                    progressCount.innerHTML = '';
                    progressContainer.appendChild(empty_response);
                }

				if ( response.success === true ) {

					let statusContainer = parent.setupContainer.querySelector( '.status_import' );
                    let pg = parent.setupContainer.querySelector( '.progress-bar-kinguin' );
                    let koeff = 100 / parent.totalPages;
                    let interrupt_step = 0;

                    if ( parseInt( response.data.page ) > 0 && parent.progressImportProductsUpdate == false ) {
                        // after break
                        parent.progressImportProductsUpdate = true;
                        parent.progressDB++;
                        let interrupt_step = parseInt( parent.progressDB ) * koeff * response.data.page;
                        let step = parseInt( parent.progressDB ) * koeff * response.data.page + '%';
                        pg.style.width = step;

                    } else {

                        if ( parseInt( response.data.page ) > 1 && parent.progressImportProductsUpdate == false ) {
                            parent.progress = parent.progress + parseInt( response.data.page );
                            parent.progressDB++;
                            let step = parseInt( parent.progressDB ) * koeff  + '%';
                            pg.style.width = step;

                        } else {
                            parent.progressDB++;
                            let step =  koeff * response.data.page;
                            let progress_step = step + '%';
                            pg.style.width = progress_step;
                        }
                    }
                    // if filter setting where changed and page is not reload - avoid incorrect progress bars
                    if( parent.totalPages > 0 && ( response.data.page > parent.totalPages ) ) {
                        let progressBar = parent.setupContainer.querySelector( '.progress-db' );
                        progressBar.innerHTML = '<b style="color:red; font-size:1.1em">' +
                            parent.notice +
                            '</b>';
                        return;

                    } else {
                        // show not qty of products but qty of proccessed cached files
                        statusContainer.innerHTML = 'Proccessing file ' + response.data.page + ' of ' + parent.totalPages;

                    }

                    if( response.data.page < 5 ) {
                        parent.ProcessStart();
                    }

                    parent.cachedFiles.shift();
					parent.importProducts();

                    // Set Done after completed.
                    if ( response.data.page === parent.totalPages ) {
                        statusContainer.classList.add( 'success' );
                        statusContainer.innerHTML = 'Done';
                        parent.setupContainer.querySelector( '.import-setup__process' ).style.display = "none";
                        parent.setupContainer.querySelector( '.import-setup__done' ).style.display = "block";
                    }
				}
			});
		}
	}


	/**
	 * Set progressbar state
	 */
	setProgressBar() {
		let importProgress   = this.setupContainer.querySelector( '#import-progress' );
        if( null !== importProgress && importProgress !== undefined ) {
            importProgress.max   = parseInt( this.totalPages );
            importProgress.value = parseInt( this.progress );
        }
	}


    ProcessStart() {

        let parent = this;
        let dynamic_text   = parent.setupContainer.querySelector( '.kinguin-dynamic-text' );
        let statusContainer = parent.setupContainer.querySelector( '.status_import' );

        var Processes = [
            { Percent: 1, ProgressText: 'Please take a little patience' },
            { Percent: 4, ProgressText: 'Please take a little patience.' },
            { Percent: 6, ProgressText: 'Please take a little patience..' },
            { Percent: 8, ProgressText: 'Please take a little patience...' },
            { Percent: 10, ProgressText: 'Please take a little patience...' },
            { Percent: 10, ProgressText: 'Please take a little patience' },
            { Percent: 12, ProgressText: 'Please take a little patience.' },
            { Percent: 14, ProgressText: 'Please take a little patience..' },
            { Percent: 18, ProgressText: 'Please take a little patience...' },
            { Percent: 20, ProgressText: 'Please take a little patience' },
            { Percent: 24, ProgressText: 'Please take a little patience.' },
            { Percent: 26, ProgressText: 'Please take a little patience..' },
            { Percent: 28, ProgressText: 'Please take a little patience...' },
            { Percent: 30, ProgressText: 'Please take a little patience' },
            { Percent: 31, ProgressText: 'Please take a little patience.' },
            { Percent: 32, ProgressText: 'Please take a little patience..' },
            { Percent: 33, ProgressText: 'Please take a little patience...' },
            { Percent: 34, ProgressText: 'We\'re proccessing files' },
            { Percent: 36, ProgressText: 'We\'re proccessing files.' },
            { Percent: 38, ProgressText: 'We\'re proccessing files..' },
            { Percent: 40, ProgressText: 'We\'re proccessing files...' },
            { Percent: 42, ProgressText: 'We\'re proccessing files' },
            { Percent: 44, ProgressText: 'We\'re proccessing files.' },
            { Percent: 46, ProgressText: 'We\'re proccessing files..' },
            { Percent: 48, ProgressText: 'We\'re proccessing files...' },
            { Percent: 50, ProgressText: 'We\'re proccessing files' },
            { Percent: 52, ProgressText: 'We\'re proccessing files.' },
            { Percent: 54, ProgressText: 'We\'re proccessing files..' },
            { Percent: 56, ProgressText: 'We\'re proccessing files...' },
            { Percent: 58, ProgressText: 'We\'re proccessing files' },
            { Percent: 60, ProgressText: 'We\'re proccessing files.' },
            { Percent: 62, ProgressText: 'We\'re proccessing files..' },
            { Percent: 64, ProgressText: 'We\'re proccessing files...' },
            { Percent: 66, ProgressText: 'We\'re proccessing files' },
            { Percent: 70, ProgressText: 'We\'re proccessing files.' },
            { Percent: 74, ProgressText: 'We\'re proccessing files..' },
            { Percent: 78, ProgressText: 'We\'re proccessing files...' },
            { Percent: 80, ProgressText: 'We\'re proccessing files' },
            { Percent: 82, ProgressText: 'We\'re proccessing files.' },
            { Percent: 86, ProgressText: 'We\'re proccessing files..' },
            { Percent: 90, ProgressText: 'We\'re proccessing files...' },
            { Percent: 94, ProgressText: 'We\'re proccessing files' },
            { Percent: 96, ProgressText: 'We\'re proccessing files.' },
            { Percent: 98, ProgressText: 'We\'re proccessing files..' },
            { Percent: 100, ProgressText: 'We\'re proccessing files...' }
        ];
        var Current = 0;

        statusContainer.addEventListener("DOMSubtreeModified", function () {
            // To cancel an interval, pass the timer to clearInterval()
            clearInterval(IntervalProgress);
        });

        var IntervalProgress = setInterval(function() {
            dynamic_text.innerHTML = Processes[Current].ProgressText;
            Current++;
            if (Current >= Processes.length) {
                clearInterval(IntervalProgress);
            }
        }, 1000);
    }
}

new ImportProducts();