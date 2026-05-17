import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Placeholder from "@tiptap/extension-placeholder";
import Link from "@tiptap/extension-link";

document.addEventListener("DOMContentLoaded", () => {
    // ── Read-only doc body renderer ────────────────────────────────
    const bodyEl = document.getElementById("doc-body");
    if (bodyEl) {
        try {
            const json = JSON.parse(bodyEl.dataset.json || "null");
            if (json) {
                const tmp = document.createElement("div");
                const view = new Editor({
                    element: tmp,
                    content: json,
                    editable: false,
                    extensions: [StarterKit, Link],
                });
                bodyEl.innerHTML = view.getHTML();
                view.destroy();
            }
        } catch (_) {}
    }

    // ── Editor (create / edit pages) ───────────────────────────────
    const editorMount = document.getElementById("doc-editor");
    const bodyInput = document.getElementById("doc-body-input");
    const docForm = document.getElementById("doc-form");

    if (editorMount && bodyInput && docForm) {
        const initialContent =
            window.__docInitialContent ||
            (window.__docEditorMode === "edit" ? null : undefined);

        const editor = new Editor({
            element: editorMount,
            extensions: [
                StarterKit,
                Link.configure({ openOnClick: false }),
                Placeholder.configure({
                    placeholder: "Start writing your documentation…",
                }),
            ],
            content: initialContent ?? undefined,
            editorProps: {
                attributes: {
                    class: "focus:outline-none min-h-[400px]",
                },
            },
        });

        // Serialise to hidden input on submit
        docForm.addEventListener("submit", () => {
            bodyInput.value = JSON.stringify(editor.getJSON());
        });

        // ── Floating toolbar ────────────────────────────────────────
        const toolbar = document.createElement("div");
        toolbar.id = "doc-toolbar";
        toolbar.className =
            "hidden fixed z-50 flex items-center gap-0.5 bg-gray-900 dark:bg-gray-800 rounded-lg shadow-xl px-1.5 py-1 text-white";
        toolbar.innerHTML = `
            <button data-cmd="bold"    title="Bold"          class="toolbar-btn px-1.5 py-1 rounded hover:bg-white/10 text-xs font-bold">B</button>
            <button data-cmd="italic"  title="Italic"        class="toolbar-btn px-1.5 py-1 rounded hover:bg-white/10 text-xs italic">I</button>
            <button data-cmd="code"    title="Inline code"   class="toolbar-btn px-1.5 py-1 rounded hover:bg-white/10 text-xs font-mono">&lt;/&gt;</button>
            <div class="w-px h-4 bg-white/20 mx-1"></div>
            <button data-cmd="h2"      title="Heading 2"     class="toolbar-btn px-1.5 py-1 rounded hover:bg-white/10 text-xs font-semibold">H2</button>
            <button data-cmd="h3"      title="Heading 3"     class="toolbar-btn px-1.5 py-1 rounded hover:bg-white/10 text-xs font-semibold">H3</button>
            <div class="w-px h-4 bg-white/20 mx-1"></div>
            <button data-cmd="bullet"  title="Bullet list"   class="toolbar-btn px-1.5 py-1 rounded hover:bg-white/10 text-xs">• List</button>
            <button data-cmd="ordered" title="Numbered list" class="toolbar-btn px-1.5 py-1 rounded hover:bg-white/10 text-xs">1. List</button>
            <button data-cmd="block"   title="Blockquote"    class="toolbar-btn px-1.5 py-1 rounded hover:bg-white/10 text-xs">"</button>
        `;
        document.body.appendChild(toolbar);

        toolbar.querySelectorAll(".toolbar-btn").forEach((btn) => {
            btn.addEventListener("mousedown", (e) => {
                e.preventDefault();
                const cmd = btn.dataset.cmd;
                const chain = editor.chain().focus();
                if (cmd === "bold") chain.toggleBold().run();
                if (cmd === "italic") chain.toggleItalic().run();
                if (cmd === "code") chain.toggleCode().run();
                if (cmd === "h2") chain.toggleHeading({ level: 2 }).run();
                if (cmd === "h3") chain.toggleHeading({ level: 3 }).run();
                if (cmd === "bullet") chain.toggleBulletList().run();
                if (cmd === "ordered") chain.toggleOrderedList().run();
                if (cmd === "block") chain.toggleBlockquote().run();
            });
        });

        // Show/hide floating toolbar on selection
        editor.on("selectionUpdate", ({ editor: ed }) => {
            const { from, to } = ed.state.selection;
            if (from === to) {
                toolbar.classList.add("hidden");
                return;
            }
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;
            const range = sel.getRangeAt(0).getBoundingClientRect();
            toolbar.style.left = `${range.left + range.width / 2 - toolbar.offsetWidth / 2}px`;
            toolbar.style.top = `${range.top + window.scrollY - 42}px`;
            toolbar.classList.remove("hidden");
        });

        document.addEventListener("click", (e) => {
            if (!toolbar.contains(e.target)) toolbar.classList.add("hidden");
        });
    }

    // ── Sidebar search ─────────────────────────────────────────────
    const searchInput = document.getElementById("doc-search-input");
    const searchResults = document.getElementById("doc-search-results");
    const searchUrl = window.__docsSearchUrl;

    if (searchInput && searchResults && searchUrl) {
        let debounce;
        searchInput.addEventListener("input", () => {
            clearTimeout(debounce);
            const q = searchInput.value.trim();
            if (!q) {
                searchResults.classList.add("hidden");
                searchResults.innerHTML = "";
                return;
            }
            debounce = setTimeout(async () => {
                const res = await fetch(
                    `${searchUrl}?q=${encodeURIComponent(q)}`,
                    { headers: { "X-Requested-With": "XMLHttpRequest" } },
                );
                const docs = await res.json();
                if (!docs.length) {
                    searchResults.innerHTML =
                        '<p class="text-[11px] text-gray-400 px-1 py-1">No results</p>';
                } else {
                    searchResults.innerHTML = docs
                        .map(
                            (d) => `
                        <a href="${d.url}"
                           class="flex items-center gap-1.5 px-1 py-1 rounded text-[12px] text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.06] transition">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                            <span class="truncate">${d.title}</span>
                        </a>`,
                        )
                        .join("");
                }
                searchResults.classList.remove("hidden");
            }, 250);
        });
    }
});
