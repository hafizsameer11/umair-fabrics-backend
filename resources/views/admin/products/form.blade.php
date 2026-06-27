@extends('layouts.admin')
@section('title', $product->exists ? 'Edit Product' : 'Add Product')
@section('header', $product->exists ? 'Edit Product' : 'Add Product')

@section('content')
@php
    $variants = old('variants', $product->variants->map(fn($v) => [
        'id' => $v->id, 'title' => $v->title, 'sku' => $v->sku,
        'price' => $v->price, 'compare_at_price' => $v->compare_at_price,
        'stock' => $v->stock, 'min_order_qty' => $v->min_order_qty, 'option1' => $v->option1,
    ])->toArray());
    if (empty($variants)) {
        $variants = [['id'=>'','title'=>'','sku'=>'','price'=>'','compare_at_price'=>'','stock'=>10,'min_order_qty'=>'','option1'=>'']];
    }
    $isActive = old('status', $product->status) === 'active';
@endphp

<form id="productForm" method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Product details</h2></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg" value="{{ old('title', $product->title) }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" placeholder="auto-generated-from-title">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vendor</label>
                            <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $product->vendor) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <input type="text" name="product_type" class="form-control" value="{{ old('product_type', $product->product_type) }}" placeholder="3-PC">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Tags <small class="text-muted">(comma separated)</small></label>
                        <input type="text" name="tags" class="form-control" value="{{ old('tags', $product->tags) }}">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Description</h2></div>
                <div class="card-body">
                    <textarea name="description_html" id="description_html" rows="8" class="form-control">{{ old('description_html', $product->description_html) }}</textarea>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0 fw-semibold">Images</h2>
                    <small class="text-muted">First image = featured</small>
                </div>
                <div class="card-body">
                    @if($product->images->count())
                        <p class="small text-muted mb-2">Existing images — click Remove to delete on save</p>
                        <div class="image-gallery mb-4">
                            @foreach($product->images as $image)
                                <div class="image-gallery-item">
                                    <img src="{{ $image->url() }}" alt="{{ $image->alt }}">
                                    <input type="hidden" name="image_order[]" value="{{ $image->id }}">
                                    <input type="checkbox" class="d-none delete-image-cb" name="delete_images[]" value="{{ $image->id }}" id="del_img_{{ $image->id }}">
                                    <div class="img-actions">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-remove-image">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="upload-zone" id="uploadZone">
                        <i class="bi bi-cloud-upload display-6 text-primary"></i>
                        <p class="mb-1 fw-medium">Drop images here or click to upload</p>
                        <p class="small text-muted mb-0">PNG, JPG, WEBP — max 5MB each — multiple allowed</p>
                    </div>
                    <input type="file" name="images[]" id="imageInput" class="d-none" accept="image/*" multiple>
                    <div class="row g-2 mt-3" id="newImagesPreview"></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0 fw-semibold">Pricing & inventory</h2>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddVariant">
                        <i class="bi bi-plus"></i> Add variant
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered variant-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Variant / Option</th>
                                    <th>SKU</th>
                                    <th>Price (PKR)</th>
                                    <th>Compare at</th>
                                    <th>Stock</th>
                                    <th>Min qty</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="variantsBody">
                                @foreach($variants as $v)
                                <tr>
                                    <td>
                                        <input type="hidden" name="variants[{{ $loop->index }}][id]" value="{{ $v['id'] ?? '' }}">
                                        <input type="text" class="form-control form-control-sm" name="variants[{{ $loop->index }}][title]" value="{{ $v['title'] ?? '' }}" placeholder="e.g. Design 6">
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm" name="variants[{{ $loop->index }}][sku]" value="{{ $v['sku'] ?? '' }}"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[{{ $loop->index }}][price]" value="{{ $v['price'] ?? '' }}" required min="0"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[{{ $loop->index }}][compare_at_price]" value="{{ $v['compare_at_price'] ?? '' }}" min="0"></td>
                                    <td><input type="number" class="form-control form-control-sm stock-input" name="variants[{{ $loop->index }}][stock]" value="{{ $v['stock'] ?? 0 }}" required min="0"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="variants[{{ $loop->index }}][min_order_qty]" value="{{ $v['min_order_qty'] ?? '' }}" min="1" placeholder="—"></td>
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-out-of-stock" title="Out of stock"><i class="bi bi-x-circle"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-variant"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted p-3 mb-0"><i class="bi bi-info-circle me-1"></i>Stock = 0 shows as <strong>Sold out</strong> on the store. Use <i class="bi bi-x-circle"></i> to quickly set out of stock.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Publish</h2></div>
                <div class="card-body">
                    <input type="hidden" name="status" id="statusField" value="{{ old('status', $product->status ?? 'draft') }}">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="statusActive" {{ $isActive ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="statusActive">Product is active (visible on store)</label>
                    </div>
                    <p class="small text-muted mb-0" id="statusHint">{{ $isActive ? 'Customers can see and buy this product.' : 'Hidden from store — draft mode.' }}</p>
                    <div class="form-check form-switch">
                        <input type="hidden" name="featured" value="0">
                        <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="featured">Featured on homepage</label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Shipping weight</h2></div>
                <div class="card-body">
                    <label class="form-label fw-medium">Weight (grams)</label>
                    <input type="number" name="weight_grams" class="form-control" value="{{ old('weight_grams', $product->weight_grams ?? 500) }}" min="1" required>
                    <div class="form-text">Used to calculate shipping at checkout. Example: a 3-piece suit ≈ 800–1200g.</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Purchase rules</h2></div>
                <div class="card-body">
                    <label class="form-label">Minimum order quantity</label>
                    <input type="number" name="min_order_qty" class="form-control mb-3" value="{{ old('min_order_qty', $product->min_order_qty ?? 1) }}" min="1" required>
                    <label class="form-label">Maximum order quantity</label>
                    <input type="number" name="max_order_qty" class="form-control mb-3" value="{{ old('max_order_qty', $product->max_order_qty) }}" min="1" placeholder="No limit">

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="cannot_sell_alone" value="0">
                        <input class="form-check-input" type="checkbox" id="cannotSellAlone" name="cannot_sell_alone" value="1"
                            @checked(old('cannot_sell_alone', !($product->allow_sell_alone ?? true)))>
                        <label class="form-check-label fw-medium" for="cannotSellAlone">Cannot sell alone (single piece)</label>
                    </div>
                    <p class="small text-muted mb-3">When enabled, customer must either buy <strong>minimum quantity (2+)</strong> OR add this product together with one of the linked products below.</p>

                    <label class="form-label fw-medium">Must buy with (optional)</label>
                    <p class="small text-muted">Select products that satisfy the "buy together" rule when customer orders only 1 piece.</p>
                    <div class="border rounded p-2" style="max-height:180px;overflow-y:auto;">
                        @foreach($allProducts as $p)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="companions[]" value="{{ $p->id }}" id="comp_{{ $p->id }}"
                                    @checked(collect(old('companions', $product->companionProducts->pluck('id')->toArray()))->contains($p->id))>
                                <label class="form-check-label small" for="comp_{{ $p->id }}">{{ $p->title }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Collections</h2></div>
                <div class="card-body" style="max-height:200px;overflow-y:auto;">
                    @foreach($collections as $collection)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="collections[]" value="{{ $collection->id }}" id="col_{{ $collection->id }}"
                                @checked(collect(old('collections', $product->collections->pluck('id')->toArray()))->contains($collection->id))>
                            <label class="form-check-label" for="col_{{ $collection->id }}">{{ $collection->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">SEO</h2></div>
                <div class="card-body">
                    <label class="form-label">Meta title</label>
                    <input type="text" name="meta_title" class="form-control mb-3" value="{{ old('meta_title', $product->meta_title) }}">
                    <label class="form-label">Meta description</label>
                    <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description', $product->meta_description) }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-check-lg me-1"></i> Save Product
            </button>
            @if($product->exists)
                <a href="{{ route('admin.products.index') }}" class="btn btn-link w-100 mt-2">Back to products</a>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script src="{{ asset('assets/admin/js/product-form.js') }}"></script>
@endpush
