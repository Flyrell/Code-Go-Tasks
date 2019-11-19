import axios from 'axios';

const api = axios.create();

api.interceptors.response.use((response) => response, (error) => {
	throw error;
});

export default api;
