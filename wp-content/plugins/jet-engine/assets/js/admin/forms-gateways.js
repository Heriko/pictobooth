var JEFormGateways = new Vue({
	el: '#gateways_data',
	data: {
		gateways: JSON.parse( JSON.stringify( JetEngineGatewaysSettings.gateways ) ),
	},
	computed: {
		notificationsList: function() {
			return window.JEBookingFormNotifications.items;
		},
		availableFields: function() {
			return window.JEBookingFormNotifications.availableFields;
		},
		hasRedirectNotification: function() {

			if ( ! this.notificationsList || ! this.notificationsList.length ) {
				return false;
			}

			for ( var i = 0; i < this.notificationsList.length; i++) {

				if ( 'redirect' === this.notificationsList[ i ].type ) {
					return true;
				}

			}

			return false;
		}
	},
	methods: {
		hasPostNotification: function() {

			if ( ! this.notificationsList || ! this.notificationsList.length ) {
				return false;
			}

			for ( var i = 0; i < this.notificationsList.length; i++) {

				if ( 'insert_post' === this.notificationsList[ i ].type ) {
					return true;
				}

			}

			return false;

		},
		getNotificationLabel: function( notification ) {

			var result = notification.type;

			if ( 'email' === result ) {
				result += ' to ' + notification.mail_to;

				if ( 'custom' === notification.mail_to ) {
					result += '/' + notification.custom_email;
				} else if ( 'form' === notification.mail_to ) {
					result += '/' + notification.from_field;
				}

				result += ': ' + notification.email.subject;

			}

			return result;
		}
	}
});
