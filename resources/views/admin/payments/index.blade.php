@extends('layouts.admin')
@section('title', 'Payments')
@section('header', 'Payment Methods')
@section('content')
<div class="row g-4">
    @foreach($methods as $method)
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.payments.update', $method) }}">
                        @csrf @method('PUT')
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <input type="text" name="name" class="form-control form-control-lg border-0 fw-semibold p-0" value="{{ $method->name }}">
                            <div class="form-check form-switch ms-3">
                                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" @checked($method->is_enabled)>
                                <label class="form-check-label small">Enabled</label>
                            </div>
                        </div>
                        <label class="form-label small text-muted">Checkout instructions</label>
                        <textarea name="instructions" class="form-control" rows="4">{{ $method->instructions }}</textarea>
                        <button type="submit" class="btn btn-primary btn-sm mt-3">Save</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
