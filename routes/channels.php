<?php

use App\Models\Channel;
use App\Models\Doc;
use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for each project messaging channel
Broadcast::channel('channel.{channelId}', function ($user, $channelId) {
    $channel = Channel::find($channelId);
    if (! $channel) {
        return false;
    }
    // Members of the project can join
    return $channel->project->members()->where('user_id', $user->id)->exists()
        || $channel->project->owner_id === $user->id;
});

// Presence channel for board collaboration
Broadcast::channel('board.{projectId}', function ($user, $projectId) {
    $project = Project::find($projectId);
    if (! $project) {
        return false;
    }
    $isMember = $project->members()->where('user_id', $user->id)->exists()
        || $project->owner_id === $user->id;

    if (! $isMember) {
        return false;
    }

    return ['id' => $user->id, 'name' => $user->name];
});

// Private DM channel per user
Broadcast::channel('dm.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Private huddle signalling channel per user
Broadcast::channel('huddle.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Private project-wide channel for huddle presence notifications
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    $project = Project::find($projectId);
    if (! $project) {
        return false;
    }
    return $project->members()->where('user_id', $user->id)->exists()
        || $project->owner_id === $user->id;
});

// Private collaborative whiteboard channel per doc
Broadcast::channel('whiteboard.{docId}', function ($user, $docId) {
    $doc = Doc::find($docId);
    if (! $doc) {
        return false;
    }
    $project = $doc->project;
    return $project->members()->where('user_id', $user->id)->exists()
        || $project->owner_id === $user->id;
});
