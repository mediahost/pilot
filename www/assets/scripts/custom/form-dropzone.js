var FormDropzone = function() {


    return {
        //main function to initiate the module
        init: function() {

            Dropzone.options.docsDropzone = {
                init: function() {
//                    this.on("addedfile", function(file) {
//                        // Create the remove button
//                        var removeButton = Dropzone.createElement("<a href='#'>Remove file</a>");
//
//                        // Capture the Dropzone instance as closure.
//                        var _this = this;
//
//                        // Listen to the click event
//                        removeButton.addEventListener("click", function(e) {
//                            // Make sure the button click doesn't submit the form:
//                            e.preventDefault();
//                            e.stopPropagation();
//
//                            // Remove the file preview.
//                            _this.removeFile(file);
//                            // If you want to the delete the file on the server as well,
//                            // you can do the AJAX request here.
//                        });
//
//                        // Add the button to the file preview element.
//                        file.previewElement.appendChild(removeButton);
//                    });
                },
                maxFilesize: 5,
                acceptedFiles: 'image/png, image/x-png, image/jpeg, image/jpg, application/pdf',
                success: function(file, response) {jQuery.nette.success(response); return Dropzone.prototype.defaultOptions.success(file);}
            };

            Dropzone.options.picsDropzone = {
                init: function() {
                },
                maxFilesize: 5,
                acceptedFiles: 'image/gif, image/png, image/x-png, image/jpeg, image/jpg',
                success: function(file, response) {jQuery.nette.success(response); return Dropzone.prototype.defaultOptions.success(file);}
            };
        }
    };
}();