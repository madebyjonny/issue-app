<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Check whether the user is a member or owner of the project.
     */
    private function isMember(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id
            || $project->members()->where('id', $user->id)->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $this->isMember($user, $project);
    }

    public function create(User $user): bool
    {
        return true; // any authenticated user may create a project
    }

    public function update(User $user, Project $project): bool
    {
        return $this->isMember($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id; // only the owner may delete
    }
}
