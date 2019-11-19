import React, { Component } from 'react';
import EmployeeRepository from '../repository/EmployeeRepository';
import SkillRepository from '../repository/SkillRepository';
import UrlService from '../services/UrlService';
import ReactTable from 'react-table';
import BrowserHistory from '../services/BrowserHistory';
import AsyncSelect from 'react-select/lib/Async';
import makeAnimated from 'react-select/lib/animated';
import EmployeeSkillsCell from './EmployeeTable/EmployeeSkillsCell';
import ButtonEdit from './EmployeeTable/ButtonEdit';
import ButtonCreateNewEmployee from './EmployeeTable/ButtonCreateNewEmployee';
import ExpandedRowContent from './EmployeeTable/ExpandedRowContent';
import { formatOptionLabel, noOptionsMessage } from './EmployeeTable/ReactSelectCustomizations';
import { produce } from 'immer';
import { debounce } from 'debounce';
import equal from 'fast-deep-equal';
import qs from 'query-string';
import 'react-table/react-table.css';

/**
 * @typedef {Object} EmployeeTableProps
 * @property {string} endpoints.employee.search    Employee endpoint for searching via API
 * @property {string} endpoints.employee.edit      Employee endpoint for editing existing employee via form
 * @property {string} endpoints.employee.new       Employee endpoint for creating new employee via form
 * @property {string} endpoints.skill.autocomplete Skill endpoint for select autocomplete
 */

/**
 * @property {EmployeeTableProps} this.props
 */
export default class EmployeeTable extends Component {
	state = {
		loading: true,
		employees: [],
		pages: null,
		page: 0,
		pageSize: 10,
		...this.getDefaultFilterState()
	};

	employeeRepository = null;
	skillRepository = null;

	/**
	 * Updates the url with 500ms debounce
	 */
	debouncedReplaceHistory = debounce((searchFromState) => {
		BrowserHistory.replace({ search: searchFromState }, {});
	}, 500);

	constructor(props) {
		super(props);

		this.employeeRepository = new EmployeeRepository(props.endpoints.employee);
		this.skillRepository = new SkillRepository(props.endpoints.skill);
	}

	getDefaultFilterState() {
		return {
			filters: {
				// Array of skill ids used for filtering
				skills: [],
				// Array of skill options, used to render the skill multiselect
				selectedSkillOptions: [],
				// Searched string for fulltext search
				search: ''
			},
			sort: 'lastName-asc'
		};
	}

	getQueryPropertiesFromState(state) {
		return produce({}, queryProperties => {
			queryProperties.skills = state.filters.skills;
			queryProperties.search = state.filters.search;
			queryProperties.sort = state.sort;
		});
	}

	/**
	 * @return {Promise}
	 */
	buildFetchPromise() {
		return this.employeeRepository.fetchSlice({
			page: this.state.page + 1,
			pageSize: this.state.pageSize,
			...this.getQueryPropertiesFromState(this.state)
		});
	}

	/**
	 * @param {string} query
	 */
	updateFiltersFromQuery(query) {
		const parsedQuery = qs.parse(query, { arrayFormat: 'bracket' });
		const updatedState = produce(this.getDefaultFilterState(), updatedState => {
			// Handle filters
			if (parsedQuery.search) {
				updatedState.filters.search = parsedQuery.search;
			}

			updatedState.filters.skills = parsedQuery.skills;

			// Handle sort
			if (parsedQuery.sort) {
				updatedState.sort = parsedQuery.sort;
			}
		});

		// This will load the selected skill options (from server, if required) and update the state
		const skillIds = parsedQuery.skills ? parsedQuery.skills : [];
		this.skillRepository.findByIdsWithCache(skillIds).then(this.updateSelectedSkillOptions);

		if (!equal(updatedState, this.getDefaultFilterState())) {
			this.setState({ ...updatedState, loading: true, page: 0 }, this.fetchDataAndUpdateState);
		}
	}

	/**
	 * @param {int[]} skills
	 */
	updateSelectedSkillOptions = (skills) => {
		this.setState(produce(this.state, draftState => {
			draftState.filters.selectedSkillOptions = skills;
		}));
	};

	fetchDataAndUpdateState = () => {
		this.buildFetchPromise().then((data) => {
			this.setState(this.buildPostFetchState(data));
		});
	};

	/**
	 * @param {Object} data
	 * @return {Object}
	 */
	buildPostFetchState(data) {
		return {
			loading: false,
			employees: data.documents,
			pages: Math.ceil(data.total / this.state.pageSize)
		};
	}

	autocompleteSkill = (search) => this.skillRepository.autocomplete(search);

	/**
	 * @typedef {Object} SkillOption
	 * @property {int} value
	 * @property {string} label
	 */

