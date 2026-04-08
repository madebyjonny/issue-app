<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateTicket;
use App\Mcp\Tools\DeleteTicket;
use App\Mcp\Tools\GetSprintBoard;
use App\Mcp\Tools\GetTicket;
use App\Mcp\Tools\ListProjects;
use App\Mcp\Tools\ListTickets;
use App\Mcp\Tools\MyTickets;
use App\Mcp\Tools\UpdateTicket;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Issues')]
#[Version('0.1.0')]
#[Instructions('AI-first issue tracker. Use these tools to manage projects and tickets. Ticket identifiers follow the format KEY-N (e.g. PROJ-1). You can list projects, view boards, create/update/delete tickets, and filter by sprint, assignee, or priority.')]
class Issues extends Server
{
    protected array $tools = [
        ListProjects::class,
        ListTickets::class,
        GetTicket::class,
        CreateTicket::class,
        UpdateTicket::class,
        DeleteTicket::class,
        MyTickets::class,
        GetSprintBoard::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
