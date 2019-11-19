import api from '../api';

export default class SkillRepository {

	skillCache = {};

	constructor(endpoints) {
		this.endpoints = endpoints;
	}

	/**
	 * Lookup skill options by query string
	 *
	 * @param {string} search
	 *
	 * @return {Promise<SkillOption[]>}
	 */
	autocomplete = (search) => api.get(this.endpoints.autocomplete, {
		params: {
			search
		}
	}).then(response => response.data);

	/**
	 * Fetch skill options from the server.
	 *
	 * @param {string[]} ids
	 *
	 * @return {Promise<SkillOption[]>}
	 */
	findByIds = (ids) => api.get(this.endpoints.findByIds, {
		params: {
			ids
		}
	}).then(response => response.data);

	/**
	 * Try to look up options in cache, otherwise fetch them from the server.
	 *
	 * @param {string[]} ids
	 *
	 * @return {Promise<SkillOption[]>}
	 */
	findByIdsWithCache = (ids) => new Promise((resolve, reject) => {
		const options = [];
		const idsToFind = [];
		ids.forEach((id) => {
			if (this.skillCache.hasOwnProperty(id)) {
				options.push(this.skillCache[id]);
			}
			else {
				idsToFind.push(id);
			}
		});

		if (idsToFind.length === 0) {
			// All options were already cached, resolve the promise right away
			resolve(options);
		}
		else {
			// We need to fetch some options from server and then resolve the promise
			this.findByIds(idsToFind).then(fetchedOptions => {
				fetchedOptions.forEach((option) => {
					options.push(option);
					// Save queried options to cache
					this.skillCache[option.value] = option;
				});

				resolve(options);
			}).catch(error => reject(error));
		}
	});

	/**
	 * @param {SkillOption[]} options
	 */
	addOptionsToCache = (options) => options.forEach((option) => this.skillCache[option.value] = option);
}
