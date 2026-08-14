<?php

namespace Modules\Projects\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Projects\Models\Project;
use Modules\Projects\Http\Requests\Web\StoreProjectRequest;
use Modules\Projects\Http\Requests\Web\UpdateProjectRequest;
use Modules\Projects\Services\ProjectService;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Data\CreateProjectData;
use Modules\Projects\Data\UpdateProjectData;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService,
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    /**
     * Display all projects
     */
    public function index()
    {
        $projects = $this->projectRepository->paginate(15);
        return view('projects::projects.index', ['projects' => $projects]);
    }

    /**
     * Show project detail
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        return view('projects::projects.show', ['project' => $project]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->authorize('create', Project::class);
        return view('projects::projects.create');
    }

    /**
     * Store new project
     */
    public function store(StoreProjectRequest $request)
    {
        $data = new CreateProjectData(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            ownerId: auth()->id(),
            startsAt: $request->validated('starts_at') ? \DateTimeImmutable::createFromFormat('Y-m-d', $request->validated('starts_at')) : null,
            dueAt: $request->validated('due_at') ? \DateTimeImmutable::createFromFormat('Y-m-d', $request->validated('due_at')) : null,
        );

        $project = $this->projectService->create($data);

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects::projects.edit', ['project' => $project]);
    }

    /**
     * Update project
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = new UpdateProjectData(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            status: $request->validated('status'),
            startsAt: $request->validated('starts_at') ? \DateTimeImmutable::createFromFormat('Y-m-d', $request->validated('starts_at')) : null,
            dueAt: $request->validated('due_at') ? \DateTimeImmutable::createFromFormat('Y-m-d', $request->validated('due_at')) : null,
        );

        $this->projectService->update($project, $data);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    /**
     * Archive project
     */
    public function archive(Project $project)
    {
        $this->authorize('archive', $project);

        $this->projectService->archive($project);

        return redirect()->route('projects.index')->with('success', 'Project archived successfully.');
    }

    /**
     * Delete project
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $this->projectService->delete($project);

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
