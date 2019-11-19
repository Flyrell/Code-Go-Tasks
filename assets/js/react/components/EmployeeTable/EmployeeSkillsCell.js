import React from 'react';
import skillLevelLabel from '../../helper/SkillLevelHelper';

const EmployeeSkillsCell  = ({ row }) => (
	<span style={{ fontSize: '0.75em' }}>
		{row.skills.map((skill, i) => <span key={`${skill.name}-${skill.level}`} title={skillLevelLabel(skill.level)}>{i > 0 ? ', ' : ''}{skill.name}</span>)}
	</span>
);

export default EmployeeSkillsCell;
