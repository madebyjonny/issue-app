import { Editor, mergeAttributes } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Placeholder from "@tiptap/extension-placeholder";
import Mention from "@tiptap/extension-mention";
import Link from "@tiptap/extension-link";
import tippy from "tippy.js";

// Self-initialize when the page contains a channel root element
document.addEventListener("DOMContentLoaded", () => {
    const root = document.getElementById("channel-root");
    if (root) messagingApp(root);
});

function renderMessageBody(el, extensions = null) {
    try {
        const json = JSON.parse(el.dataset.json || "null");
        if (!json) return;
        const tmp = document.createElement("div");
        const view = new Editor({
            element: tmp,
            content: json,
            editable: false,
            extensions: extensions ?? [StarterKit],
        });
        el.innerHTML = view.getHTML();
        view.destroy();
    } catch (_) {
        // Fallback: leave empty
    }
}

function jsonToHtml(json, extensions = null) {
    try {
        const tmp = document.createElement("div");
        const view = new Editor({
            element: tmp,
            content: json,
            editable: false,
            extensions: extensions ?? [StarterKit],
        });
        const html = view.getHTML();
        view.destroy();
        return html;
    } catch (_) {
        return "";
    }
}

/** Mention extension variant for read-only rendering — outputs <a> links */
function buildReadonlyMention(projectId) {
    return Mention.extend({
        renderHTML({ node, HTMLAttributes }) {
            return [
                "a",
                mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, {
                    href: `/projects/${projectId}/tickets/${node.attrs.id}`,
                    "data-ticket-id": String(node.attrs.id),
                }),
                `@${node.attrs.label ?? node.attrs.id}`,
            ];
        },
    }).configure({
        HTMLAttributes: {
            class: "inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-semibold no-underline hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition",
        },
        suggestion: {
            items: () => [],
            render: () => ({
                onStart() {},
                onUpdate() {},
                onKeyDown: () => false,
                onExit() {},
            }),
        },
    });
}

function buildMentionSuggestion(tickets) {
    return {
        items: ({ query }) =>
            tickets
                .filter(
                    (t) =>
                        t.identifier
                            .toLowerCase()
                            .includes(query.toLowerCase()) ||
                        t.title.toLowerCase().includes(query.toLowerCase()),
                )
                .slice(0, 8),
        render: () => {
            let component, popup;
            return {
                onStart(props) {
                    component = document.createElement("div");
                    component.className =
                        "bg-white dark:bg-[#1c1c20] border border-gray-200 dark:border-white/[0.1] rounded-xl shadow-lg py-1 text-sm w-64";
                    document.body.appendChild(component);
                    popup = tippy("body", {
                        getReferenceClientRect: props.clientRect,
                        appendTo: () => document.body,
                        content: component,
                        showOnCreate: true,
                        interactive: true,
                        trigger: "manual",
                        placement: "bottom-start",
                    });
                    renderMentionList(component, props);
                },
                onUpdate(props) {
                    renderMentionList(component, props);
                },
                onKeyDown(props) {
                    if (props.event.key === "Escape") {
                        popup[0].hide();
                        return true;
                    }
                    return false;
                },
                onExit() {
                    popup[0].destroy();
                    component.remove();
                },
            };
        },
    };
}

function renderMentionList(el, props) {
    el.innerHTML = "";
    if (!props.items.length) {
        el.innerHTML =
            '<p class="px-3 py-2 text-gray-400 text-xs">No tickets found</p>';
        return;
    }
    props.items.forEach((ticket) => {
        const btn = document.createElement("button");
        btn.className =
            "w-full flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/[0.05] text-left transition";
        btn.innerHTML = `
            <span class="text-xs font-mono font-semibold text-indigo-600 dark:text-indigo-400 shrink-0">${ticket.identifier}</span>
            <span class="text-gray-700 dark:text-gray-300 truncate text-xs">${ticket.title}</span>
        `;
        btn.addEventListener("click", () =>
            props.command({ id: ticket.id, label: ticket.identifier }),
        );
        el.appendChild(btn);
    });
}

