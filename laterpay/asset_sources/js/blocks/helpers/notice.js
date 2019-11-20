const { Notice } = wp.components;

const showNotice = ( type, message ) => {
	return <Notice status={ type } isDismissible={ false }>
		{ message }
	</Notice>;
};

export default showNotice;
