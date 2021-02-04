(function( $, JetEngineMBConfig ) {

	'use strict';

	window.JetEngineMB = new Vue( {
		el: '#jet_mb_form',
		template: '#jet-mb-form',
		data: {
			generalSettings: JetEngineMBConfig.general_settings,
			metaFields: JetEngineMBConfig.meta_fields,
			allowedSources: JetEngineMBConfig.sources,
			postTypes: JetEngineMBConfig.post_types,
			taxonomies: JetEngineMBConfig.taxonomies,
			buttonLabel: JetEngineMBConfig.edit_button_label,
			isEdit: JetEngineMBConfig.item_id,
			helpLinks: JetEngineMBConfig.help_links,
			showDeleteDialog: false,
			saving: false,
			errors: {
				name: false,
				slug: false,
			},
			errorNotices: [],
		},
		mounted: function() {

			var self = this;

			this.ensureDefaultArrays();

			if ( JetEngineMBConfig.item_id ) {

				wp.apiFetch( {
					method: 'get',
					path: JetEngineMBConfig.api_path_get + JetEngineMBConfig.item_id,
				} ).then( function( response ) {

					if ( response.success && response.data ) {

						self.generalSettings = response.data.general_settings;
						self.metaFields      = response.data.meta_fields;

					} else {
						if ( response.notices.length ) {
							response.notices.forEach( function( notice ) {
								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 15000,
								} );
								//self.errorNotices.push( notice.message );
							} );
						}
					}
				} ).then( function() {

					if ( self.$refs.allowed_posts.currentValues.length ) {
						self.$refs.allowed_posts.remoteUpdateSelected();
					}

					if ( self.$refs.excluded_posts.currentValues.length ) {
						self.$refs.excluded_posts.remoteUpdateSelected();
					}

				} ).catch( function( e ) {
					console.log( e );
				} );

			}
		},
		methods: {
			buildQuery: function( params ) {
				return Object.keys( params ).map(function( key ) {
					return key + '=' + params[ key ];
				}).join( '&' );
			},
			getPosts: function( query, ids ) {

				var postTypes = this.generalSettings.allowed_post_type.join( ',' );

				if ( ids.length ) {
					ids = ids.join( ',' );
				}

				return wp.apiFetch( {
					method: 'get',
					path: JetEngineMBConfig.api_path_search + '?' + this.buildQuery( {
						query: query,
						ids: ids,
						post_type: postTypes,
					} )
				} );

			},
			ensureDefaultArrays: function() {

				if ( ! this.generalSettings.allowed_tax ) {
					this.$set( this.generalSettings, 'allowed_tax', [] );
				}

				if ( ! this.generalSettings.allowed_post_type ) {
					this.$set( this.generalSettings, 'allowed_post_type', [] );
				}

				if ( ! this.generalSettings.allowed_posts ) {
					this.$set( this.generalSettings, 'allowed_posts', [] );
				}

				if ( ! this.generalSettings.excluded_posts ) {
					this.$set( this.generalSettings, 'excluded_posts', [] );
				}

			},
			handleFocus: function( where ) {

				if ( this.errors[ where ] ) {
					this.$set( this.errors, where, false );
					this.$CXNotice.close( where );
					//this.errorNotices.splice( 0, this.errorNotices.length );
				}

			},
			save: function() {

				var self      = this,
					hasErrors = false,
					path      = JetEngineMBConfig.api_path_edit;

				if ( JetEngineMBConfig.item_id ) {
					path += JetEngineMBConfig.item_id;
				}

				if ( ! self.generalSettings.name ) {
					self.$set( this.errors, 'name', true );

					self.$CXNotice.add( {
						message: JetEngineMBConfig.notices.name,
						type: 'error',
						duration: 7000,
					}, 'name' );

					//self.errorNotices.push( JetEngineMBConfig.notices.name );
					hasErrors = true;
				}

				if ( hasErrors ) {
					return;
				}

				self.saving = true;

				wp.apiFetch( {
					method: 'post',
					path: path,
					data: {
						general_settings: self.generalSettings,
						meta_fields: self.metaFields,
					}
				} ).then( function( response ) {

					if ( response.success ) {
						if ( JetEngineMBConfig.redirect ) {
							window.location = JetEngineMBConfig.redirect.replace( /%id%/, response.item_id );
						} else {

							self.$CXNotice.add( {
								message: JetEngineMBConfig.notices.success,
								type: 'success',
							} );

							self.saving = false;
						}
					} else {
						if ( response.notices.length ) {
							response.notices.forEach( function( notice ) {

								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 7000,
								} );

								//self.errorNotices.push( notice.message );
							} );
						}
					}
				} ).catch( function( response ) {
					//self.errorNotices.push( response.message );

					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 7000,
					} );

					self.saving = false;
				} );

			},
		}
	} );

})( jQuery, window.JetEngineMBConfig );
