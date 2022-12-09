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
            this.showChecks();

            if (this.currentGalleryId) {
                this.getPictures(this.currentGalleryId);
            }
        }

        getPictures(galleryId) {
            let self = this;
            let container = $('div[data-pictures="' + galleryId + '"]')
            let path = $('input[data-path="' + galleryId + '"]').val();

            container.empty();
            container.append('<h3 id="loading" style="text-align: center;">Loading...</h3>');

            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wp-admin/admin-ajax.php",
                data: {
                    action: 'load_gallery_images',
                    blog_id: this.blogId,
                    gallery_id: galleryId,
                    path: path
                },
                success: function (data) {
                    // let self = true;
                    let count = 0;
                    let errorCount = 0;

                    data.forEach(function (picture) {
                        if (count > 0 && count % 12 === 0) {
                            container.append('<hr/>');
                        }
                        container.append('<img data-image="' + galleryId + '" src="' + picture + '" style="height: 50px; padding: 5px;"/>');
                        count++;
                    });
                    $('#loading').remove();

                    if (self.pathsMatch(galleryId)) {
                        return;
                    }

                    self.canSubmit(true, galleryId);

                    let theseImages = $('img[data-image="' + galleryId + '"]');
                    theseImages.on('error', function () {
                        errorCount++;
                        $('#broken_images').remove();
                        if (theseImages.length === errorCount) {
                            container.prepend('<h3 id="broken_images">All images are broken.  Path not valid.</h3>');
                            self.canSubmit(false, galleryId);
                        } else {
                            container.prepend('<h3 id="broken_images">Some images are broken but Suggested Path is valid. You can [ Submit ].</h3>');
                        }
                    });

                },
                error: function (msg) {
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
                    action: 'update_gallery_path',
                    blog_id: this.blogId,
                    gallery_id: galleryId,
                    path: path
                },
                success: function (data) {
                    let location = document.location;
                    if (!this.currentGalleryId) {
                        location += '&gallery_id=' + galleryId;
                    }

                    document.location = location;
                },
                error: function (msg) {
                    console.log(msg);
                }
            });
        }

        canSubmit(canIt, galleryId) {
            $('button[data-submit="' + galleryId + '"]').prop('disabled', ! canIt);
        }

        pathsMatch(galleryId) {
            let currentPath = $('span[data-current="' + galleryId + '"]').html();
            let suggestedPath = $('input[data-path="' + galleryId + '"]').val()

            return currentPath === suggestedPath;
        }

        showChecks() {
            let self = this;

            $('span[data-check]').each(function () {
                let galleryId = $(this).data('check');
                let box = self.pathsMatch(galleryId) ? '✅' : '🟩';
                $('span[data-check="' + galleryId + '"]').html(box);
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