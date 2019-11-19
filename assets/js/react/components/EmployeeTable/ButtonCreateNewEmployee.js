import React from 'react';

const ButtonCreateNewEmployee  = ({ href, ...props }) => (
	<div {...props}>
		<a href={href} className="btn btn-primary float-right" role="button">Create new employee</a>
	</div>
);

export default ButtonCreateNewEmployee;
