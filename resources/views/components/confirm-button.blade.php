@props(['message' => 'Are you sure?', 'label' => 'Delete'])

<form method="POST" {{ $attributes->merge(['data-confirm' => $message]) }}>
    @csrf
    @method('DELETE')
    <button type="submit" class="rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">{{ $label }}</button>
</form>