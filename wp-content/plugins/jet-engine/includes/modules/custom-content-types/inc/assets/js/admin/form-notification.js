Vue.component( 'jet-cct-notification', {
	template: '#jet-cct-notification',
	props: [ 'value', 'fields', 'statuses', 'contentTypes', 'fetchPath' ],
	data: function () {
		return {
			result: {},
			formFields: [],
			typeFields: [],
			isLoading: false,
		};
	},
	created: function() {

		this.result = this.value;
		this.formFields = this.fields;

		if ( ! this.result ) {
			this.result = {};
		}

		if ( ! this.result.fields_map || 'object' !== typeof this.result.fields_map ) {
			this.$set( this.result, 'fields_map', {} );
		}

		this.fetchTypeFields();

	},
	methods: {
		setField: function( $event, key ) {

			var value = $event.target.value;

			this.$set( this.result, key, value );
			this.$emit( 'input', this.result );

			if ( 'type' === key ) {
				this.fetchTypeFields();
			}

		},
		setFieldsMap: function( $event, field ) {
			var value = $event.target.value;
			this.$set( this.result.fields_map, field, value );
			this.$emit( 'input', this.result );
		},
		fetchTypeFields: function() {

			if ( ! this.result.type ) {
				return;
			}

			this.isLoading = true;

			wp.apiFetch( {
				method: 'get',
				path: this.fetchPath + '?type=' + this.result.type,
			} ).then( ( response ) => {

				if ( response.success && response.fields ) {

					for ( var i = 0; i < response.fields.length; i++ ) {

						if ( '_ID' === response.fields[ i ].value ) {
							response.fields[ i ].label += ' (will update the item)';
						}

						this.typeFields.push( response.fields[ i ] );
					};

				} else {

					let message = '';

					for ( var i = 0; i < response.notices.length; i++) {
						message += response.notices[ i ] + '; ';
					};

					alert( message );

				}

				this.isLoading = false;
				this.$forceUpdate();

			} ).catch( ( e ) => {
				console.log( e );
				this.isLoading = false;
				alert( e );
			} );

		},
	}
});