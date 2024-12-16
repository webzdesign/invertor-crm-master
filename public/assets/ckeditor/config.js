/**
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	

	let baseUrl = window.location.origin + '/' + window.location.pathname.split ('/') [1] + '/' + window.location.pathname.split ('/') [2] + '/';
	config.stylesSet = [];
	// config.contentsCss = baseUrl + 'public/assets/vendors/vendors.min.css';
	
	
	config.extraPlugins = 'btgrid,setfield,descriptionlist';
	config.allowedContent = true;
};
