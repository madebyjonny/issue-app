import "./bootstrap";

import Alpine from "alpinejs";
import Peer from "peerjs";

window.Alpine = Alpine;

Alpine.start();

// ── Global Sidebar Huddle ────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
    const section = document.getElementById("huddle-section");
    if (!section) return; // not in a project context

    const startUrl = section.dataset.huddleStart;
    const leaveBase = section.dataset.huddleLeaveBase;
    const userId = parseInt(section.dataset.userId, 10);
    const projectId = parseInt(section.dataset.projectId, 10);

    const startBtn = document.getElementById("huddle-start-btn");
    const activeCard = document.getElementById("huddle-active-card");
    const participantsList = document.getElementById(
        "huddle-participants-list",
    );
    const joinBtn = document.getElementById("huddle-join-btn");
    const leaveBtn = document.getElementById("huddle-leave-btn");
    const muteBtn = document.getElementById("huddle-mute-btn");
    const csrf = () =>
        document.querySelector('meta[name="csrf-token"]')?.content;

    let huddleId = null;
    let peerConn = null; // PeerJS Peer instance
    let calls = {}; // remotePeerId -> PeerJS Call
    let localStream = null;
    let muted = false;
    let inHuddle = false;

    // ── UI state helpers ──────────────────────────────────────────
    function renderParticipants(data) {
        if (!participantsList) return;
        participantsList.innerHTML = data
            .map(
                (p) =>
                    `<div class="flex items-center gap-1.5 text-[11px] text-emerald-800 dark:text-emerald-300">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                ${p.name}${p.id === userId ? ' <span class="text-emerald-500 dark:text-emerald-500">(you)</span>' : ""}
            </div>`,
            )
            .join("");
    }

    function showActiveCard(participants) {
        startBtn?.classList.add("hidden");
        activeCard?.classList.remove("hidden");
        renderParticipants(participants);
    }

    function showIdleState() {
        activeCard?.classList.add("hidden");
        startBtn?.classList.remove("hidden");
    }

    function showParticipatingState() {
        joinBtn?.classList.add("hidden");
        leaveBtn?.classList.remove("hidden");
        muteBtn?.classList.remove("hidden");
    }

    function showJoinState() {
        joinBtn?.classList.remove("hidden");
        leaveBtn?.classList.add("hidden");
        muteBtn?.classList.add("hidden");
    }

    // ── Init from server-rendered state ──────────────────────────
    const initHuddleId = section.dataset.activeHuddleId;
    const initParticipants = JSON.parse(
        section.dataset.activeParticipants || "[]",
    );
    if (initHuddleId) {
        showActiveCard(initParticipants);
        showJoinState(); // not yet participating on fresh page load
    }

    // ── Live Echo updates ─────────────────────────────────────────
    if (window.Echo && projectId) {
        window.Echo.private(`project.${projectId}`).listen(
            "HuddleUpdated",
            (e) => {
                if (e.is_active) {
                    showActiveCard(e.participants);
                    if (!inHuddle) showJoinState();
                } else {
                    if (!inHuddle) showIdleState();
                }
            },
        );
    }

    // ── Join / Start huddle ───────────────────────────────────────
    async function joinHuddle() {
        const res = await fetch(startUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf(),
            },
        });
        const data = await res.json();
        huddleId = data.id;
        inHuddle = true;

        showActiveCard(data.participants_data || [{ id: userId, name: "You" }]);
        showParticipatingState();

        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                audio: true,
                video: false,
            });
        } catch {
            participantsList?.insertAdjacentHTML(
                "beforeend",
                '<p class="text-red-500 text-[10px] mt-1">Microphone access denied.</p>',
            );
            return;
        }

        // Deterministic PeerJS ID for this user in this huddle session
        const myPeerId = `huddle${huddleId}u${userId}`;
        peerConn = new Peer(myPeerId, {
            config: { iceServers: [{ urls: "stun:stun.l.google.com:19302" }] },
        });

        peerConn.on("error", (err) => console.error("PeerJS:", err));

        // Answer calls from participants who joined before us
        peerConn.on("call", (call) => {
            call.answer(localStream);
            call.on("stream", (stream) => attachAudio(call.peer, stream));
            calls[call.peer] = call;
        });

        // Once registered with PeerJS server, call everyone already in the huddle
        peerConn.on("open", () => {
            const others = (data.participants_data || []).filter(
                (p) => p.id !== userId,
            );
            for (const p of others) {
                const remotePeerId = `huddle${huddleId}u${p.id}`;
                const call = peerConn.call(remotePeerId, localStream);
                call.on("stream", (stream) => attachAudio(call.peer, stream));
                calls[call.peer] = call;
            }
        });
    }

    function attachAudio(peerId, stream) {
        document.getElementById(`ra-${peerId}`)?.remove();
        const audio = document.createElement("audio");
        audio.id = `ra-${peerId}`;
        audio.autoplay = true;
        audio.srcObject = stream;
        document.body.appendChild(audio);
    }

    startBtn?.addEventListener("click", joinHuddle);
    joinBtn?.addEventListener("click", joinHuddle);

    // ── Leave huddle ──────────────────────────────────────────────
    leaveBtn?.addEventListener("click", async () => {
        Object.values(calls).forEach((c) => c.close());
        calls = {};
        peerConn?.destroy();
        peerConn = null;
        localStream?.getTracks().forEach((t) => t.stop());
        localStream = null;
        document.querySelectorAll('[id^="ra-"]').forEach((el) => el.remove());

        let remaining = null;
        if (huddleId) {
            const res = await fetch(`${leaveBase}/${huddleId}/leave`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf(),
                },
            });
            remaining = await res.json();
        }

        muted = false;
        inHuddle = false;
        huddleId = null;
        if (muteBtn) muteBtn.querySelector("span").textContent = "Mute";

        if (remaining?.is_active) {
            showActiveCard(remaining.participants_data);
            showJoinState();
        } else {
            showIdleState();
        }
    });

    // ── Mute ─────────────────────────────────────────────────────
    muteBtn?.addEventListener("click", () => {
        muted = !muted;
        localStream?.getAudioTracks().forEach((t) => {
            t.enabled = !muted;
        });
        const span = muteBtn.querySelector("span");
        if (span) span.textContent = muted ? "Unmute" : "Mute";
    });
});
