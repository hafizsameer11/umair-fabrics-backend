@extends('layouts.admin')
@section('title', 'Settings')
@section('header', 'Store Settings')
@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="card border-0 shadow-sm" style="max-width:560px;">
        <div class="card-body">
            <div class="mb-3"><label class="form-label fw-medium">Store name</label><input type="text" name="store_name" class="form-control" value="{{ $settings['store_name'] }}" required></div>
            <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="store_phone" class="form-control" value="{{ $settings['store_phone'] }}"></div>
            <div class="mb-3"><label class="form-label">Website</label><input type="url" name="store_website" class="form-control" value="{{ $settings['store_website'] }}" placeholder="https://umairfabrics.com"></div>
            <div class="mb-3"><label class="form-label">WhatsApp</label><input type="text" name="store_whatsapp" class="form-control" value="{{ $settings['store_whatsapp'] }}" placeholder="923704896496"></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="store_email" class="form-control" value="{{ $settings['store_email'] }}"></div>
            <div class="mb-4"><label class="form-label">Logo</label>
                @if($settings['store_logo'])<img src="{{ asset('storage/'.$settings['store_logo']) }}" class="d-block mb-2" style="max-height:60px">@endif
                <input type="file" name="logo" class="form-control" accept="image/*"></div>
            <button type="submit" class="btn btn-primary">Save settings</button>
        </div>
    </div>
</form>
@endsection
