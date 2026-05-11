<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Check whether the user is a member or owner of the ticket's project.
     */
    private function isMember(User $user, Ticket $ticket): bool
    {
        $project = $ticket->project;

        return $project->owner_id === $user->id
            || $project->members()->where('users.id', $user->id)->exists();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $this->isMember($user, $ticket);
    }

    public function create(User $user, Ticket $ticket): bool
    {
        return $this->isMember($user, $ticket);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $this->isMember($user, $ticket);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->isMember($user, $ticket);
    }
}
