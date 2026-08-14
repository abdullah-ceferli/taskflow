@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Create Project</h1>

    <form method="POST" action="{{ route('projects.store') }}" class="bg-white p-6 rounded-lg shadow">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-semibold mb-2">Project Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border p-2 rounded" required>
            @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold mb-2">Slug</label>
            <input type="text" name="slug" value="{{ old('slug') }}" class="w-full border p-2 rounded">
            @error('slug')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold mb-2">Description</label>
            <textarea name="description" class="w-full border p-2 rounded h-24">{{ old('description') }}</textarea>
            @error('description')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Starts At</label>
                <input type="date" name="starts_at" value="{{ old('starts_at') }}" class="w-full border p-2 rounded">
                @error('starts_at')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Due At</label>
                <input type="date" name="due_at" value="{{ old('due_at') }}" class="w-full border p-2 rounded">
                @error('due_at')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Create</button>
            <a href="{{ route('projects.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded">Cancel</a>
        </div>
    </form>
</div>
@endsection