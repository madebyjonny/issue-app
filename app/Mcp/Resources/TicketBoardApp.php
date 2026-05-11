<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Ui\Enums\Library;

#[Description('Interactive visual Kanban board showing project tickets grouped by column.')]
#[AppMeta(libraries: [])]
class TicketBoardApp extends AppResource
{
    public function handle(Request $request): Response
    {
        return Response::view('mcp.ticket-board-app', [
            'title' => $this->title(),
        ]);
    }
}
