@if (session('success'))
    <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
        Please correct the highlighted fields and try again.
    </div>
@endif