import qs from 'query-string';

export default class UrlService {

	/**
	 * @param {string} url
	 * @return {string}
	 */
	static getQueryString(url) {
		const index = url.indexOf('?');
		if (index > -1) {
			return url.substring(index + 1);
		}
		return '';
	}

	/**
	 * @param {string} url
	 * @return {string}
	 */
	static getUrlWithoutQueryString(url) {
		const index = url.indexOf('?');
		if (index > -1) {
			return url.substring(0, index);
		}
		return url;
	}

	static buildFullUrl(url, query) {
		const urlWithoutQuery = UrlService.getUrlWithoutQueryString(url);
		const queryString = qs.stringify(query, { arrayFormat: 'bracket' });
		return queryString
			? `${urlWithoutQuery}?${queryString}`
			: urlWithoutQuery
		;
	}

	static path(url) {
		return window.location.origin + url;
	}
}