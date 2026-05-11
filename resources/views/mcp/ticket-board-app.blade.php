<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: system-ui, sans-serif; font-size: 13px; background: #f9fafb; color: #111; height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
            #header { display: flex; align-items: center; gap: 10px; padding: 0 16px; height: 48px; background: #fff; border-bottom: 1px solid #e5e7eb; flex-shrink: 0; }
            #header h1 { font-size: 13px; font-weight: 600; color: #374151; }
            #project-select { font-size: 12px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 4px 8px; background: #fff; color: #374151; cursor: pointer; }
            #refresh-btn { margin-left: auto; font-size: 12px; padding: 4px 10px; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; color: #6b7280; cursor: pointer; }
            #refresh-btn:hover { background: #f9fafb; }
            #sprint-label { font-size: 11px; color: #9ca3af; }
            #status { flex: 1; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 13px; }
            #board { flex: 1; display: none; gap: 12px; padding: 16px; overflow-x: auto; overflow-y: hidden; align-items: flex-start; }
            .col { flex-shrink: 0; width: 230px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; max-height: 100%; }
            .col-header { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid #f3f4f6; flex-shrink: 0; }
            .col-name { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; }
            .col-count { font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 999px; background: #f3f4f6; color: #9ca3af; }
            .col-count.has { background: #eef2ff; color: #6366f1; }
            .ticket-list { flex: 1; overflow-y: auto; padding: 8px; display: flex; flex-direction: column; gap: 6px; min-height: 60px; }
            .ticket { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; }
            .ticket:hover { border-color: #d1d5db; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
            .ticket-meta { display: flex; align-items: center; gap: 6px; margin-bottom: 5px; }
            .ticket-id { font-family: monospace; font-size: 10px; color: #9ca3af; }
            .pdot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
            .ticket-title { font-size: 12px; font-weight: 500; color: #1f2937; line-height: 1.4; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .ticket-footer { display: flex; align-items: center; justify-content: space-between; }
            .tbadge { font-size: 10px; font-weight: 500; padding: 2px 6px; border-radius: 4px; }
            .avatar { width: 20px; height: 20px; border-radius: 50%; background: linear-gradient(135deg,#818cf8,#a78bfa); display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; color: #fff; flex-shrink: 0; }
            .empty-col { display: flex; align-items: center; justify-content: center; height: 56px; border: 1px dashed #e5e7eb; border-radius: 8px; color: #d1d5db; font-size: 11px; }
            ::-webkit-scrollbar { width: 4px; height: 4px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 2px; }
        </style>
        <script>
        var PCOL = { urgent:'#ef4444', high:'#fb923c', medium:'#facc15', low:'#60a5fa', none:'#d1d5db' };
        var TTYPE = {
            bug:         { bg:'#fee2e2', color:'#dc2626', label:'● Bug' },
            feature:     { bg:'#ede9fe', color:'#7c3aed', label:'◆ Feature' },
            improvement: { bg:'#dbeafe', color:'#2563eb', label:'▲ Improvement' },
            task:        { bg:'#f3f4f6', color:'#6b7280', label:'○ Task' }
        };

        function esc(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function showStatus(msg) {
            document.getElementById('status').textContent = msg;
            document.getElementById('status').style.display = 'flex';
            document.getElementById('board').style.display = 'none';
        }

        function renderBoard(data) {
            document.getElementById('sprint-label').textContent = data.sprint || '';
            var board = document.getElementById('board');
            board.innerHTML = '';
            data.columns.forEach(function(col) {
                var n = col.tickets.length;
                var colEl = document.createElement('div');
                colEl.className = 'col';
                var listId = 'tl-' + Math.random().toString(36).slice(2);
                colEl.innerHTML =
                    '<div class="col-header">' +
                        '<span class="col-name">' + esc(col.name) + '</span>' +
                        '<span class="col-count' + (n > 0 ? ' has' : '') + '">' + n + '</span>' +
                    '</div>' +
                    '<div class="ticket-list" id="' + listId + '"></div>';
                board.appendChild(colEl);
                var list = document.getElementById(listId);
                if (n === 0) {
                    list.innerHTML = '<div class="empty-col">Empty</div>';
                } else {
                    col.tickets.forEach(function(t) {
                        var ts = TTYPE[t.type] || TTYPE.task;
                        var el = document.createElement('div');
                        el.className = 'ticket';
                        el.innerHTML =
                            '<div class="ticket-meta">' +
                                '<span class="ticket-id">' + esc(t.identifier) + '</span>' +
                                '<span class="pdot" style="background:' + (PCOL[t.priority] || PCOL.none) + '"></span>' +
                            '</div>' +
                            '<div class="ticket-title">' + esc(t.title) + '</div>' +
                            '<div class="ticket-footer">' +
                                '<span class="tbadge" style="background:' + ts.bg + ';color:' + ts.color + '">' + ts.label + '</span>' +
                                (t.assignee ? '<div class="avatar" title="' + esc(t.assignee) + '">' + esc(t.assignee[0].toUpperCase()) + '</div>' : '') +
                            '</div>';
                        list.appendChild(el);
                    });
                }
            });
            document.getElementById('status').style.display = 'none';
            board.style.display = 'flex';
        }

        var _app = null;

        async function loadBoard(key) {
            if (!key || !_app) return;
            showStatus('Loading…');
            try {
                var r = await _app.callServerTool('get-sprint-board', { project_key: key });
                if (r.isError) { showStatus(r.content[0] ? r.content[0].text : 'Error.'); return; }
                renderBoard(JSON.parse(r.content[0].text));
            } catch(e) { showStatus('Error loading board.'); }
        }

        createMcpApp(async function(app) {
            _app = app;
            showStatus('Loading…');
            var projects = [];
            try {
                var r = await app.callServerTool('list-projects', {});
                if (!r.isError && r.content[0]) projects = JSON.parse(r.content[0].text);
            } catch(e) {}

            if (projects.length === 0) { showStatus('No projects found.'); return; }

            var sel = document.getElementById('project-select');
            projects.forEach(function(p) {
                var o = document.createElement('option');
                o.value = p.key;
                o.textContent = p.name + ' · ' + p.key;
                sel.appendChild(o);
            });

            await loadBoard(projects[0].key);
        });
        </script>
    </x-slot:head>

    <div id="header">
        <svg width="16" height="16" fill="none" stroke="#6366f1" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/>
        </svg>
        <h1>Board</h1>
        <div style="width:1px;height:16px;background:#e5e7eb"></div>
        <select id="project-select" onchange="loadBoard(this.value)"></select>
        <span id="sprint-label"></span>
        <button id="refresh-btn" onclick="loadBoard(document.getElementById('project-select').value)">↻ Refresh</button>
    </div>
    <div id="status">Loading…</div>
    <div id="board"></div>

</x-mcp::app>
