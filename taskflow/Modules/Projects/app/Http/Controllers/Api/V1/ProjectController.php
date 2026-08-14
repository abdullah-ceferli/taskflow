<?php

namespace Modules\Projects\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Projects\Models\Project;
use Modules\Projects\Http\Requests\Api\V1\StoreProjectApiRequest;
use Modules\Projects\Http\Resources\ProjectResource;
use Modules\Projects\Http\Resources\ProjectCollection;
use Modules\Projects\Services\ProjectService;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Data\CreateProjectData;
use Modules\Projects\Data\UpdateProjectData;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService,
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    /**
     * List all projects (paginated)
     */
    public function index()
    {
        $this->authorize('viewAny', Project::class);
        
        $projects = $this->projectRepository->paginate(20);
        return new ProjectCollection($projects);
    }

    /**
     * Get single project
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        return new ProjectResource($project);
    }

    /**
     * Create new project
     */
    public function store(StoreProjectApiRequest $request)
    {
        $this->authorize('create', Project::class);

        $data = new CreateProjectData(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            ownerId: auth()->id(),
            startsAt: $request->validated('starts_at') ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $request->validated('starts_at')) : null,
            dueAt: $request->validated('due_at') ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $request->validated('due_at')) : null,
        );

        $project = $this->projectService->create($data);

        return new ProjectResource($project), Response::HTTP_CREATED;
    }

    /**
     * Update project
     */
    public function update(StoreProjectApiRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = new UpdateProjectData(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            startsAt: $request->validated('starts_at') ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $request->validated('starts_at')) : null,
            dueAt: $request->validated('due_at') ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $request->validated('due_at')) : null,
        );

        $this->projectService->update($project, $data);

        return new ProjectResource($project);
    }

    /**
     * Delete project
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $this->projectService->delete($project);

        return response()->noContent();
    }

    /**
     * Archive project
     */
    public function archive(Project $project)
    {
        $this->authorize('archive', $project);

        $this->projectService->archive($project);

        return new ProjectResource($project);
    }
}
