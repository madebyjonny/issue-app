import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Placeholder from "@tiptap/extension-placeholder";

// Self-initialize when the page contains a DM root element
document.addEventListener("DOMContentLoaded", () => {
    const root = document.getElementById("dm-root");
    if (root) dmApp(root);
});

function renderMessageBody(el) {
    try {
        const json = JSON.parse(el.dataset.json || "null");
        if (!json) return;
        const tmp = document.createElement("div");
        const view = new Editor({
            element: tmp,
            content: json,
            editable: false,
            extensions: [StarterKit],
        });
        el.innerHTML = view.getHTML();
        view.destroy();
    } catch (_) {}
}

function dmApp(root) {
    if (!root) return;

    const conversationId = root.dataset.conversationId;
    const userId = parseInt(root.dataset.userId, 10);
    const sendUrl = root.dataset.sendUrl;

    const messagesList = document.getElementById("dm-messages-list");
    const sendBtn = document.getElementById("dm-send");

    // Render existing message bodies
    document.querySelectorAll(".message-body").forEach(renderMessageBody);

    // Tiptap editor
    const editor = new Editor({
        element: document.getElementById("dm-editor"),
        extensions: [
            StarterKit,
            Placeholder.configure({ placeholder: "Message..." }),
        ],
    });

    async function sendMessage() {
        if (editor.isEmpty) return;
        const body = editor.getJSON();
        const res = await fetch(sendUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
            },
            body: JSON.stringify({ body }),
        });
        if (res.ok) {
            editor.commands.clearContent();
        }
    }

    sendBtn?.addEventListener("click", sendMessage);
    document.getElementById("dm-editor")?.addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Listen for incoming DMs via Echo
    if (window.Echo) {
        window.Echo.private(`dm.${userId}`).listen(
            "DirectMessageSent",
            (msg) => {
                if (msg.conversation_id != conversationId) return;
                const isMine = msg.user.id === userId;
                const div = document.createElement("div");
                div.className = `flex gap-3 px-2 py-1 rounded-lg hover:bg-gray-50 dark:hover:bg-white/[0.03] transition ${isMine ? "flex-row-reverse" : ""}`;
                div.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-[11px] font-semibold text-white flex-shrink-0">
                    ${msg.user.name[0].toUpperCase()}
                </div>
                <div class="max-w-[70%]">
                    <div class="flex items-baseline gap-2 mb-0.5 ${isMine ? "flex-row-reverse" : ""}">
                        <span class="font-semibold text-sm text-gray-900 dark:text-white">${msg.user.name}</span>
                        <span class="text-xs text-gray-400">${new Date(msg.created_at).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</span>
                    </div>
                    <div class="message-body-raw prose prose-sm dark:prose-invert max-w-none ${isMine ? "bg-indigo-600 text-white rounded-2xl rounded-tr-sm px-3 py-2" : "text-gray-700 dark:text-gray-300"}"></div>
                </div>`;

                const bodyDiv = div.querySelector(".message-body-raw");
                try {
                    const tmp = document.createElement("div");
                    const view = new Editor({
                        element: tmp,
                        content: msg.body,
                        editable: false,
                        extensions: [StarterKit],
                    });
                    bodyDiv.innerHTML = view.getHTML();
                    view.destroy();
                } catch (_) {}

                messagesList?.insertAdjacentElement("afterbegin", div);
            },
        );
    }
}
