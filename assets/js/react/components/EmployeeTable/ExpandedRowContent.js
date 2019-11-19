import React from 'react';
import ReactTable from 'react-table';
import skillLevelLabel from '../../helper/SkillLevelHelper';

const tableSubRowAddressColumns = [
	{
		Header: 'City',
		accessor: 'city'
	},
	{
		Header: 'Street',
		accessor: 'street'
	},
	{
		Header: 'Postal Code',
		accessor: 'postalCode'
	},
	{
		Header: 'Country',
		accessor: 'country'
	}
];

const SkillLevelCell = row => <span>{skillLevelLabel(row.original.level)}</span>;

const tableSubRowSkillColumns = [
	{
		Header: 'Skill',
		accessor: 'name'
	},
	{
		Header: 'Level',
		accessor: 'level',
		Cell: SkillLevelCell
	}
];

const ExpandedRowContent = (row) => (
	<div>
		{ row.original.skills.length > 0 ? (
			<ReactTable
				data={row.original.skills}
				columns={tableSubRowSkillColumns}
				defaultPageSize={row.original.skills.length}
				showPagination={false}
				defaultSorted={[{
					id: 'level',
					desc: true
				}]}
				className={'m-2'}
			/>
		) : <p className="m-2">Employee has no skills assigned.</p>}
		{ row.original.addresses.length > 0 ? (
			<ReactTable
				data={row.original.addresses}
				columns={tableSubRowAddressColumns}
				defaultPageSize={row.original.addresses.length}
				showPagination={false}
				className={'m-2'}
			/>
		) : <p className="m-2">Employee has no addresses filled in.</p>}
	</div>
);

export default ExpandedRowContent;


