import React from 'react';

const ButtonEdit = ({ href, ...props }) => (
	<a href={href ? href : '#'} title="Edit" {...props}><i className="fas fa-edit list-icon" title="Edit" /> <span className="sr-only">Edit</span></a>
);

export default ButtonEdit;