jQuery(document).ready(function ($) {

    class PathFixer {

        constructor() {
            this.urlParams = new URLSearchParams(window.location.search);
            this.test = $('button[data-test]');
            this.submit = $('button[data-submit]');
            this.rows = $('tr[data-gallery]');
            this.blogId = this.urlParams.has('blog_id') ? this.urlParams.get('blog_id') : null;
            this.currentGalleryId = this.urlParams.has('gallery_id') ? this.urlParams.get('gallery_id') : null;

            this.addListeners();

            if (this.currentGalleryId) {
                this.getPictures(this.currentGalleryId);
            }
        }

        getPictures(galleryId) {
            let container = $('div[data-pictures="' + galleryId + '"]')
            let path = $('input[data-path="' + galleryId + '"]').val();

            container.empty();
            container.append('<h3 id="loading" style="text-align: center;">Loading...</h3>');

            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wp-admin/admin-ajax.php",
                data: {
                    action:'load_gallery_images',
                    blog_id: this.blogId,
                    gallery_id: galleryId,
                    path: path
                },
                success: function(data) {
                    let count = 0;
                    data.forEach(function(picture) {
                        if (count % 12 === 0) {
                            container.append('<hr/>');
                        }
                        container.append('<img src="' + picture +'" style="height: 50px; padding: 5px;"/>');
                        count++;
                    });
                    $('#loading').remove();
                },
                error: function(msg) {
                    console.log(msg);
                }
            });
        }

        updatePath(galleryId) {
            let path = $('input[data-path="' + galleryId + '"]').val();

            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wp-admin/admin-ajax.php",
                data: {
                    action:'update_gallery_path',
                    blog_id: this.blogId,
                    gallery_id: galleryId,
                    path: path
                },
                success: function(data) {
                    let location = document.location;
                    if (! this.currentGalleryId) {
                        location += '&gallery_id=' + galleryId;
                    }

                    document.location = location;
                },
                error: function(msg) {
                    console.log(msg);
                }
            });
        }

        addListeners() {
            let self = this;

            this.test.on('click', function () {
                let galleryId = $(this).data('test');

                self.getPictures(galleryId);
            })

            this.submit.on('click', function () {
                let galleryId = $(this).data('submit');

                self.updatePath(galleryId);
            })
        }
    }

    new PathFixer();
});