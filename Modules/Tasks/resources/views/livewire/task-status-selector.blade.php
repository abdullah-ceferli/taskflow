<div class="rounded-2xl border bg-white p-6 shadow-sm">
    <h3 class="font-semibold">Move task forward</h3>
    <form wire:submit="change" class="mt-4">
        <select wire:model="status" @disabled($available === []) class="w-full rounded-xl border border-slate-300 px-3 py-2.5">
            @foreach($available as $item)<option value="{{ $item->value }}">{{ ucfirst(str_replace('_', ' ', $item->value)) }}</option>@endforeach
        </select>
        <button @disabled($available === []) wire:loading.attr="disabled" class="mt-3 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-50">Update status</button>
    </form>
</div>