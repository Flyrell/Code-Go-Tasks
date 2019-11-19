const skillLevelLabel = (level) => {
	switch (level) {
		case 1: return 'Beginner';
		case 2: return 'Intermediate';
		case 3: return 'Advanced';
		case 4: return 'Expert';
		case 5: return 'Master';
		default: return '?';
	}
};

export default skillLevelLabel;