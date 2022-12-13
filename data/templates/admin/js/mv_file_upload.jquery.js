///////////////////////////////////////////////////////////////////////////////////////////////////
// the main function e.g. object
// with a little help of: http://abandon.ie/notebook/simple-file-uploads-using-jquery-ajax
///////////////////////////////////////////////////////////////////////////////////////////////////
(function( $ ) {
		$.fn.mv_file_uploader = function( options ) {
				// This is the easiest way to have default options.
				var settings = $.extend({
						// These are the defaults.
						input_file_selector: "",		//this is the file selector html element
						input_upload_button: "",		//this is the upload button html element
						input_file: "",							//this is the html file input that holds the upload file information
						receiver_url: "",						//this is the url, which receives the file upload
						debug: true,								//set this to false, to disable the console output
						trigger_add_post_vars: '',	//a trigger that is to be called before submit..
						handler_uploaded: '',				//a handler to be called when an image has been uploaded
						trigger_allow_upload_without_file: '',		//a trigger that is called, if no upload file is specified. It decides if the form is submitted anyway. (Return true in the handler function to submit, false to don't submit)
						auto_upload: false
						
				}, options );
				
				var files;
				
				//output debug information
				if(settings['debug']) {
						console.log('started file uploader (debugging is on!)');
						console.log('input_file_selector: ' + settings['input_file_selector']);
						console.log('input_upload_button: ' + settings['input_upload_button']);
						console.log('input_file: ' + settings['input_file']);
						console.log('receiver_url: ' + settings['receiver_url']);
				};
				
				//when the add file event was executed..
				$(settings['input_file']).on('change', function(event) {
						//grab the input file! (we only support one file by now..)
						if(settings['debug']) {
								console.log('adding file ' + event.target.files);
						}
						
						files = event.target.files;
						
						if(settings['debug']) {
								console.log('file added..');
						}
						
						if(settings['auto_upload'] == true) {
								mv_file_uploader_upload();
						}
				});
				
				var mv_file_uploader_upload = function() {
						// Create a formdata object and add the files
						var data = new FormData();
						
						if(settings['debug']) {
								console.log('form data before inserting ' + data);
						}
						
						if(files != undefined) {
								$.each(files, function(key, value) {
										if(settings['debug']) {
												console.log('adding to form data - key: ' + key + ' | value: ' + value);
										}
										
										data.append(key, value);
								});
						} else {
								if(settings['trigger_allow_upload_without_file'] != '') {
										if(settings['trigger_allow_upload_without_file']() == false) {
												if(settings['debug']) {
														console.log('upload with no files is permitted. No Upload is done!');
												}
												return false;
										}
								}
						}
						
						if(settings['debug']) {
								console.log('form data after inserting ' + data);
						}
						
						//append additional data..
						if(settings['trigger_add_post_vars'] != '') {
								$.each(settings['trigger_add_post_vars'](), function(key, value) {
										data.append(key, value);
								});
						}

						$.ajax({
								url: settings['receiver_url'],
								type: 'POST',
								data: data,
								cache: false,
								dataType: 'json',
								processData: false,		//important - will otherwise result in Javasript error:  'append' called on an object that does not implement interface FormData.
								contentType: false,		//important - will otherwise result in Javasript error:  'append' called on an object that does not implement interface FormData.
								success: function(data, textStatus, jqXHR) {
										if(typeof data.error === 'undefined') {
												// Success so call function to process the form
												if(settings['debug']) {
														console.log('SUCCESS: ' + data);
												}
												
												if(settings['handler_uploaded'] != '') {
														settings['handler_uploaded'](data);
												}
										} else {
												// Handle errors here
												alert('An error occured, Please check the browser console if you need more information');
												console.log('ERRORS: ' + data.error);
										}
								},
								error: function(jqXHR, textStatus, errorThrown) {
										// Handle errors here
										alert('An error occured, Please check the browser console if you need more information');
										console.log('ERRORS: ' + textStatus);
								},
								complete: function() {
										// STOP LOADING SPINNER
								}
						});
				};
				
				//when the submit button was hit - begin the upload (if we got a file..)
				$(settings['input_upload_button']).on('click', function() {
						mv_file_uploader_upload();
				});
		}
}( jQuery ));