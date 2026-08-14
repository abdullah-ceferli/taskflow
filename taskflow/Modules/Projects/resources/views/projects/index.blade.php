@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Projects</h1>
        <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">
            Create Project
        </a>
    </div>

    @if($projects->count())
    <div class="grid gap-6">
        @foreach($projects as $project)
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-semibold">
                        <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline">
                            {{ $project->name }}
                        </a>
                    </h2>
                    <p class="text-gray-600">{{ $project->slug }}</p>
                    <p class="text-sm text-gray-500 mt-2">{{ $project->description }}</p>
                    <span class="inline-block mt-3 px-3 py-1 text-sm bg-gray-200 rounded">
                        {{ $project->status }}
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Owner: {{ $project->owner->name }}</p>
                    <p class="text-sm text-gray-600">Members: {{ $project->members_count ?? 0 }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $projects->links() }}
    @else
    <div class="bg-gray-100 p-8 rounded text-center">
        <p class="text-gray-600">No projects yet. <a href="{{ route('projects.create') }}" class="text-blue-600">Create one!</a></p>
    </div>
    @endif
</div>
@endsection