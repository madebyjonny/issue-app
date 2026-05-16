<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: system-ui, sans-serif; font-size: 13px; background: #f9fafb; color: #111; height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
            #header { display: flex; align-items: center; gap: 10px; padding: 0 16px; height: 48px; background: #fff; border-bottom: 1px solid #e5e7eb; flex-shrink: 0; }
            #header h1 { font-size: 13px; font-weight: 600; color: #374151; }
            #sprint-label { font-size: 11px; color: #9ca3af; }
            #project-label { font-size: 12px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 4px 8px; background: #fff; color: #374151; }
            #status { flex: 1; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 13px; }
            #board { flex: 1; display: flex; gap: 12px; padding: 16px; overflow-x: auto; overflow-y: hidden; align-items: flex-start; }
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
    </x-slot:head>

    @php
        $priorityColors = ['urgent' => '#ef4444', 'high' => '#fb923c', 'medium' => '#facc15', 'low' => '#60a5fa', 'none' => '#d1d5db'];
        $typeStyles = [
            'bug'         => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => '● Bug'],
            'feature'     => ['bg' => '#ede9fe', 'color' => '#7c3aed', 'label' => '◆ Feature'],
            'improvement' => ['bg' => '#dbeafe', 'color' => '#2563eb', 'label' => '▲ Improvement'],
            'task'        => ['bg' => '#f3f4f6', 'color' => '#6b7280', 'label' => '○ Task'],
        ];
    @endphp

    <div id="header">
        <svg width="16" height="16" fill="none" stroke="#6366f1" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z"/>
        </svg>
        <h1>Board</h1>
        @if($project)
            <div style="width:1px;height:16px;background:#e5e7eb"></div>
            <span id="project-label">{{ $project->name }} · {{ $project->key }}</span>
            <span id="sprint-label">{{ $sprint }}</span>
        @endif
    </div>

    @if($error)
        <div id="status">{{ $error }}</div>
    @elseif($project)
        <div id="board">
            @foreach($columns as $col)
                @php $count = count($col['tickets']); @endphp
                <div class="col">
                    <div class="col-header">
                        <span class="col-name">{{ $col['name'] }}</span>
                        <span class="col-count {{ $count > 0 ? 'has' : '' }}">{{ $count }}</span>
                    </div>
                    <div class="ticket-list">
                        @if($count === 0)
                            <div class="empty-col">Empty</div>
                        @else
                            @foreach($col['tickets'] as $ticket)
                                @php
                                    $ts = $typeStyles[$ticket['type']] ?? $typeStyles['task'];
                                    $pdot = $priorityColors[$ticket['priority']] ?? $priorityColors['none'];
                                @endphp
                                <div class="ticket">
                                    <div class="ticket-meta">
                                        <span class="ticket-id">{{ $ticket['identifier'] }}</span>
                                        <span class="pdot" style="background:{{ $pdot }}"></span>
                                    </div>
                                    <div class="ticket-title">{{ $ticket['title'] }}</div>
                                    <div class="ticket-footer">
                                        <span class="tbadge" style="background:{{ $ts['bg'] }};color:{{ $ts['color'] }}">{{ $ts['label'] }}</span>
                                        @if($ticket['assignee'])
                                            <div class="avatar" title="{{ $ticket['assignee'] }}">{{ strtoupper(substr($ticket['assignee'], 0, 1)) }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div id="status">No project loaded.</div>
    @endif

</x-mcp::app>

