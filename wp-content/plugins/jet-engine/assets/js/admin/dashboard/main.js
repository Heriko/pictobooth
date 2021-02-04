(function( $, dashboardConfig ) {

	'use strict';

	window.JetEngineDashboard = new Vue( {
		el: '#jet_engine_dashboard',
		data: {
			availableModules: dashboardConfig.available_modules,
			activeModules: dashboardConfig.active_modules,
			componentsList: dashboardConfig.components_list,
			shortcode: {
				component: '',
				meta_field: '',
				page: '',
				field: '',
			},
			saving: false,
			result: false,
			errorMessage: '',
			successMessage: '',
		},
		mounted: function() {
			this.$el.className = 'is-mounted';
		},
		computed: {
			generatedShortcode: function() {

				var result = 'jet_engine ';

				if ( ! this.shortcode.component ) {
					return result;
				}

				result += ' component="' + this.shortcode.component + '"';

				switch ( this.shortcode.component ) {

					case 'meta_field':
						result += ' field="' + this.shortcode.meta_field + '"';

						if ( this.shortcode.post_id ) {
							result += ' post_id="' + this.shortcode.post_id + '"';
						}

						break;

					case 'option':
						result += ' page="' + this.shortcode.page + '" field="' + this.shortcode.field + '"';
						break;

				}

				return result;

			},
		},
		methods: {
			saveModules: function() {

				var self = this;

				self.saving = true;

				jQuery.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'jet_engine_save_modules',
						modules: self.activeModules,
					},
				}).done( function( response ) {

					self.saving = false;

					if ( response.success ) {
						self.result = 'success';

						if ( ! response.data.reload ) {
							self.successMessage = dashboardConfig.messages.saved;
						} else {

							self.successMessage = dashboardConfig.messages.saved_and_reload;

							setTimeout( function() {
								window.location.reload();
							}, 4000 );

						}

					} else {
						self.result = 'error';
						self.errorMessage = 'Error!';
					}

					self.hideNotice();

				} ).fail( function( e, textStatus ) {
					self.result       = 'error';
					self.saving       = false;
					self.errorMessage = e.statusText;
					self.hideNotice();
				} );

			},
			hideNotice: function() {
				var self = this;
				setTimeout( function() {
					self.result       = false;
					self.errorMessage = '';
				}, 8000 );
			},
		}
	} );

})( jQuery, window.JetEngineDashboardConfig );
