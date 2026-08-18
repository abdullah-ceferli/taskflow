@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'mt-2 text-sm font-medium text-rose-600']) }}>{{ $message }}</p>
@enderror