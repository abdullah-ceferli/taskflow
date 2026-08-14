@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ $project->name }}</h1>
        @can('update', $project)
        <div class="space-x-2">
            <a href="{{ route('projects.edit', $project) }}" class="bg-blue-600 text-white px-4 py-2 rounded">
                Edit
            </a>
            @if($project->status !== 'archived')
            <form method="POST" action="{{ route('projects.archive', $project) }}" class="inline">
                @csrf
                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded">Archive</button>
            </form>
            @endif
        </div>
        @endcan
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h2 class="text-xl font-semibold mb-4">Details</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="font-semibold text-gray-600">Slug:</dt>
                        <dd>{{ $project->slug }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600">Status:</dt>
                        <dd>{{ $project->status }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600">Owner:</dt>
                        <dd>{{ $project->owner->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600">Description:</dt>
                        <dd>{{ $project->description }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600">Starts At:</dt>
                        <dd>{{ $project->starts_at?->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600">Due At:</dt>
                        <dd>{{ $project->due_at?->format('Y-m-d') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Members</h2>
                    @can('manageMember', $project)
                    <a href="{{ route('projects.members.create', $project) }}" class="text-blue-600 text-sm">+ Add</a>
                    @endcan
                </div>

                <div class="space-y-2">
                    @foreach($project->members as $member)
                    <div class="flex justify-between items-center p-2 border-b">
                        <div>
                            <p class="font-semibold">{{ $member->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $member->member_role }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection