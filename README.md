# Issues

A Linear-inspired issue tracker built with Laravel. Manage projects, track tickets on a Kanban board, and interact with your issues through AI agents via a built-in MCP server.

## Features

- **Projects** — Create projects with a short key (e.g. `PROJ`). Invite team members with roles.
- **Kanban Board** — Drag-and-drop tickets across fully customizable, reorderable columns.
- **Tickets** — Issues auto-generate identifiers in `KEY-N` format with title, description, priority (urgent/high/medium/low/none), type (bug/feature/improvement/task), assignee, sprint, and story point estimate.
- **Sprints** — Create and manage sprints per project with active sprint tracking.
- **Filters** — Filter the board by sprint and assignee.
- **MCP Server** — Exposes an AI-accessible `Issues` server with tools: `ListProjects`, `ListTickets`, `GetTicket`, `CreateTicket`, `UpdateTicket`, `DeleteTicket`, `MyTickets`, `GetSprintBoard`.

## Tech Stack

- **PHP 8.3+** / **Laravel 13**
- **Laravel Breeze** — authentication
- **Laravel MCP** — Model Context Protocol server for AI agent integration
- **Tailwind CSS** / **Vite**

## Getting Started

```bash
composer run setup
composer run dev
```

`composer run setup` installs dependencies, copies `.env`, generates the app key, runs migrations, and builds frontend assets.

## Running Tests

```bash
composer test
```

## License

MIT
