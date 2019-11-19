// Used to add contribution payment button in Contribution dialog.
export const PresetButton = ( props ) => {
	const selectedClass = props.isSelected ? 'lp-amount-preset-button-selected' : '';
	return (
		<div className="lp-amount-presets">
			<div className="lp-amount-preset-wrapper">
				<div className={ [ 'lp-amount-preset-button', selectedClass ].join( ' ' ) }>
					{ props.amount }
				</div>
			</div>
		</div>
	);
};
