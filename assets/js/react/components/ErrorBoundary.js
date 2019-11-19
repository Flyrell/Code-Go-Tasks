import React, { Component } from 'react';

export default class ErrorBoundary extends Component {
	constructor(props) {
		super(props);
		this.state = { hasError: false };
	}

	static getDerivedStateFromError(error) {
		// Update state so the next render will show the fallback UI.
		return { hasError: true };
	}

	componentDidCatch(error, info) {
		// Log the error
		console.error(error);
	}

	render() {
		if (this.state.hasError) {
			// You can render any custom fallback UI
			return (
				<div className={'alert alert-danger'}>
					<h3>Oops. That's an error! :(</h3>
					<p>An uncaught javascript error has occurred somewhere within the <b>{this.props.name}</b> component.</p>
				</div>
			);
		}

		return this.props.children;
	}
}