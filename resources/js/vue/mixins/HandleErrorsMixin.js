var HandleErrorsMixin = {
	methods: {
		sendError(msg, type = 'message', user_id, route, request) {
			const data = {
				user_id: user_id,
				type: type,
				route: route,
				request: JSON.stringify(request),
				msg: msg
			}
			axios.post('api/error/msg', data, route, request)
				.catch(err => console.error('send',err))
		},
		sendErrorObject(object, type, user_id, route, request) {
			const data = {
				user_id: user_id,
				type: type,
				route: route,
				request: JSON.stringify(request),
				msg: JSON.stringify(object)
			}
			axios.post('api/error/object', data)
				.catch(err => console.error('send',err))
		},
		handleErrors(err, userId, route, data = []) {
			if (err.response) {
				// The server responded with a status code outside the 2xx range
				console.log('Error response:', err.response);
				this.sendErrorObject(err.response, 'response', userId, route, data)
			} else if (err.request) {
				// The request was made but no response was received
				console.log('Error request:', err.request);
				this.sendErrorObject(err.request, 'request', userId, route, data)
			} else {
				// Something happened in setting up the request that triggered an error
				console.log('Error message:', err.message);
				this.sendError(err.message,'message', userId, route, data)
			}

			myMessage('Es ist ein Fehler aufgetreten: ' + err.message, 'error');
		}
	},
}
export default HandleErrorsMixin;