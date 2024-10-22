class KinguinAccordion {
	constructor(container) {
		this.container = document.querySelector( container );
		this.tabs = this.getTabs();
		this.expandFirst();
		this.addEvents();
	}

	getTabs() {
		return this.container.querySelectorAll( '.accordion__item' );
	}

	expandFirst() {
		if ( this.tabs.length > 0 ) {
			this.tabs[0].classList.add( 'is-shown' );
		}
	}

	closeShown() {
		Array.from( this.tabs ).forEach( ( item ) => {
			item.classList.remove( 'is-shown' );
		} );
	}

	addEvents() {
		let patent = this;
		if ( this.tabs.length > 0 ) {
			Array.from( this.tabs ).forEach( ( item ) => {
				item.querySelector( '.accordion__item__name' ).addEventListener( 'click', ( element ) => {
					this.closeShown();
					element.target.parentNode.parentNode.classList.add( 'is-shown' );
				});
			} );
		}
	}
}