	/**
	 * @param {SkillOption[]} options
	 */
	handleSkillFilterChange = (options) => {
		this.skillRepository.addOptionsToCache(options);

		this.setState(produce(this.state, draftState => {
			draftState.filters.skills = options.map(option => option.value);
			draftState.filters.selectedSkillOptions = options;
			draftState.loading = true;
			draftState.page = 0;
		}));
	};

	/**
	 * @param {Event} event
	 */
	handleFulltextSearch = (event) => {
		const search = event.target.value;

		this.setState(produce(this.state, draftState => {
			draftState.filters.search = search;
		}));
	};

	/**
	 * @param {Event} event
	 */
	handleSort = event => {
		this.setState({ sort: event.target.value, loading: true, page: 0 });
	};

	/**
	 * @param {number} page
	 */
	handlePageChange = page => this.setState({ page });

	/**
	 * @param {number} pageSize
	 * @param {number} page
	 */
	handlePageSizeChange = (pageSize, page) => this.setState({ page, pageSize });

	resetFilters = () => this.setState({ ...this.getDefaultFilterState(), page: 0 })

	componentWillMount() {
		// We should setup the state of filters from the query (if one is present)
		const currentUrl = this.props.location.search;
		const query = UrlService.getQueryString(currentUrl);
		this.updateFiltersFromQuery(query);
	}

	componentDidUpdate(prevProps, prevState) {
		const currentSearch = this.props.location.search;
		const currentFilterState = this.getQueryPropertiesFromState(this.state);
		const defaultFilterState = this.getQueryPropertiesFromState(this.getDefaultFilterState());

		// If the URL has changed, we should update the state of filters from query
		if (prevProps.location.search !== currentSearch) {
			this.updateFiltersFromQuery(currentSearch);
			return;
		}

		if (prevState.page !== this.state.page || prevState.pageSize !== this.state.pageSize) {
			this.fetchDataAndUpdateState();
			return;
		}

		if (equal(currentFilterState, defaultFilterState) && currentSearch === '') {
			return;
		}

		// If the full url computed from state has changed, we should change the URL
		let searchFromState = qs.stringify(currentFilterState, { arrayFormat: 'bracket' });

		if (searchFromState !== '') {
			searchFromState = '?' + searchFromState;
		}

		if (currentSearch !== searchFromState) {
			// If search filter has changed, add debounce to wait until user is done typing
			if (prevState.filters.search !== this.state.filters.search) {
				this.debouncedReplaceHistory(searchFromState);
			}
			else {
				BrowserHistory.replace({ search: searchFromState }, {});
			}
		}
	}

	render() {
		const columns = [{
			Header: 'Name',
			accessor: 'fullName'
		},
		{
			Header: 'Email',
			accessor: 'email'
		},
		{
			Header: 'Phone',
			accessor: 'phone'
		},
		{
			Header: 'Skills',
			id: 'skills',
			minWidth: 200,
			style: { whiteSpace: 'normal' },
			accessor: row => <EmployeeSkillsCell row={row} />
		},
		{
			Header: 'Actions',
			maxWidth: 120,
			className: 'text-center',
			Cell: row => <ButtonEdit href={this.props.endpoints.employee.edit.replace('{id}', row.original.id)} />
		}];

		return (
			<div className="container mb-5">
				<div>
					<div className="form-inline">
						<div className="p-2">
							<select value={this.state.sort} onChange={this.handleSort} className="form-control input-sm">
								<option value="lastName-asc">By last name, asc</option>
								<option value="lastName-desc">By last name, desc</option>
							</select>
						</div>
						<div className="p-2" style={{ minWidth: '300px' }}>
							<AsyncSelect
								value={this.state.filters.selectedSkillOptions}
								closeMenuOnSelect={false}
								components={makeAnimated()}
								isClearable
								loadOptions={this.autocompleteSkill}
								onChange={this.handleSkillFilterChange}
								cacheOptions
								defaultOptions={false}
								autoLoad={false}
								placeholder={'Filter employees by skills'}
								formatOptionLabel={formatOptionLabel}
								noOptionsMessage={noOptionsMessage}
								isMulti
							/>
						</div>
						<div className="p-2">
							<input type="text" className="form-control" placeholder="Type here to search.." value={this.state.filters.search} onChange={this.handleFulltextSearch} />
						</div>
						<ButtonCreateNewEmployee href={this.props.endpoints.employee.new} className="p-2" />
					</div>
					<div className="box-body table-responsive no-padding">
						<ReactTable
							minRows={0}
							columns={columns}
							data={this.state.employees}
							pages={this.state.pages}
							loading={this.state.loading}
							manual
							sortable={false}
							className="-striped"
							page={this.state.page}
							pageSize={this.state.pageSize}
							onPageChange={this.handlePageChange}
							onPageSizeChange={this.handlePageSizeChange}
							SubComponent={ExpandedRowContent}
						/>
					</div>
					<div className="form-inline">
						<ButtonCreateNewEmployee href={this.props.endpoints.employee.new} className="ml-auto p-2" />
					</div>
				</div>
			</div>
		);
	}
}
