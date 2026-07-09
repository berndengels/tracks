var DownloadMixin = {
	methods: {
		downloadItem ({ url, params = null, type = 'application/pdf',label = 'Download' }) {
			axios.get(url, { responseType: 'blob' }, params)
				.then(resp => {
					if(resp.data) {
						const blob = new Blob([resp.data], { type: type })
						const link = document.createElement('a')
						link.href = URL.createObjectURL(blob)
						link.target = '_blank'
						link.download = label
						link.click()
						URL.revokeObjectURL(link.href)
					} else {
						myMessage('Download Fehler','error')
					}
				}).catch(err => myMessage(err,'error'))
		}
	},
}
export default DownloadMixin;
