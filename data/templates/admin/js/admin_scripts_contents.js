$(function() {
		tinymce.init({
				selector: 'textarea',
				plugins: "code textcolor emoticons",
			  toolbar: "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | fontsizeselect | forecolor backcolor | emoticons | code",
			  menubar: "tools table format view insert edit",
				height: 500
		}) ;
});