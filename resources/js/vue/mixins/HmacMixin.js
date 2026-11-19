const php = require('phpjs'),
	HmacMixin = {
		publicKey: process.env.MIX_PUBLIC_KEY,
		secretKey: process.env.MIX_SECRET_KEY,
		created: function () {
		},
		methods: {
			hmac(path, method = 'GET', data = []) {
				console.info('data', data)
				const
					json = data ? php.json_encode(data) : null,
//					json = JSON.stringify(data),
//					md5 = php.md5(JSON.stringify(data).replace(/\s+/g, '')),
					md5 = json ? php.md5(json.replace(/\s+/g, '')) : '',
					string = method.toUpperCase() + location.host + path.replace(/\//g, '') + md5,
					hash = CryptoJS.HmacSHA256(string, CryptoJS.enc.Utf8.parse(HmacMixin.secretKey)).toString(),
					result = php.base64_encode(hash);
	//				result = hash;

				console.info('md5', md5)

				return result;
			},
			hmacHeader(path, method = 'GET', data = []) {
				return {
					headers: {
						[HmacMixin.publicKey]: this.hmac(path, method, data),
					}
				}
			}
	},
}
export default HmacMixin;
