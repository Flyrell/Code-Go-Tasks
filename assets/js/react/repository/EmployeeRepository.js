import api from '../api';

export default class EmployeeRepository {

	constructor(endpoints) {
		this.endpoints = endpoints;
	}

	fetchSlice = (params) => api.get(this.endpoints.search, { params }).then(response => {
		const data = response.data;

		data.documents.forEach((employee) => {
			// Sort skills
			employee.skills = employee.skills.sort((skillA, skillB) => skillA.level > skillB.level ? -1 : 1);
		});

		return data;
	});
}
