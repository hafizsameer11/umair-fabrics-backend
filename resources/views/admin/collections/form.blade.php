@extends('layouts.admin')
@section('title', $collection->exists ? 'Edit Collection' : 'Add Collection')
@section('header', $collection->exists ? 'Edit Collection' : 'Add Collection')
@section('content')
<form method="POST" action="{{ $collection->exists ? route('admin.collections.update', $collection) : route('admin.collections.store') }}" enctype="multipart/form-data" id="collection-form">
    @csrf
    @if($collection->exists) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Collection details</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label fw-medium">Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $collection->name) }}" required></div>
                    <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $collection->slug) }}"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $collection->description) }}</textarea></div>
                    <div class="mb-3"><label class="form-label">Image</label>
                        @if($collection->image)<img src="{{ asset('storage/'.$collection->image) }}" class="d-block mb-2 rounded" style="max-height:120px">@endif
                        <input type="file" name="image" class="form-control" accept="image/*"></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span>Products in this collection</span>
                    <small class="text-muted fw-normal">Drag to sort — first product shows first on site</small>
                </div>
                <div class="card-body">
                    <ul id="sorted-products" class="list-group mb-4">
                        @foreach($collection->products as $product)
                            <li class="list-group-item d-flex align-items-center gap-2 sortable-item" draggable="true" data-id="{{ $product->id }}">
                                <span class="text-muted cursor-grab" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                                <span class="flex-grow-1">{{ $product->title }}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-product" aria-label="Remove">&times;</button>
                                <input type="hidden" name="product_ids[]" value="{{ $product->id }}">
                            </li>
                        @endforeach
                    </ul>
                    @if($sortedProducts->isEmpty() && $collection->products->isEmpty())
                        <p class="text-muted small mb-3" id="empty-products-msg">No products added yet. Select products below.</p>
                    @endif

                    <label class="form-label fw-medium">Add products</label>
                    <input type="search" id="product-search" class="form-control mb-2" placeholder="Search products...">
                    <div class="border rounded p-3" style="max-height:260px;overflow-y:auto;" id="available-products">
                        @foreach($sortedProducts as $product)
                            <div class="form-check product-option" data-title="{{ strtolower($product->title) }}">
                                <input class="form-check-input add-product-check" type="checkbox" value="{{ $product->id }}" id="add{{ $product->id }}" data-title="{{ $product->title }}" @checked($collection->products->contains($product->id))>
                                <label class="form-check-label" for="add{{ $product->id }}">{{ $product->title }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:1rem;">
                <div class="card-header bg-white fw-semibold">Homepage settings</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="show_on_homepage" value="0">
                        <input class="form-check-input" type="checkbox" name="show_on_homepage" value="1" id="show_hp" @checked(old('show_on_homepage', $collection->show_on_homepage))>
                        <label class="form-check-label" for="show_hp">Show on homepage</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Homepage title</label>
                        <input type="text" name="homepage_title" class="form-control" value="{{ old('homepage_title', $collection->homepage_title) }}" placeholder="{{ $collection->name ?: 'Same as collection name' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max products on homepage</label>
                        <input type="number" name="homepage_product_limit" class="form-control" value="{{ old('homepage_product_limit', $collection->homepage_product_limit ?? 8) }}" min="1" max="48">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Homepage display order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $collection->sort_order ?? 0) }}" min="0">
                        <div class="form-text">Lower numbers appear first on the homepage.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i> Save Collection</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const list = document.getElementById('sorted-products');
    const emptyMsg = document.getElementById('empty-products-msg');
    let dragEl = null;

    function updateEmptyState() {
        if (!emptyMsg) return;
        emptyMsg.style.display = list.children.length ? 'none' : 'block';
    }

    function syncCheckboxes() {
        const ids = new Set([...list.querySelectorAll('input[name="product_ids[]"]')].map(i => i.value));
        document.querySelectorAll('.add-product-check').forEach(cb => {
            cb.checked = ids.has(cb.value);
        });
    }

    function addProduct(id, title) {
        if (list.querySelector(`input[value="${id}"]`)) return;
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex align-items-center gap-2 sortable-item';
        li.draggable = true;
        li.dataset.id = id;
        li.innerHTML = `
            <span class="text-muted cursor-grab" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
            <span class="flex-grow-1"></span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-product" aria-label="Remove">&times;</button>
            <input type="hidden" name="product_ids[]" value="${id}">
        `;
        li.querySelector('.flex-grow-1').textContent = title;
        list.appendChild(li);
        bindItem(li);
        updateEmptyState();
    }

    function bindItem(item) {
        item.addEventListener('dragstart', () => { dragEl = item; item.classList.add('opacity-50'); });
        item.addEventListener('dragend', () => { item.classList.remove('opacity-50'); dragEl = null; });
        item.addEventListener('dragover', e => { e.preventDefault(); });
        item.addEventListener('drop', e => {
            e.preventDefault();
            if (!dragEl || dragEl === item) return;
            const rect = item.getBoundingClientRect();
            const after = (e.clientY - rect.top) > rect.height / 2;
            if (after) item.after(dragEl); else item.before(dragEl);
        });
        item.querySelector('.remove-product').addEventListener('click', () => {
            item.remove();
            syncCheckboxes();
            updateEmptyState();
        });
    }

    list.querySelectorAll('.sortable-item').forEach(bindItem);

    document.querySelectorAll('.add-product-check').forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked) addProduct(cb.value, cb.dataset.title);
            else {
                const hidden = list.querySelector(`input[value="${cb.value}"]`);
                hidden?.closest('.sortable-item')?.remove();
                updateEmptyState();
            }
        });
    });

    document.getElementById('product-search')?.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.product-option').forEach(row => {
            row.style.display = !q || row.dataset.title.includes(q) ? '' : 'none';
        });
    });
})();
</script>
@endpush
@endsection
