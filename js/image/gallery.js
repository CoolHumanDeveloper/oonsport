/**
 * Created by remi on 18/01/15.
 */
(function () {
    function previewImage(file) {

        var galleryId = "gallery";
        var gallery = document.getElementById(galleryId);
        var imageType = /image.*/;


        var thumb = document.createElement("div");
        thumb.classList.add('thumbnail');

        thumb.innerHTML = '<a class="btn btn-xs btn-danger deleteButton"><i class="fa fa-trash-o"></i></a>';

        if (!file.type.match(imageType)) {
            /*alert("File Type must be an image");*/

            var data = document.createElement("input");
            data.type = "hidden";
            data.name = "upload_file[]";
            data.file = file;
            thumb.appendChild(data);
            gallery.appendChild(thumb);

            var reader = new FileReader();
            reader.onload = (function (aImg) {
                return function (e) {
                    aImg.value = e.target.result;
                };
            })(data);
            reader.readAsDataURL(file);
        } else {
            var img = document.createElement("img");
            img.file = file;

            var data_img = document.createElement("input");
            data_img.type = "hidden";
            data_img.name = "upload_file[]";
            data_img.file = file;

            thumb.appendChild(img);
            thumb.appendChild(data_img);
            gallery.appendChild(thumb);

            // Using FileReader to display the image content
            var reader1 = new FileReader();
            reader1.onload = (function (aImg) {
                return function (e) {
                    aImg.src = e.target.result;
                    aImg.value = e.target.result;
                };
            })(img);
            reader1.readAsDataURL(file);

            var reader2 = new FileReader();
            reader2.onload = (function (aImg2) {
                return function (e) {
                    aImg2.src = e.target.result;
                    aImg2.value = e.target.result;
                };
            })(data_img);
            reader2.readAsDataURL(file);
        }
    }

    var uploadfiles = document.querySelector('#fileinput');
    uploadfiles.addEventListener('change', function () {
        var files = this.files;
        for (var i = 0; i < files.length; i++) {
            previewImage(this.files[i]);
        }

        //$("#fileinput").val("");

    }, false);
})();
