(function () {
    let editorInstance = null;
    const variantsBody = document.getElementById('variantsBody');
    const uploadZone = document.getElementById('uploadZone');
    const imageInput = document.getElementById('imageInput');
    const newImagesPreview = document.getElementById('newImagesPreview');

    // CKEditor
    const descEl = document.querySelector('#description_html');
    if (descEl && typeof ClassicEditor !== 'undefined') {
        ClassicEditor.create(descEl, {
            toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'link', 'blockQuote', '|', 'undo', 'redo']
        }).then(editor => {
            editorInstance = editor;
        }).catch(err => console.error(err));
    }

    // Sync CKEditor before submit
    const form = document.getElementById('productForm');
    const statusActive = document.getElementById('statusActive');
    const statusField = document.getElementById('statusField');
    const statusHint = document.getElementById('statusHint');

    if (statusActive && statusField) {
        statusActive.addEventListener('change', function () {
            statusField.value = this.checked ? 'active' : 'draft';
            if (statusHint) {
                statusHint.textContent = this.checked
                    ? 'Customers can see and buy this product.'
                    : 'Hidden from store — draft mode.';
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function () {
            if (editorInstance) {
                document.getElementById('description_html').value = editorInstance.getData();
            }
            if (statusActive && statusField) {
                statusField.value = statusActive.checked ? 'active' : 'draft';
            }
        });
    }

    // Auto slug from title
    const titleInput = document.querySelector('[name="title"]');
    const slugInput = document.querySelector('[name="slug"]');
    if (titleInput && slugInput && !slugInput.value) {
        titleInput.addEventListener('blur', function () {
            if (!slugInput.value) {
                slugInput.placeholder = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
        });
    }

    // Mark image for removal
    document.querySelectorAll('.btn-remove-image').forEach(btn => {
        btn.addEventListener('click', function () {
            const item = this.closest('.image-gallery-item');
            const checkbox = item.querySelector('.delete-image-cb');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                item.classList.toggle('marked-remove', checkbox.checked);
                this.innerHTML = checkbox.checked
                    ? '<i class="bi bi-arrow-counterclockwise"></i> Undo'
                    : '<i class="bi bi-trash"></i> Remove';
            }
        });
    });

    // Image upload zone
    if (uploadZone && imageInput) {
        uploadZone.addEventListener('click', () => imageInput.click());
        uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
        uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
        uploadZone.addEventListener('drop', e => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            imageInput.files = e.dataTransfer.files;
            previewNewImages();
        });
        imageInput.addEventListener('change', previewNewImages);
    }

    function previewNewImages() {
        if (!newImagesPreview || !imageInput) return;
        newImagesPreview.innerHTML = '';
        Array.from(imageInput.files).forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = e => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4 col-lg-3';
                col.innerHTML = `<div class="image-gallery-item"><img src="${e.target.result}" alt=""><div class="img-actions small text-muted text-center">New #${i + 1}</div></div>`;
                newImagesPreview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    }

    // Variants
    window.addVariantRow = function (data) {
        if (!variantsBody) return;
        const index = variantsBody.querySelectorAll('tr').length;
        const d = data || { id: '', title: '', sku: '', price: '', compare_at_price: '', stock: 10, min_order_qty: '', option1: '' };
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="hidden" name="variants[${index}][id]" value="${d.id || ''}">
                <input type="text" class="form-control form-control-sm" name="variants[${index}][title]" value="${esc(d.title)}" placeholder="e.g. Design 6"></td>
            <td><input type="text" class="form-control form-control-sm" name="variants[${index}][sku]" value="${esc(d.sku)}"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[${index}][price]" value="${d.price}" required min="0"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[${index}][compare_at_price]" value="${d.compare_at_price || ''}" min="0"></td>
            <td><input type="number" class="form-control form-control-sm stock-input" name="variants[${index}][stock]" value="${d.stock}" required min="0"></td>
            <td><input type="number" class="form-control form-control-sm" name="variants[${index}][min_order_qty]" value="${d.min_order_qty || ''}" min="1" placeholder="—"></td>
            <td class="text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-warning btn-out-of-stock" title="Set stock to 0"><i class="bi bi-x-circle"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-variant" title="Remove"><i class="bi bi-trash"></i></button>
            </td>`;
        variantsBody.appendChild(tr);
        bindVariantRow(tr);
        reindexVariants();
    };

    function bindVariantRow(tr) {
        tr.querySelector('.btn-out-of-stock')?.addEventListener('click', function () {
            tr.querySelector('.stock-input').value = 0;
        });
        tr.querySelector('.btn-remove-variant')?.addEventListener('click', function () {
            if (variantsBody.querySelectorAll('tr').length > 1) {
                tr.remove();
                reindexVariants();
            } else {
                alert('At least one variant is required.');
            }
        });
    }

    function reindexVariants() {
        variantsBody.querySelectorAll('tr').forEach((tr, index) => {
            tr.querySelectorAll('[name^="variants"]').forEach(input => {
                input.name = input.name.replace(/variants\[\d+\]/, `variants[${index}]`);
            });
        });
    }

    variantsBody?.querySelectorAll('tr').forEach(bindVariantRow);

    document.getElementById('btnAddVariant')?.addEventListener('click', () => addVariantRow());

    function esc(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
    }
})();
