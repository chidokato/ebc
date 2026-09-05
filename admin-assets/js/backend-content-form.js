document.addEventListener('DOMContentLoaded', function () {
    var formRoot = document.getElementById('backend-content-form');

    if (!formRoot) {
        return;
    }

    var wardMap = {};

    try {
        wardMap = JSON.parse(formRoot.dataset.wardMap || '{}');
    } catch (error) {
        wardMap = {};
    }

    var urlBase = formRoot.dataset.urlBase || '';
    var slugPrefix = formRoot.dataset.slugPrefix || 'san-pham';
    var fileInput = document.getElementById('image_file');
    var previewBox = document.getElementById('image-preview-box');
    var removeButton = document.getElementById('remove-image-trigger');
    var preview = document.getElementById('image-preview');
    var placeholder = document.getElementById('image-placeholder');
    var removeImageInput = document.getElementById('remove_image');
    var locationFileInput = document.getElementById('location_image_file');
    var locationPreviewBox = document.getElementById('location-image-preview-box');
    var locationRemoveButton = document.getElementById('remove-location-image-trigger');
    var locationPreview = document.getElementById('location-image-preview');
    var locationPlaceholder = document.getElementById('location-image-placeholder');
    var removeLocationImageInput = document.getElementById('remove_location_image');
    var provinceSelect = document.getElementById('province_id');
    var wardSelect = document.getElementById('ward_id');
    var slugInput = document.getElementById('slug');
    var seoLinkPreview = document.getElementById('seo-link-preview');
    var galleryTypeKeys = ['gallery'];
    var galleryInputs = {};
    var galleryGrids = {};
    var galleryFiles = {};
    var gallerySelectedFiles = {};

    if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
        window.jQuery('.js-admin-category-select').select2({
            width: '100%',
            placeholder: window.jQuery('.js-admin-category-select').data('placeholder') || 'Chon danh muc',
            allowClear: true
        });
    }

    galleryTypeKeys.forEach(function (typeKey) {
        galleryInputs[typeKey] = document.getElementById(typeKey === 'gallery' ? 'gallery_files' : 'gallery_files_' + typeKey);
        galleryGrids[typeKey] = document.getElementById('gallery-preview-grid-' + typeKey);
        galleryFiles[typeKey] = galleryInputs[typeKey] ? new DataTransfer() : null;
        gallerySelectedFiles[typeKey] = [];
    });

    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            var image = new Image();
            var objectUrl = URL.createObjectURL(file);

            image.onload = function () {
                URL.revokeObjectURL(objectUrl);
                resolve(image);
            };

            image.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Khong doc duoc anh.'));
            };

            image.src = objectUrl;
        });
    }

    function canvasToBlob(canvas, type, quality) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (blob) {
                if (!blob) {
                    reject(new Error('Khong the nen anh.'));
                    return;
                }

                resolve(blob);
            }, type, quality);
        });
    }

    function renderWardOptions(selectedWardId) {
        if (!wardSelect) {
            return;
        }

        var provinceId = provinceSelect ? provinceSelect.value : '';
        var wards = provinceId && wardMap[provinceId] ? wardMap[provinceId] : [];
        var options = ['<option value="">Chon phuong xa</option>'];

        wards.forEach(function (ward) {
            var selected = String(selectedWardId || '') === String(ward.id) ? ' selected' : '';
            options.push('<option value="' + ward.id + '"' + selected + '>' + ward.name + '</option>');
        });

        wardSelect.innerHTML = options.join('');
        wardSelect.disabled = !provinceId;
    }

    async function compressImage(file, options) {
        var settings = Object.assign({
            maxWidth: 1600,
            maxHeight: 1600,
            quality: 0.82,
            outputType: 'image/webp'
        }, options || {});

        if (!file || !file.type || file.type.indexOf('image/') !== 0 || file.type === 'image/gif') {
            return file;
        }

        var image = await loadImage(file);
        var ratio = Math.min(settings.maxWidth / image.width, settings.maxHeight / image.height, 1);
        var targetWidth = Math.max(1, Math.round(image.width * ratio));
        var targetHeight = Math.max(1, Math.round(image.height * ratio));

        if (ratio === 1 && file.size <= 1024 * 1024) {
            return file;
        }

        var canvas = document.createElement('canvas');
        canvas.width = targetWidth;
        canvas.height = targetHeight;

        var context = canvas.getContext('2d', { alpha: true });
        context.drawImage(image, 0, 0, targetWidth, targetHeight);

        var blob = await canvasToBlob(canvas, settings.outputType, settings.quality);
        var extension = settings.outputType === 'image/png' ? 'png' : 'webp';
        var fileName = (file.name || 'image').replace(/\.[^.]+$/, '') + '.' + extension;

        if (blob.size >= file.size && ratio === 1) {
            return file;
        }

        return new File([blob], fileName, {
            type: blob.type,
            lastModified: Date.now()
        });
    }

    function createDataTransfer(files) {
        var transfer = new DataTransfer();

        (files || []).forEach(function (file) {
            transfer.items.add(file);
        });

        return transfer;
    }

    function bindDropZone(target, handler) {
        if (!target) {
            return;
        }

        ['dragenter', 'dragover'].forEach(function (eventName) {
            target.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                target.classList.add('border-primary');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            target.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                target.classList.remove('border-primary');
            });
        });

        target.addEventListener('drop', function (event) {
            handler(Array.from(event.dataTransfer.files || []));
        });
    }

    function showImagePlaceholder(targetPreview, targetPlaceholder, targetRemoveButton) {
        targetPreview.src = '';
        targetPreview.classList.add('d-none');
        targetPlaceholder.classList.remove('d-none');
        targetRemoveButton.classList.add('d-none');
    }

    function showImagePreview(targetPreview, targetPlaceholder, targetRemoveButton, src) {
        targetPreview.src = src;
        targetPreview.classList.remove('d-none');
        targetPlaceholder.classList.add('d-none');
        targetRemoveButton.classList.remove('d-none');
    }

    async function applySingleImage(file, targetFileInput, targetRemoveInput, onPreview) {
        if (!file) {
            return;
        }

        var compressedFile = await compressImage(file, {
            maxWidth: 1800,
            maxHeight: 1800,
            quality: 0.82
        });
        var transfer = createDataTransfer([compressedFile]);

        targetRemoveInput.value = '0';
        targetFileInput.files = transfer.files;

        var reader = new FileReader();
        reader.onload = function (event) {
            onPreview(event.target.result);
        };
        reader.readAsDataURL(compressedFile);
    }

    if (fileInput && previewBox && preview && placeholder && removeButton && removeImageInput) {
        previewBox.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function (event) {
            var file = event.target.files[0];

            if (file) {
                applySingleImage(file, fileInput, removeImageInput, function (src) {
                    showImagePreview(preview, placeholder, removeButton, src);
                });
            }
        });

        removeButton.addEventListener('click', function () {
            fileInput.value = '';
            removeImageInput.value = '1';
            showImagePlaceholder(preview, placeholder, removeButton);
        });

        bindDropZone(previewBox, function (files) {
            applySingleImage(files[0], fileInput, removeImageInput, function (src) {
                showImagePreview(preview, placeholder, removeButton, src);
            });
        });
    }

    if (locationFileInput && locationPreviewBox && locationPreview && locationPlaceholder && locationRemoveButton && removeLocationImageInput) {
        locationPreviewBox.addEventListener('click', function () {
            locationFileInput.click();
        });

        locationFileInput.addEventListener('change', function (event) {
            var file = event.target.files[0];

            if (file) {
                applySingleImage(file, locationFileInput, removeLocationImageInput, function (src) {
                    showImagePreview(locationPreview, locationPlaceholder, locationRemoveButton, src);
                });
            }
        });

        locationRemoveButton.addEventListener('click', function () {
            locationFileInput.value = '';
            removeLocationImageInput.value = '1';
            showImagePlaceholder(locationPreview, locationPlaceholder, locationRemoveButton);
        });

        bindDropZone(locationPreviewBox, function (files) {
            applySingleImage(files[0], locationFileInput, removeLocationImageInput, function (src) {
                showImagePreview(locationPreview, locationPlaceholder, locationRemoveButton, src);
            });
        });
    }

    if (provinceSelect && wardSelect) {
        renderWardOptions(wardSelect.value);

        provinceSelect.addEventListener('change', function () {
            renderWardOptions('');
        });
    }

    function updateSeoPreview() {
        if (!slugInput || !seoLinkPreview) {
            return;
        }

        var slug = slugInput.value || 'slug';
        seoLinkPreview.textContent = urlBase + '/' + slugPrefix + '/' + slug;
    }

    if (slugInput) {
        slugInput.addEventListener('input', updateSeoPreview);
    }
    updateSeoPreview();

    function appendGalleryItem(typeKey, src, existingId, fileToken) {
        var galleryGrid = galleryGrids[typeKey];

        if (!galleryGrid) {
            return;
        }

        var wrapper = document.createElement('div');
        wrapper.className = 'position-relative border rounded overflow-hidden bg-light gallery-item gallery-item-thumb';
        wrapper.setAttribute('data-gallery-item', '');

        var image = document.createElement('img');
        image.src = src;
        image.className = 'w-100 h-100 object-fit-cover';
        image.alt = 'Gallery';

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle d-flex align-items-center justify-content-center remove-gallery-item gallery-remove-button';
        button.textContent = 'x';

        if (existingId) {
            button.dataset.existingId = existingId;
        }

        if (fileToken) {
            button.dataset.fileToken = fileToken;
        }

        button.dataset.galleryType = typeKey;

        wrapper.appendChild(image);
        wrapper.appendChild(button);
        galleryGrid.appendChild(wrapper);
    }

    function syncGalleryInput(typeKey) {
        var galleryInput = galleryInputs[typeKey];

        if (!galleryInput) {
            return;
        }

        var transfer = new DataTransfer();

        gallerySelectedFiles[typeKey].forEach(function (file) {
            transfer.items.add(file);
        });

        galleryFiles[typeKey] = transfer;
        galleryInput.files = transfer.files;
    }

    async function addGalleryFiles(typeKey, files) {
        var galleryInput = galleryInputs[typeKey];

        if (!galleryInput || !files.length) {
            return;
        }

        for (let index = 0; index < files.length; index += 1) {
            let compressedFile = await compressImage(files[index], {
                maxWidth: 1800,
                maxHeight: 1800,
                quality: 0.8
            });
            let currentToken = [compressedFile.name, compressedFile.size, compressedFile.lastModified].join('__');
            gallerySelectedFiles[typeKey].push(compressedFile);

            var reader = new FileReader();
            reader.onload = function (event) {
                appendGalleryItem(typeKey, event.target.result, null, currentToken);
            };
            reader.readAsDataURL(compressedFile);
        }

        syncGalleryInput(typeKey);
    }

    document.querySelectorAll('.gallery-picker-trigger').forEach(function (button) {
        var typeKey = button.getAttribute('data-gallery-type');
        var galleryInput = galleryInputs[typeKey];
        var galleryGrid = galleryGrids[typeKey];

        if (!galleryInput || !galleryGrid) {
            return;
        }

        button.addEventListener('click', function () {
            galleryInput.value = '';
            galleryInput.click();
        });

        galleryInput.addEventListener('change', function (event) {
            addGalleryFiles(typeKey, Array.from(event.target.files || []));
        });

        bindDropZone(galleryGrid, function (files) {
            addGalleryFiles(typeKey, files);
        });
    });

    document.addEventListener('click', function (event) {
        var removeGalleryButton = event.target.closest('.remove-gallery-item');

        if (!removeGalleryButton) {
            return;
        }

        var existingId = removeGalleryButton.getAttribute('data-existing-id');
        var fileToken = removeGalleryButton.getAttribute('data-file-token');
        var galleryType = removeGalleryButton.getAttribute('data-gallery-type');
        var galleryGrid = galleryGrids[galleryType];
        var galleryInput = galleryInputs[galleryType];

        if (existingId && galleryGrid) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_gallery_images[]';
            input.value = existingId;
            galleryGrid.appendChild(input);
        }

        if (fileToken && galleryInput) {
            gallerySelectedFiles[galleryType] = gallerySelectedFiles[galleryType].filter(function (file) {
                var currentToken = [file.name, file.size, file.lastModified].join('__');
                return currentToken !== fileToken;
            });

            syncGalleryInput(galleryType);
        }

        var galleryItem = removeGalleryButton.closest('[data-gallery-item]');
        if (galleryItem) {
            galleryItem.remove();
        }
    });

});
