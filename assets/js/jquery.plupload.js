/**
 * +------------------------------------------------------------------------
 * | Copyright (c) 2016, 梦落芳华
 * +------------------------------------------------------------------------
 * | Author : Ferguson <Ferguson.Mr.F@gmail.com>
 * +------------------------------------------------------------------------
 * | Time   : 2017-03-24 13:24
 * +------------------------------------------------------------------------
 */
/**
 * options object|array
 * [
 *      url: string         file submit url
 *      limit: int          allowed upload count
 *      max_size: string    allowed upload file size
 *      file_name: string   uploaded file input name
 *      type: string        uploaded file type or customer
 *      filter: array|object allowed upload file extension
 *                           [
 *                              {title: 'Image Files', extension: 'jpg,jpeg,gif,png'}
 *                           ]
 * ]
 *
 *
 */
(function (window, document, plupload, o, $, undefined) {
    $.fn.uploaded = function (options, form) {
        var opts = $.extend({
            url: null,
            limit: 1,
            max_size: null,
            file_name: 'file',
            type: 'image',
            filter: null
        }, options);

        var uploader, uploaders = {};

        var ele = $(this), message, preview, preview_frame;
        var id = ele.attr('id'), counter = 0;
        if (!id) {
            id = plupload.guid();
            ele.attr('id', id);
        }

        function _(str) {
            return plupload.translate(str) || str;
        }
        
        function preview_image(id, json) {
            ele.find('.upload-text-notice').hide();
            var img = opts.type === 'image' ? '<img src="' + json.url + '" />' : '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-' + json.ext + '"></use></svg>';

            preview.append('<div class="upload-preview-frame" id="' + id + '">' +
                '<input type="hidden" name="' + (opts.limit > 1 ? form.name + '[]' : form.name) + '" value="' + json.url + '" />' +
                '   <div class="upload-preview-img">' + img + '</div>' +
                '   <div class="upload-preview-text">' +
                '       <a href="javascript:;" data-action="view"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-dist"></use></svg></a><a href="javascript:;" data-action="delete"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg></a>' +
                '</div>' +
                '</div>');

            $('a', preview.find('#' + id)).on('click', function () {
                var action = $(this).data('action');
                if (action === 'delete') {
                    $.post(opts.delete, {'file': json.url}, function (res) {
                        if (res.status === true) {
                            $('#' + id).remove();
                            counter >= 1 && counter--;
                            counter === 0 && ele.find('.upload-text-notice').show();
                        }
                    });
                } else if (action === 'view') {
                    window.open(json.url);
                }
                return false;
            })
        }

        ele.append('<div class="upload-text-notice">' + _('Drag files here.') + '</div>' +
            '<div class="upload-message" id="' + id + '-message"></div>' +
            '<div class="upload-preview" id="' + id + '-browser" title="' +
            plupload.sprintf(_('File type allowed: %s, max file size: %s'), opts.filter[0].extensions, opts.max_size) +
            '"></div>');
        preview = ele.find('.upload-preview');
        message = ele.find('.upload-message');

        var value = {};

        uploader = uploaders[id] = new plupload.Uploader({
            runtimes: 'html5, html4',
            browse_button: id + '-browser',
            container: id,
            drop_element: id,
            url: opts.url,

            multi_selection: false,
            unique_names: true,
            chunk_size: '1mb',
            flash_swf_url: opts.path + '/Moxie.swf',
            filters: {
                max_file_size: opts.max_size,
                mime_types: opts.filter
            },
            file_data_name: opts.file_name,
            multipart: true,
            multipart_params: {
                type: opts.type
            },

            init: {
                PostInit: function () {
                    console.log('init: ', counter);
                    if (form.value) {
                        console.log(form.value);
                        //
                        if (form.value instanceof Array) {
                            value = JSON.parse(form.value);
                        } else {
                            counter++;
                            var json = {
                                url: form.value,
                                ext: 'unknow'
                            };
                            // preview image
                            preview_image(plupload.guid(), json);
                        }
                    }
                },
                FilesAdded: function (up, files) {
                    if (counter >= opts.limit) {
                        uploader.removeFile(files[0]);
                        uploader.trigger('Error', {code: 109});
                    } else {
                        uploader.start();
                    }
                },
                FilesRemoved: function (up, files) {

                },
                BeforeUpload: function (up, file) {
                    uploader.disableBrowse(true);
                },
                UploadProgress: function (up, file) {
                    message.html('<div class="upload-progress-bar"></div>').removeAttr('class').addClass('upload-message upload-message-progress');
                    message.find('.upload-progress-bar').css({'width': file.percent + '%'});
                },
                FileUploaded: function (up, file, res) {
                    var json = eval("(" + res.response + ")");
                    if (json.status === false) {
                        json['file'] = file;
                        uploader.trigger('Error', json);
                        uploader.stop();
                    } else if (json.status === true) {
                        counter++;
                        // preview uploaded file.
                        preview_image(file.id, json);
                    }
                },
                UploadComplete: function (up, files) {
                    message.html('').removeAttr('class').addClass('upload-message');
                    uploader.disableBrowse(false);
                },
                Error: function (up, err) {
                    console.log(err);
                    var msg = err.message;
                    switch (err.code) {
                        case plupload.FILE_EXTENSION_ERROR:
                        case 106:
                            msg = _('File extension error.');
                            break;
                        case plupload.FILE_SIZE_ERROR:
                        case 103:
                            msg = _('File size error.');
                            break;
                        case plupload.FILE_DUPLICATE_ERROR:
                            msg = plupload.sprintf(_("%s already present in the queue."), err.file.name);
                            break;
                        case plupload.IMAGE_FORMAT_ERROR :
                            msg = _("Image format either wrong or not supported.");
                            break;
                        case 109:
                            msg = plupload.sprintf(_("Overrides, only %s files allowed to be uploaded"), opts.limit);
                            break;
                        default:
                            msg = _("Upload URL might be wrong or doesn't exist.");
                            break;
                    }
                    message.html(msg).removeAttr('class').addClass('upload-message upload-message-error');
                    uploader.disableBrowse(false);
                }


            }
        });
        uploader.init();
    }
})(window, document, plupload, moxie, jQuery);