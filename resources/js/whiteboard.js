import React, { useState, useEffect, useRef } from "react";
import { createRoot } from "react-dom/client";
import { Excalidraw } from "@excalidraw/excalidraw";
import "@excalidraw/excalidraw/index.css";

function WhiteboardApp({ initialElements, syncUrl, docId, userId, theme }) {
    const [excalidrawAPI, setExcalidrawAPI] = useState(null);
    const syncTimerRef = useRef(null);
    const isReceivingRef = useRef(false);

    // WebSocket: receive updates from other collaborators
    useEffect(() => {
        if (!window.Echo || !docId || !excalidrawAPI) return;

        const ch = window.Echo.private(`whiteboard.${docId}`);

        ch.listen("WhiteboardUpdated", ({ elements, sender_id }) => {
            if (sender_id === userId) return;
            if (!excalidrawAPI) return;
            isReceivingRef.current = true;
            // Deserialize from JSON — spread each element to unfreeze
            const liveElements = (elements || []).map((el) => ({ ...el }));
            excalidrawAPI.updateScene({ elements: liveElements });
            setTimeout(() => {
                isReceivingRef.current = false;
            }, 150);
        });

        // Show live badge when connected
        const badge = document.getElementById("wb-live-badge");
        if (badge) badge.classList.remove("hidden");

        return () => {
            window.Echo.leave(`whiteboard.${docId}`);
            if (badge) badge.classList.add("hidden");
        };
    }, [docId, userId, excalidrawAPI]);

    // Debounced sync: send local changes to server (broadcasts to others)
    const handleChange = (elements) => {
        if (isReceivingRef.current) return;
        if (!elements || elements.length === 0) return;
        clearTimeout(syncTimerRef.current);
        syncTimerRef.current = setTimeout(async () => {
            try {
                const csrf = document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content;
                // JSON.parse(JSON.stringify()) gives a clean serialisable copy
                const payload = JSON.parse(JSON.stringify([...elements]));
                await fetch(syncUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrf ?? "",
                    },
                    body: JSON.stringify({ elements: payload }),
                });
            } catch (_) {
                // Silently ignore sync errors — will retry on next change
            }
        }, 150);
    };

    return React.createElement(Excalidraw, {
        excalidrawAPI: setExcalidrawAPI,
        initialData: {
            elements: initialElements,
            scrollToContent: initialElements.length > 0,
        },
        onChange: handleChange,
        theme: theme === "dark" ? "dark" : "light",
        UIOptions: {
            canvasActions: {
                saveToActiveFile: false,
                loadScene: false,
            },
        },
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("whiteboard-container");
    if (!container) return;

    const props = {
        initialElements: JSON.parse(container.dataset.elements || "[]"),
        syncUrl: container.dataset.syncUrl,
        docId: container.dataset.docId,
        userId: parseInt(container.dataset.userId, 10),
        theme: container.dataset.theme || "light",
    };

    createRoot(container).render(React.createElement(WhiteboardApp, props));
});