function messagingApp(root) {
    if (!root) return;

    const channelId = root.dataset.channelId;
    const projectId = root.dataset.projectId;
    const userId = parseInt(root.dataset.userId, 10);
    const sendUrl = root.dataset.sendUrl;
    const aiUrl = root.dataset.aiUrl;
    const ticketsCreateUrl = root.dataset.ticketsCreateUrl || null;
    const aiDocUrl = root.dataset.aiDocUrl || null;
    const docsSearchUrl = root.dataset.docsSearchUrl || null;
    const huddleStartUrl = root.dataset.huddleStartUrl;
    const huddleSignalUrl = root.dataset.huddleSignalUrl;
    const tickets = JSON.parse(root.dataset.ticketsJson || "[]");

    // ── Read-only render setup (includes Mention → clickable chips) ──
    const renderExtensions = [
        StarterKit,
        buildReadonlyMention(projectId),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: { target: null, rel: null },
        }),
    ];

    /** Recursively extract plain text from a Tiptap JSON node */
    function extractTextFromTiptap(node) {
        if (!node) return "";
        if (node.type === "text") return node.text || "";
        if (Array.isArray(node.content))
            return node.content.map(extractTextFromTiptap).join(" ").trim();
        return "";
    }

    function escHtml(s) {
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }
    function ticketCardHtml(t) {
        const pc =
            {
                high: "text-red-500",
                medium: "text-amber-500",
                low: "text-blue-400",
            }[t.priority?.toLowerCase()] ?? "text-gray-400";
        return `<a href="/projects/${projectId}/tickets/${t.id}"
            class="flex items-center gap-2.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.07] transition no-underline">
            <span class="shrink-0 font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-1.5 py-0.5 rounded">${escHtml(t.identifier)}</span>
            <span class="flex-1 min-w-0 text-sm text-gray-800 dark:text-gray-200 font-medium truncate">${escHtml(t.title)}</span>
            ${t.status ? `<span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">${escHtml(t.status)}</span>` : ""}
            ${t.priority ? `<span class="shrink-0 text-xs capitalize ${pc}">${escHtml(t.priority)}</span>` : ""}
        </a>`;
    }
    function buildTicketCards(ids) {
        if (!ids?.length) return "";
        const cards = ids
            .map((id) => tickets.find((t) => String(t.id) === String(id)))
            .filter(Boolean)
            .map(ticketCardHtml)
            .join("");
        return cards
            ? `<div class="flex flex-col gap-1.5 mt-2">${cards}</div>`
            : "";
    }

    const messagesList = document.getElementById("messages-list");
    const sendBtn = document.getElementById("send-message");
    const selectionBar = document.getElementById("selection-toolbar");
    const selectedCount = document.getElementById("selected-count");
    const aiSummariseBtn = document.getElementById("ai-summarise-btn");
    const clearSelBtn = document.getElementById("clear-selection");
    const aiSuggestion = document.getElementById("ai-suggestion");
    const aiSummaryEl = document.getElementById("ai-summary");
    const aiActionsEl = document.getElementById("ai-actions");
    const aiTicketsEl = document.getElementById("ai-ticket-suggestions");
    const aiDismiss = document.getElementById("ai-dismiss");
    const threadPanel = document.getElementById("thread-panel");
    const threadClose = document.getElementById("thread-close");
    const threadMessages = document.getElementById("thread-messages");
    const threadEditor = document.getElementById("thread-editor");
    const threadSend = document.getElementById("thread-send");
    const huddleBtn = document.getElementById("huddle-btn");
    const huddlePanel = document.getElementById("huddle-panel");
    const huddleEnd = document.getElementById("huddle-end");
    const huddleMute = document.getElementById("huddle-mute");
    const huddleParticipants = document.getElementById("huddle-participants");

    // ── Render existing message bodies ──────────────────────────────
    document
        .querySelectorAll(".message-body")
        .forEach((el) => renderMessageBody(el, renderExtensions));

    // ── Main Tiptap editor ──────────────────────────────────────────
    const editor = new Editor({
        element: document.getElementById("message-editor"),
        extensions: [
            StarterKit,
            Placeholder.configure({ placeholder: "Message #channel..." }),
            Link.configure({
                openOnClick: false,
                HTMLAttributes: { target: null, rel: null },
            }),
            Mention.configure({
                HTMLAttributes: { class: "mention" },
                suggestion: buildMentionSuggestion(tickets),
            }),
        ],
        editorProps: {
            attributes: {
                class: "px-3 pt-3 pb-1 min-h-[60px] focus:outline-none",
            },
        },
    });

    // ── Thread Tiptap editor ────────────────────────────────────────
    const threadEditorInstance = new Editor({
        element: threadEditor,
        extensions: [
            StarterKit,
            Placeholder.configure({ placeholder: "Reply in thread..." }),
            Mention.configure({
                HTMLAttributes: { class: "mention" },
                suggestion: buildMentionSuggestion(tickets),
            }),
        ],
    });

    // ── /search command ─────────────────────────────────────────────
    const searchOverlay = (() => {
        const el = document.createElement("div");
        el.id = "slash-search-overlay";
        el.className =
            "hidden absolute bottom-full left-0 right-0 mb-2 z-50 bg-white dark:bg-[#1a1a1f] border border-gray-200 dark:border-white/[0.1] rounded-xl shadow-xl p-3 mx-4";
        el.innerHTML = `
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>
                </svg>
                <input id="slash-search-input" type="text" placeholder="Search docs…"
                       class="flex-1 bg-transparent text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 outline-none">
                <button id="slash-search-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xs">✕</button>
            </div>
            <div id="slash-search-results" class="space-y-1 max-h-64 overflow-y-auto"></div>
        `;
        // Insert relative to the composer area
        const composerArea =
            document
                .getElementById("message-editor")
                ?.closest(".relative, form, [class*='border']") ||
            document.getElementById("message-editor")?.parentElement;
        if (composerArea) {
            composerArea.style.position = "relative";
            composerArea.appendChild(el);
        }
        return el;
    })();

    const slashSearchInput = searchOverlay.querySelector("#slash-search-input");
    const slashSearchResults = searchOverlay.querySelector(
        "#slash-search-results",
    );
    const slashSearchClose = searchOverlay.querySelector("#slash-search-close");

    function showSlashSearch(initialQuery = "") {
        if (!docsSearchUrl) return;
        searchOverlay.classList.remove("hidden");
        slashSearchInput.value = initialQuery;
        slashSearchInput.focus();
        if (initialQuery) performDocSearch(initialQuery);
    }

    function hideSlashSearch() {
        searchOverlay.classList.add("hidden");
        slashSearchResults.innerHTML = "";
    }

    slashSearchClose?.addEventListener("click", hideSlashSearch);

    let slashDebounce;
    slashSearchInput?.addEventListener("input", () => {
        clearTimeout(slashDebounce);
        const q = slashSearchInput.value.trim();
        if (!q) {
            slashSearchResults.innerHTML = "";
            return;
        }
        slashDebounce = setTimeout(() => performDocSearch(q), 250);
    });

    slashSearchInput?.addEventListener("keydown", (e) => {
        if (e.key === "Escape") hideSlashSearch();
    });

    async function performDocSearch(q) {
        if (!docsSearchUrl) return;
        const res = await fetch(`${docsSearchUrl}?q=${encodeURIComponent(q)}`, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const docs = await res.json();
        if (!docs.length) {
            slashSearchResults.innerHTML =
                '<p class="text-xs text-gray-400 px-1 py-1">No results found</p>';
        } else {
            slashSearchResults.innerHTML = docs
                .map(
                    (d) => `
                <a href="${escHtml(d.url)}"
                   class="flex items-start gap-2 px-2 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/[0.05] transition">
                    <svg class="w-4 h-4 flex-shrink-0 text-gray-400 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${escHtml(d.title)}</p>
                        ${d.excerpt ? `<p class="text-xs text-gray-500 dark:text-gray-400 truncate">${escHtml(d.excerpt)}</p>` : ""}
                    </div>
                </a>
            `,
                )
                .join("");
        }
    }

    // ── Slash command menu ─────────────────────────────────────────
    const SLASH_COMMANDS = [
        ...(root.dataset.docsStoreUrl
            ? [
                  {
                      id: "whiteboard",
                      label: "Whiteboard",
                      desc: "Start a live collaborative canvas",
                      icon: `<svg class="w-3.5 h-3.5 text-violet-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>`,
                      accent: "violet",
                  },
              ]
            : []),
        ...(docsSearchUrl
            ? [
                  {
                      id: "search",
                      label: "Search docs",
                      desc: "Find a document",
                      icon: `<svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/></svg>`,
                      accent: "indigo",
                  },
              ]
            : []),
    ];

    let slashPopupInst = null;
    let slashEl = null;
    let slashPhase = "list"; // "list" | "whiteboard-name"
    let slashHighlight = 0;

    function openSlashMenu() {
        if (slashPopupInst) return;
        slashEl = document.createElement("div");
        slashEl.className =
            "bg-white dark:bg-[#1c1c20] border border-gray-200 dark:border-white/[0.1] rounded-xl shadow-xl py-1.5 text-sm w-72";
        document.body.appendChild(slashEl);
        const editorEl = document.getElementById("message-editor");
        slashPopupInst = tippy(editorEl, {
            content: slashEl,
            showOnCreate: true,
            interactive: true,
            trigger: "manual",
            placement: "top-start",
            offset: [0, 10],
        });
        slashPhase = "list";
        slashHighlight = 0;
    }

    function closeSlashMenu() {
        slashPhase = "list";
        slashHighlight = 0;
        if (slashPopupInst) {
            slashPopupInst.destroy();
            slashPopupInst = null;
        }
        if (slashEl) {
            slashEl.remove();
            slashEl = null;
        }
    }

    function renderSlashList(query) {
        if (!slashEl || slashPhase !== "list") return;
        const q = (query || "").toLowerCase();
        const filtered = SLASH_COMMANDS.filter(
            (c) => !q || c.id.includes(q) || c.label.toLowerCase().includes(q),
        );
        slashEl.innerHTML = "";
        if (!filtered.length) {
            slashEl.innerHTML =
                '<p class="px-3 py-2 text-xs text-gray-400">No commands matched</p>';
            return;
        }
        slashHighlight = Math.max(
            0,
            Math.min(slashHighlight, filtered.length - 1),
        );
        filtered.forEach((cmd, i) => {
            const btn = document.createElement("button");
            btn.className = `w-full flex items-center gap-3 px-3 py-2.5 text-left transition rounded-lg mx-1 my-0.5 ${i === slashHighlight ? "bg-gray-100 dark:bg-white/[0.08]" : "hover:bg-gray-50 dark:hover:bg-white/[0.05]"}`;
            btn.style.width = "calc(100% - 8px)";
            btn.innerHTML = `
                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0
                    ${cmd.accent === "violet" ? "bg-violet-50 dark:bg-violet-900/30" : "bg-indigo-50 dark:bg-indigo-900/30"}">
                    ${cmd.icon}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-tight">${cmd.label}</p>
                    <p class="text-xs text-gray-400 leading-tight mt-0.5">${cmd.desc}</p>
                </div>
                <span class="text-[10px] font-mono text-gray-300 dark:text-gray-600">/${cmd.id}</span>
            `;
            btn.addEventListener("click", () => executeSlashCommand(cmd.id));
            slashEl.appendChild(btn);
        });
    }

    function renderWhiteboardNameInput() {
        if (!slashEl) return;
        slashPhase = "whiteboard-name";
        slashEl.innerHTML = `
            <div class="px-3 py-2.5">
                <p class="text-xs font-semibold text-violet-600 dark:text-violet-400 mb-2.5 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                    New whiteboard
                </p>
                <input id="slash-wb-title" type="text" placeholder="Give it a name…"
                       value=""
                       class="w-full bg-gray-50 dark:bg-white/[0.05] border border-gray-200 dark:border-white/[0.1] rounded-lg px-3 py-1.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-400">
                <div class="flex gap-2 mt-2.5">
                    <button id="slash-wb-confirm" class="flex-1 px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold rounded-lg transition">Create &amp; join</button>
                    <button id="slash-wb-cancel" class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition rounded-lg hover:bg-gray-50 dark:hover:bg-white/[0.05]">Cancel</button>
                </div>
            </div>
        `;
        const titleInput = slashEl.querySelector("#slash-wb-title");
        const confirmBtn = slashEl.querySelector("#slash-wb-confirm");
        const cancelBtn = slashEl.querySelector("#slash-wb-cancel");
        setTimeout(() => titleInput?.focus(), 50);
        titleInput?.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                e.stopPropagation();
                confirmWhiteboard(titleInput.value);
            }
            if (e.key === "Escape") {
                closeSlashMenu();
                editor.commands.clearContent();
                editor.commands.focus();
            }
            e.stopPropagation();
        });
        confirmBtn?.addEventListener("click", () =>
            confirmWhiteboard(titleInput?.value ?? ""),
        );
        cancelBtn?.addEventListener("click", () => {
            closeSlashMenu();
            editor.commands.clearContent();
            editor.commands.focus();
        });
    }

    async function confirmWhiteboard(rawTitle) {
        const title = rawTitle.trim() || "New Whiteboard";
        const docsStoreUrl = root.dataset.docsStoreUrl;
        if (!docsStoreUrl) return;
        closeSlashMenu();
        editor.commands.clearContent();
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch(docsStoreUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf ?? "",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ title, type: "whiteboard" }),
        });
        if (!res.ok) return;
        const data = await res.json();
        // Broadcast announcement to the channel
        const announcementBody = {
            type: "doc",
            content: [
                {
                    type: "paragraph",
                    content: [
                        { type: "text", text: "🎨 Started a whiteboard: " },
                        {
                            type: "text",
                            marks: [{ type: "bold" }],
                            text: title,
                        },
                        { type: "text", text: " — " },
                        {
                            type: "text",
                            marks: [
                                { type: "link", attrs: { href: data.url } },
                            ],
                            text: "→ Join here",
                        },
                    ],
                },
            ],
        };
        const msgRes = await fetch(sendUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf ?? "",
                "X-Socket-ID": window.Echo?.socketId() ?? "",
            },
            body: JSON.stringify({
                body: announcementBody,
                parent_id: null,
                mentioned_tickets: [],
            }),
        });
        if (msgRes.ok) {
            const msg = await msgRes.json();
            prependMessage(msg);
        }
        editor.commands.focus();
    }

    function executeSlashCommand(id) {
        if (id === "search") {
            closeSlashMenu();
            editor.commands.clearContent();
            showSlashSearch("");
        } else if (id === "whiteboard") {
            renderWhiteboardNameInput();
        }
    }

    // Slash command editor integration
    editor.on("update", () => {
        const text = editor.getText();
        if (
            text === "/" ||
            (text.startsWith("/") && !text.includes(" ") && text.length > 0)
        ) {
            const query = text.slice(1);
            if (!slashPopupInst) openSlashMenu();
            renderSlashList(query);
        } else {
            closeSlashMenu();
        }
    });

    document.getElementById("message-editor")?.addEventListener(
        "keydown",
        (e) => {
            if (!slashPopupInst || slashPhase !== "list") return;
            const buttons = slashEl?.querySelectorAll("button") ?? [];
            if (e.key === "ArrowDown") {
                e.preventDefault();
                slashHighlight =
                    (slashHighlight + 1) % Math.max(buttons.length, 1);
                renderSlashList(editor.getText().slice(1));
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                slashHighlight =
                    (slashHighlight - 1 + Math.max(buttons.length, 1)) %
                    Math.max(buttons.length, 1);
                renderSlashList(editor.getText().slice(1));
            } else if (e.key === "Enter" || e.key === "Tab") {
                const btn = buttons[slashHighlight];
                if (btn) {
                    e.preventDefault();
                    btn.click();
                }
            } else if (e.key === "Escape") {
                e.preventDefault();
                closeSlashMenu();
                editor.commands.clearContent();
                editor.commands.focus();
            }
        },
        true,
    ); // capture phase so this runs before the send-on-enter handler

    // ── Toolbar button handlers ─────────────────────────────────────
    document.querySelectorAll("[data-format]").forEach((btn) => {
        btn.addEventListener("click", () => {
            const fmt = btn.dataset.format;
            if (fmt === "bold") editor.chain().focus().toggleBold().run();
            if (fmt === "italic") editor.chain().focus().toggleItalic().run();
            if (fmt === "code") editor.chain().focus().toggleCode().run();
        });
    });

    // ── Send message ────────────────────────────────────────────────
    function extractMentionedTickets(doc) {
        const ids = [];
        function traverse(node) {
            if (node.type === "mention") ids.push(node.attrs.id);
            if (node.content) node.content.forEach(traverse);
        }
        if (doc) traverse(doc);
        return ids;
    }

    async function sendMessage(
        editorInstance,
        parentId = null,
        urlOverride = null,
    ) {
        const body = editorInstance.getJSON();
        const mentioned = extractMentionedTickets(body);
        if (editorInstance.isEmpty && !mentioned.length) return null;
        const res = await fetch(urlOverride || sendUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
            },
            body: JSON.stringify({
                body,
                parent_id: parentId,
                mentioned_tickets: mentioned,
            }),
        });
        if (res.ok) {
            const data = await res.json();
            editorInstance.commands.clearContent();
            // Show the sender's own message immediately (broadcast is toOthers only)
            if (!parentId) {
                prependMessage(data);
            }
            return data;
        }
        return null;
    }

    sendBtn?.addEventListener("click", () => sendMessage(editor));
    document
        .getElementById("message-editor")
        ?.addEventListener("keydown", (e) => {
            if (
                e.key === "Enter" &&
                !e.shiftKey &&
                !slashPopupInst &&
                !editor.state.selection.$from.parent.type.name.startsWith(
                    "code",
                )
            ) {
                e.preventDefault();
                sendMessage(editor);
            }
        });

    // ── Laravel Echo — listen for new messages ──────────────────────
    if (window.Echo) {
        window.Echo.private(`channel.${channelId}`).listen(
            "MessageSent",
            (msg) => {
                if (msg.parent_id) {
                    // Thread reply — bump the parent message's reply count in the list
                    incrementThreadCount(msg.parent_id);
                } else {
                    prependMessage(msg);
                }
            },
        );
    }

    // ── Thread count helpers ─────────────────────────────────────────
    function setThreadCount(parentId, count) {
        document
            .querySelectorAll(`.thread-open-btn[data-message-id="${parentId}"]`)
            .forEach((btn) => {
                btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/></svg>
                ${count} ${count === 1 ? "reply" : "replies"}`;
            });
    }

    function incrementThreadCount(parentId) {
        const btn = document.querySelector(
            `.thread-open-btn[data-message-id="${parentId}"]`,
        );
        if (!btn) return;
        const match = btn.textContent.trim().match(/^(\d+)/);
        const current = match ? parseInt(match[1]) : 0;
        setThreadCount(parentId, current + 1);
    }

    function prependMessage(msg) {
        if (msg.parent_id) return; // thread replies don't go in the main list
        if (messagesList?.querySelector(`[data-message-id="${msg.id}"]`))
            return; // already rendered

        const el = document.createElement("div");
        el.className =
            "group flex gap-3 px-2 py-1 rounded-lg hover:bg-gray-50 dark:hover:bg-white/[0.03] transition message-item";
        el.dataset.messageId = msg.id;
        el.dataset.user = msg.user.name;
        el.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-[11px] font-semibold text-white flex-shrink-0">
                ${msg.user.name[0].toUpperCase()}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-baseline gap-2 mb-0.5">
                    <span class="font-semibold text-sm text-gray-900 dark:text-white">${msg.user.name}</span>
                    <span class="text-xs text-gray-400">${new Date(msg.created_at).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</span>
                </div>
                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">${jsonToHtml(msg.body, renderExtensions)}${buildTicketCards(msg.mentioned_tickets)}</div>
                <button class="thread-open-btn flex mt-1 items-center gap-1.5 text-xs text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                        data-message-id="${msg.id}"
                        data-thread-url="${sendUrl}/${msg.id}/thread">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/></svg>
                    Reply in thread
                </button>
            </div>
            <div class="flex-shrink-0 flex items-start mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <input type="checkbox" class="message-select w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
            </div>`;
        messagesList?.insertAdjacentElement("afterbegin", el);
    }

    // ── Message selection ────────────────────────────────────────────
    document.addEventListener("change", (e) => {
        if (!e.target.matches(".message-select")) return;
        const selected = document.querySelectorAll(".message-select:checked");
        if (selected.length > 0) {
            selectionBar?.classList.remove("hidden");
            if (selectedCount)
                selectedCount.textContent = `${selected.length} selected`;
        } else {
            selectionBar?.classList.add("hidden");
        }
    });

    clearSelBtn?.addEventListener("click", () => {
        document
            .querySelectorAll(".message-select:checked")
            .forEach((cb) => (cb.checked = false));
        selectionBar?.classList.add("hidden");
    });

    // ── Shared AI analysis logic ─────────────────────────────────────
    async function runAnalysis(messageItems) {
        const messages = [];
        for (const item of messageItems) {
            const user = item.dataset.user;
            const text = item.dataset.text;
            if (user && text) messages.push({ user, text });

            // Include thread replies if this message has them
            const threadUrl = item.dataset.threadUrl;
            if (threadUrl) {
                try {
                    const tr = await fetch(threadUrl, {
                        headers: { "X-Requested-With": "XMLHttpRequest" },
                    });
                    const replies = await tr.json();
                    for (const r of replies) {
                        const rText = extractTextFromTiptap(r.body);
                        if (rText)
                            messages.push({
                                user: r.user.name,
                                text: `[thread reply] ${rText}`,
                            });
                    }
                } catch (_) {}
            }
        }

        if (!messages.length) return;

        const res = await fetch(aiUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
            },
            body: JSON.stringify({ messages }),
        });
        const data = await res.json();
        if (res.ok) {
            aiSuggestion?.classList.remove("hidden");
            if (aiSummaryEl) aiSummaryEl.textContent = data.summary || "";
            if (aiActionsEl) {
                aiActionsEl.innerHTML = (data.actions || [])
                    .map(
                        (a) =>
                            `<p class="text-xs text-gray-600 dark:text-gray-400">• ${escHtml(a)}</p>`,
                    )
                    .join("");
            }
            if (aiTicketsEl) {
                if (data.suggested_tickets?.length) {
                    aiTicketsEl.innerHTML = data.suggested_tickets
                        .map(
                            (t) => `
                        <div class="flex items-start gap-2 px-2 py-1.5 rounded-lg bg-white dark:bg-white/[0.04] border border-gray-200 dark:border-white/[0.1] text-xs">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white">${escHtml(t.title)}</p>
                                ${t.description ? `<p class="text-gray-500 dark:text-gray-400 mt-0.5">${escHtml(t.description)}</p>` : ""}
                            </div>
                            <button class="suggest-ticket-btn shrink-0 px-2 py-1 rounded bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition"
                                    data-title="${escHtml(t.title)}"
                                    data-description="${escHtml(t.description || "")}">
                                Create
                            </button>
                        </div>`,
                        )
                        .join("");
                } else {
                    aiTicketsEl.innerHTML = "";
                }
            }

            // "Start doc" button
            const existingDocBtn =
                aiSuggestion?.querySelector(".ai-start-doc-wrap");
            if (existingDocBtn) existingDocBtn.remove();
            if (aiDocUrl && aiSuggestion) {
                const docTitle =
                    (data.summary || "")
                        .split(/[.!\n]/)[0]
                        .trim()
                        .slice(0, 100) || "New document";
                const docWrap = document.createElement("div");
                docWrap.className = "ai-start-doc-wrap mt-1";
                docWrap.innerHTML = `
                    <button id="ai-start-doc-btn"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-300 dark:border-amber-700 text-xs font-medium text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition w-full"
                            data-summary="${escHtml(data.summary || "")}"
                            data-title="${escHtml(docTitle)}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                        Start a doc from this analysis
                    </button>
                    <div id="ai-doc-result" class="hidden mt-1 text-xs text-emerald-600 dark:text-emerald-400"></div>
                `;
                aiSuggestion.appendChild(docWrap);

                docWrap
                    .querySelector("#ai-start-doc-btn")
                    .addEventListener("click", async (e) => {
                        const btn = e.currentTarget;
                        const origHTML = btn.innerHTML;
                        btn.disabled = true;
                        btn.textContent = "Creating…";
                        try {
                            const r = await fetch(aiDocUrl, {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'meta[name="csrf-token"]',
                                    )?.content,
                                },
                                body: JSON.stringify({
                                    title: btn.dataset.title,
                                    body_text: btn.dataset.summary,
                                }),
                            });
                            const d = await r.json();
                            if (r.ok) {
                                const resultEl =
                                    docWrap.querySelector("#ai-doc-result");
                                resultEl.innerHTML = `Doc created: <a href="${escHtml(d.url)}" class="underline font-medium" target="_blank">${escHtml(d.title)}</a>`;
                                resultEl.classList.remove("hidden");
                                btn.classList.add("hidden");
                            } else {
                                btn.disabled = false;
                                btn.innerHTML = origHTML;
                                alert(d.error || "Failed to create doc.");
                            }
                        } catch (_) {
                            btn.disabled = false;
                            btn.innerHTML = origHTML;
                        }
                    });
            }

            clearSelBtn?.click();
        } else {
            alert(
                data.error ||
                    "AI analysis failed. Check your OpenAI key in Project Settings.",
            );
        }
    }

    // ── Multi-select Analyse button ──────────────────────────────────
    aiSummariseBtn?.addEventListener("click", async () => {
        const checkedBoxes = [
            ...document.querySelectorAll(".message-select:checked"),
        ];
        if (!checkedBoxes.length) return;

        aiSummariseBtn.disabled = true;
        aiSummariseBtn.textContent = "Thinking…";
        try {
            const items = checkedBoxes
                .map((cb) => cb.closest(".message-item"))
                .filter(Boolean);
            await runAnalysis(items);
        } finally {
            aiSummariseBtn.disabled = false;
            aiSummariseBtn.textContent = "Analyse with AI";
        }
    });

    // ── Per-message Analyse button ───────────────────────────────────
    messagesList?.addEventListener("click", async (e) => {
        const btn = e.target.closest(".msg-analyse-btn");
        if (!btn) return;
        const item = btn.closest(".message-item");
        if (!item) return;

        const origHTML = btn.innerHTML;
        btn.disabled = true;
        btn.textContent = "Thinking…";
        try {
            await runAnalysis([item]);
            // Scroll AI panel into view
            aiSuggestion?.scrollIntoView({
                behavior: "smooth",
                block: "nearest",
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHTML;
        }
    });

    // ── Create ticket from AI suggestion ────────────────────────────
    aiTicketsEl?.addEventListener("click", async (e) => {
        const btn = e.target.closest(".suggest-ticket-btn");
        if (!btn || !ticketsCreateUrl) return;

        const origText = btn.textContent.trim();
        btn.disabled = true;
        btn.textContent = "Creating…";

        try {
            const res = await fetch(ticketsCreateUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    )?.content,
                },
                body: JSON.stringify({
                    title: btn.dataset.title,
                    description: btn.dataset.description || null,
                    type: "task",
                    priority: "none",
                }),
            });
            const data = await res.json();
            if (res.ok) {
                btn.textContent = `✓ ${data.identifier}`;
                btn.classList.replace("bg-indigo-600", "bg-emerald-600");
                btn.classList.replace(
                    "hover:bg-indigo-700",
                    "hover:bg-emerald-600",
                );
            } else {
                btn.disabled = false;
                btn.textContent = origText;
                alert(data.error || "Failed to create ticket.");
            }
        } catch (_) {
            btn.disabled = false;
            btn.textContent = origText;
        }
    });

    aiDismiss?.addEventListener("click", () =>
        aiSuggestion?.classList.add("hidden"),
    );

    // ── Thread panel ─────────────────────────────────────────────────
    async function loadThread(threadUrl) {
        const res = await fetch(threadUrl, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const replies = await res.json();
        threadMessages.innerHTML = "";
        replies.forEach((r) => {
            const div = document.createElement("div");
            div.className = "flex gap-2 py-1";
            div.innerHTML = `
                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-[9px] font-semibold text-white flex-shrink-0 mt-0.5">${r.user.name[0].toUpperCase()}</div>
                <div class="flex-1 min-w-0">
                    <span class="font-semibold text-xs text-gray-900 dark:text-white">${r.user.name}</span>
                    <span class="text-xs text-gray-400 ml-1">${new Date(r.created_at).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</span>
                    <div class="prose prose-xs dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 mt-0.5">${jsonToHtml(r.body, renderExtensions)}</div>
                </div>`;
            threadMessages.appendChild(div);
        });
    }

    document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".thread-open-btn");
        if (!btn) return;

        const parentId = btn.dataset.messageId;
        const threadUrl = btn.dataset.threadUrl;
        threadSend.dataset.parentId = parentId;
        threadSend.dataset.threadUrl = threadUrl;

        await loadThread(threadUrl);
        threadPanel?.classList.remove("hidden");
    });

    threadClose?.addEventListener("click", () =>
        threadPanel?.classList.add("hidden"),
    );

    threadSend?.addEventListener("click", async () => {
        const parentId = threadSend.dataset.parentId;
        const threadUrl =
            threadSend.dataset.threadUrl || `${sendUrl}/${parentId}/thread`;
        await sendMessage(threadEditorInstance, parentId);
        await loadThread(threadUrl);
        // Update the reply count on the parent message in the main list
        const replies = document.querySelectorAll("#thread-messages > div");
        if (replies.length) setThreadCount(parentId, replies.length);
    });

    // ── Huddle ────────────────────────────────────────────────────────
    let localStream = null;
    let peerConnection = null;
    let huddleRoomId = null;
    let huddleId = null; // DB id of the HuddleSession record
    let huddlePeerId = null; // user_id of the remote peer
    let muted = false;

    huddleBtn?.addEventListener("click", async () => {
        const res = await fetch(huddleStartUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
            },
        });
        const data = await res.json();
        huddleRoomId = data.room_id;
        huddleId = data.id;
        huddlePanel?.classList.remove("hidden");
        huddleBtn?.classList.add("hidden");

        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                audio: true,
                video: false,
            });
        } catch (err) {
            if (huddleParticipants)
                huddleParticipants.innerHTML =
                    '<p class="text-red-400 text-xs">Microphone access denied.</p>';
            return;
        }

        if (huddleParticipants) {
            huddleParticipants.innerHTML = `<p class="text-xs text-emerald-400">You joined the huddle.</p>`;
        }

        if (window.Echo) {
            window.Echo.private(`huddle.user.${userId}`).listen(
                "HuddleSignal",
                async (signal) => {
                    huddlePeerId = huddlePeerId ?? signal.from_user_id;
                    if (signal.type === "offer") {
                        if (!peerConnection) await initPeer(false);
                        await peerConnection.setRemoteDescription(
                            signal.payload,
                        );
                        const answer = await peerConnection.createAnswer();
                        await peerConnection.setLocalDescription(answer);
                        await sendSignal({
                            room_id: huddleRoomId,
                            type: "answer",
                            payload: answer,
                            to_user_id: signal.from_user_id,
                        });
                    } else if (signal.type === "answer") {
                        await peerConnection.setRemoteDescription(
                            signal.payload,
                        );
                    } else if (signal.type === "ice-candidate") {
                        await peerConnection.addIceCandidate(signal.payload);
                    } else if (signal.type === "hangup") {
                        cleanup();
                    }
                },
            );
        }
    });

    async function initPeer(isInitiator) {
        peerConnection = new RTCPeerConnection({
            iceServers: [{ urls: "stun:stun.l.google.com:19302" }],
        });
        localStream
            ?.getTracks()
            .forEach((t) => peerConnection.addTrack(t, localStream));
        peerConnection.onicecandidate = (e) => {
            if (e.candidate && huddlePeerId)
                sendSignal({
                    room_id: huddleRoomId,
                    type: "ice-candidate",
                    payload: e.candidate,
                    to_user_id: huddlePeerId,
                });
        };
        peerConnection.ontrack = (e) => {
            const audio = new Audio();
            audio.srcObject = e.streams[0];
            audio.play().catch(() => {});
        };
        if (isInitiator && huddlePeerId) {
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            await sendSignal({
                room_id: huddleRoomId,
                type: "offer",
                payload: offer,
                to_user_id: huddlePeerId,
            });
        }
    }

    async function sendSignal(payload) {
        await fetch(huddleSignalUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
            },
            body: JSON.stringify(payload),
        });
    }

    function cleanup() {
        peerConnection?.close();
        peerConnection = null;
        localStream?.getTracks().forEach((t) => t.stop());
        localStream = null;
        huddlePanel?.classList.add("hidden");
        huddleBtn?.classList.remove("hidden");
    }

    huddleEnd?.addEventListener("click", async () => {
        // POST hangup signal to peer before leaving
        if (huddlePeerId) {
            await sendSignal({
                room_id: huddleRoomId,
                type: "hangup",
                payload: null,
                to_user_id: huddlePeerId,
            });
        }
        const leaveUrl = huddleStartUrl.replace(
            "/huddle/start",
            `/huddle/${huddleId}/leave`,
        );
        await fetch(leaveUrl, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
            },
        });
        cleanup();
    });

    huddleMute?.addEventListener("click", () => {
        muted = !muted;
        localStream?.getAudioTracks().forEach((t) => {
            t.enabled = !muted;
        });
        huddleMute.querySelector("span") &&
            (huddleMute.querySelector("span").textContent = muted
                ? "Unmute"
                : "Mute");
    });
}
