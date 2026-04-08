# Issues

A Linear-inspired issue tracker built with Laravel. Manage projects, track tickets on a Kanban board, and interact with your issues through AI agents via a built-in MCP server.

## Features

- **Projects** — Create projects with a short key (e.g. `PROJ`). Invite team members with roles.
- **Kanban Board** — Drag-and-drop tickets across fully customizable columns. Filter by sprint, assignee, priority, type, and epic.
- **Tickets** — Auto-generated identifiers (`KEY-N`), title, description, priority, type, assignee, sprint, epic, and story point estimates.
- **Sprints** — Create and manage iteration cycles with active sprint tracking.
- **Epics** — Group related issues under a shared goal with color-coded progress tracking.
- **MCP Server** — AI-accessible server with tools for managing projects, tickets, sprints, and epics.

## Tech Stack

- **PHP 8.3+** / **Laravel 13**
- **Laravel Breeze** — authentication
- **Laravel MCP** — Model Context Protocol server for AI agent integration
- **Tailwind CSS** / **Vite** / **Alpine.js**

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js & npm
- SQLite (default) or another supported database

### Setup

```bash
git clone <repo-url> && cd issues-app
composer run setup
```

This installs dependencies, copies `.env`, generates the app key, runs migrations, and builds frontend assets.

### Development

```bash
composer run dev
```

This starts the Laravel dev server, queue worker, log tail, and Vite in parallel. The app will be available at `http://localhost:8000`.

## MCP Server

The app exposes an MCP (Model Context Protocol) server at `/mcp` that lets AI agents interact with your issue tracker.

### Available Tools

| Tool             | Description                                                  |
| ---------------- | ------------------------------------------------------------ |
| `ListProjects`   | List all projects                                            |
| `ListTickets`    | List tickets with filters (sprint, assignee, epic, priority) |
| `GetTicket`      | Get a single ticket by identifier                            |
| `CreateTicket`   | Create a new ticket                                          |
| `UpdateTicket`   | Update an existing ticket                                    |
| `DeleteTicket`   | Delete a ticket                                              |
| `MyTickets`      | List tickets assigned to the current user                    |
| `GetSprintBoard` | Get the board state for a sprint                             |
| `ListEpics`      | List epics for a project                                     |

### Connecting Locally

The MCP server uses HTTP+SSE transport. When running locally, AI clients (VS Code Copilot, Cursor, Claude Desktop, etc.) need a publicly reachable URL to connect. Use [ngrok](https://ngrok.com) to tunnel your local server:

```bash
# Start the app
composer run dev

# In another terminal, start ngrok
ngrok http 8000
```

ngrok will give you a public URL like `https://abc123.ngrok-free.app`.

### Client Configuration

Add the MCP server to your AI client config. For example, in VS Code (`.vscode/mcp.json`):

```json
{
    "servers": {
        "issues": {
            "type": "http",
            "url": "https://abc123.ngrok-free.app/mcp"
        }
    }
}
```

For Claude Desktop (`claude_desktop_config.json`):

```json
{
    "mcpServers": {
        "issues": {
            "type": "http",
            "url": "https://abc123.ngrok-free.app/mcp"
        }
    }
}
```

Replace the ngrok URL with your own. The server will prompt for OAuth authentication on first connection.

> **Tip:** If you're only connecting from the same machine, you can use `http://localhost:8000/mcp` directly — ngrok is only needed when the client cannot reach localhost (e.g. cloud-hosted AI services).

## Running Tests

```bash
composer test
```

## License

MIT
