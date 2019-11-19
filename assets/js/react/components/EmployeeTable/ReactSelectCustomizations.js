import React from 'react';

// Helper methods used to adjust React-Select behaviour

const noOptionsMessage = ({ inputValue }) => {
	if (inputValue.length > 1) {
		return 'No results match your search';
	}

	return 'Please type in at least 2 characters';
};

const escapeRegExp = (string) => string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

const formatOptionLabel = ({ label }, { context, inputValue }) => {
	if (inputValue.length > 0) {
		const searchRegex = new RegExp(escapeRegExp(inputValue.toLowerCase()), 'i');
		const position = label.search(searchRegex);
		if (position > -1) {
			const start = label.substr(0, position);
			const match = label.substr(position, inputValue.length);
			const end = label.substr(position + inputValue.length, label.length);
			return (
				<span>
					<span>{start}</span>
					<span style={{ textDecoration: 'underline' }}>{match}</span>
					<span>{end}</span>
				</span>
			);
		}
	}

	return <span>{label}</span>;
};

export { noOptionsMessage, formatOptionLabel };
