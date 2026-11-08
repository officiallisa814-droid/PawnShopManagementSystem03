(() => {
    const root = document.getElementById("ntf");
    if (!root) return;

    const btn = document.getElementById("ntfBtn");
    const panel = document.getElementById("ntfPanel");
    const list = document.getElementById("ntfList");
    const badge = document.getElementById("ntfBadge");
    const readAllBtn = document.getElementById("ntfReadAll");
    const URL_API = root.dataset.url;
    const STORE = "ntf_read";

    let items = [];

    const esc = (s) =>
        String(s ?? "").replace(
            /[&<>"']/g,
            (c) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;",
                })[c],
        );

    /* ---------- ការអាន (រក្សាក្នុង Browser) ---------- */
    const getRead = () => {
        try {
            return JSON.parse(localStorage.getItem(STORE) || "[]");
        } catch (_) {
            return [];
        }
    };
    const setRead = (arr) => {
        try {
            localStorage.setItem(STORE, JSON.stringify(arr));
        } catch (_) {}
    };
    const markRead = (keys) => {
        const set = new Set(getRead());
        keys.forEach((k) => set.add(k));
        setRead([...set]);
    };

    /* ---------- Render ---------- */
    function render() {
        const read = new Set(getRead());
        const unread = items.filter((i) => !read.has(i.key)).length;

        badge.textContent = unread > 9 ? "9+" : unread;
        badge.hidden = unread === 0;
        btn.classList.toggle("ntf-has", unread > 0);
        readAllBtn.hidden = unread === 0;

        if (!items.length) {
            list.innerHTML = `
              <div class="ntf-empty">
                <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
                <b>មិនមានការជូនដំណឹង</b>
                <small>អ្វីៗរៀបចំរួចរាល់ហើយ ✓</small>
              </div>`;
            return;
        }

        list.innerHTML = items
            .map(
                (i) => `
              <a class="ntf-item ${read.has(i.key) ? "" : "unread"}" href="${esc(i.url)}" data-key="${esc(i.key)}">
                <span class="ntf-ic ${esc(i.level)}"><i class="fa-solid ${esc(i.icon)}" aria-hidden="true"></i></span>
                <span class="ntf-tx">
                  <b>${esc(i.title)}</b>
                  <small>${esc(i.text)}</small>
                  <em>${esc(i.meta)}</em>
                </span>
                ${read.has(i.key) ? "" : '<span class="ntf-dot" aria-label="មិនទាន់អាន"></span>'}
              </a>`,
            )
            .join("");
    }

    function showError(code) {
        console.error("Notifications failed:", code, URL_API);
        list.innerHTML = `
          <div class="ntf-empty">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <b>មិនអាចទាញការជូនដំណឹងបាន</b>
            <small>កំហុស: ${esc(code)} — ${esc(URL_API)}</small>
          </div>`;
    }

    /* ---------- ទាញទិន្នន័យ ---------- */
    async function load() {
        try {
            const res = await fetch(URL_API, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
            });
            if (!res.ok) {
                showError("HTTP " + res.status);
                return;
            }
            const data = await res.json();
            items = data.items || [];

            // សម្អាតគ្រាប់ចាស់ដែលលែងមាន
            const keys = new Set(items.map((i) => i.key));
            setRead(getRead().filter((k) => keys.has(k)));

            render();
        } catch (err) {
            console.error("Notifications:", err);
            showError("មិនអាចភ្ជាប់បាន");
        }
    }

    /* ---------- បើក / បិទ ---------- */
    function toggle(open) {
        const willOpen = typeof open === "boolean" ? open : panel.hidden;
        panel.hidden = !willOpen;
        btn.setAttribute("aria-expanded", willOpen ? "true" : "false");
        if (willOpen) load();
    }

    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        toggle();
    });
    document.addEventListener("click", (e) => {
        if (!panel.hidden && !root.contains(e.target)) toggle(false);
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !panel.hidden) {
            toggle(false);
            btn.focus();
        }
    });

    list.addEventListener("click", (e) => {
        const a = e.target.closest(".ntf-item");
        if (a) markRead([a.dataset.key]);
    });
    readAllBtn.addEventListener("click", () => {
        markRead(items.map((i) => i.key));
        render();
    });

    /* ---------- ចាប់ផ្តើម + ធ្វើបច្ចុប្បន្នភាពរាល់ ៦០ វិនាទី ---------- */
    load();
    setInterval(() => {
        if (!document.hidden) load();
    }, 60000);
    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) load();
    });
})();
