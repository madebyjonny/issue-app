# Issues

A Linear-inspired issue tracker built with Laravel. Manage projects, track tickets on a Kanban board, and interact with your issues through AI agents via a built-in MCP server — authenticated with OAuth 2.1.

## Features

- **Projects** — Create projects with a short key (e.g. `PROJ`). Invite team members with roles.
- **Kanban Board** — Drag-and-drop tickets across fully customizable, reorderable columns.
- **Tickets** — Issues auto-generate identifiers in `KEY-N` format with title, description, priority (urgent/high/medium/low/none), type (bug/feature/improvement/task), assignee, sprint, and story point estimate.
- **Sprints** — Create and manage sprints per project with active sprint tracking.
- **Epics** — Group related issues under a shared goal with color-coded progress tracking. Create tickets directly from the epic view.
- **Filters** — Filter the board by sprint, assignee, priority, type, and epic.
- **MCP Server** — AI-accessible `Issues` server secured with OAuth 2.1 via Laravel Passport.

### MCP Tools

| Tool             | Description                                     |
| ---------------- | ----------------------------------------------- |
| `ListProjects`   | List all projects the user has access to        |
| `ListTickets`    | List tickets with optional filters              |
| `GetTicket`      | Get a single ticket by key (e.g. `PROJ-1`)      |
| `CreateTicket`   | Create a new ticket                             |
| `UpdateTicket`   | Update an existing ticket                       |
| `DeleteTicket`   | Delete a ticket                                 |
| `MyTickets`      | List tickets assigned to the authenticated user |
| `GetSprintBoard` | Get the board state for a sprint                |
| `ListEpics`      | List epics for a project                        |
| `CreateEpic`     | Create a new epic                               |

## Tech Stack

- **PHP 8.3+** / **Laravel 13**
- **Laravel Breeze** — session-based web authentication
- **Laravel Passport** — OAuth 2.1 for MCP server authentication
- **Laravel MCP** — Model Context Protocol server for AI agent integration
- **Tailwind CSS** / **Alpine.js** / **Vite**

## Getting Started

### 1. Install & setup

```bash
git clone <repo-url> && cd issues-app
composer run setup
```

This installs dependencies, copies `.env`, generates the app key, runs migrations (including Passport tables), and builds frontend assets.

### 2. Generate Passport encryption keys

If keys don't already exist in `storage/`:

```bash
php artisan passport:keys
```

This creates `storage/oauth-private.key` and `storage/oauth-public.key`. These keys sign access tokens — **do not commit them to version control**.

### 3. Start the dev server

```bash
composer run dev
```

### 4. Expose with ngrok (for remote MCP clients)

MCP clients like Claude Desktop, Cursor, and VS Code need a publicly accessible URL to reach your OAuth and MCP endpoints. In a separate terminal:

```bash
ngrok http 8000
```

Use the `https://*.ngrok-free.app` URL as your server base URL when configuring MCP clients.

## OAuth 2.1 Authentication

The MCP server is protected with OAuth 2.1 via Laravel Passport. MCP clients authenticate through a standard OAuth flow:

1. **Discovery** — Client fetches `/.well-known/oauth-authorization-server` to find endpoints
2. **Dynamic Registration** — Client registers itself via `POST /oauth/register`
3. **Authorization** — User is redirected to `/oauth/authorize` to approve access
4. **Token Exchange** — Client exchanges the authorization code for an access token at `/oauth/token`
5. **MCP Access** — Client includes the Bearer token with all requests to `/mcp`

### Supported MCP Clients

Custom URI schemes are enabled for desktop clients in `config/mcp.php`:

- `claude://` — Claude Desktop
- `cursor://` — Cursor
- `vscode://` — VS Code / GitHub Copilot

### Connecting an MCP Client

Most MCP clients only need the server URL. Point your client at:

```
http://localhost:8000/mcp
```

Or if using ngrok:

```
https://your-subdomain.ngrok-free.app/mcp
```

The client will automatically discover OAuth endpoints, register, and prompt you to authorize.

#### Claude Desktop (`claude_desktop_config.json`)

```json
{
    "mcpServers": {
        "issues": {
            "type": "streamable-http",
            "url": "https://your-subdomain.ngrok-free.app/mcp"
        }
    }
}
```

#### Cursor / VS Code (`.mcp.json`)

```json
{
    "servers": {
        "issues": {
            "type": "streamable-http",
            "url": "https://your-subdomain.ngrok-free.app/mcp"
        }
    }
}
```

## Production Deployment

### Passport Keys

Generate keys on your production server:

```bash
php artisan passport:keys
```

Or load them from environment variables (set `PASSPORT_PRIVATE_KEY` and `PASSPORT_PUBLIC_KEY` in `.env`):

```env
PASSPORT_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----"

PASSPORT_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----
...
-----END PUBLIC KEY-----"
```

### Redirect Domains

By default, `config/mcp.php` allows all redirect domains (`'*'`). For production, restrict this to known domains:

```php
'redirect_domains' => [
    'https://your-app.com',
],
```

### Token Purging

Add a scheduled command to clean up expired tokens:

```php
// routes/console.php
Schedule::command('passport:purge')->hourly();
```

## Running Tests

```bash
composer test
```

## License

MIT
