<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorizeOwner($project);

        $existing = User::where('email', $request->email)->first();

        $validated = $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'name'     => $existing ? ['nullable'] : ['required', 'string', 'max:255'],
            'password' => $existing ? ['nullable'] : ['required', 'string', 'min:8'],
        ]);

        if ($existing) {
            $user = $existing;
        } else {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);
        }

        if ($project->owner_id === $user->id) {
            return back()->withErrors(['email' => 'The project owner is already a member.']);
        }

        if ($project->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'This user is already a member.']);
        }

        $project->members()->attach($user->id, ['role' => 'member']);

        return back()->with('success', $existing ? 'Member added.' : 'Account created and member added.');
    }

    public function destroy(Project $project, User $user)
    {
        $this->authorizeOwner($project);

        if ($project->owner_id === $user->id) {
            return back()->withErrors(['email' => 'Cannot remove the project owner.']);
        }

        $project->members()->detach($user->id);

        return back()->with('success', 'Member removed.');
    }

    private function authorizeOwner(Project $project): void
    {
        abort_unless($project->owner_id === auth()->id(), 403);
    }
}
