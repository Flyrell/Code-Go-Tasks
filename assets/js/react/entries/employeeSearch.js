import React from 'react';
import ReactDOM from 'react-dom';
import EmployeeTable from '../components/EmployeeTable';
import ErrorBoundary from '../components/ErrorBoundary';
import BrowserHistory from '../services/BrowserHistory';
import './style/employeeSearch.scss';

const root = document.getElementById('employee-table');
const data = JSON.parse(root.dataset.init);

// https://medium.freecodecamp.org/you-might-not-need-react-router-38673620f3d
const render = (location) => {
	//console.log('Rendering React DOM due to location change: ' + location.pathname + location.search);
	ReactDOM.render(
		<ErrorBoundary name={'Employee Table'}>
			<EmployeeTable {...data} location={location} />
		</ErrorBoundary>,
		root
	);
};

render(BrowserHistory.location); // render the current URL
BrowserHistory.listen(render);   // render subsequent URLs