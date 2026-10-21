(() => {
    const root = document.getElementById("as-app");
    if (!root) return;
    const { csrf, base, contract: CONTRACT_URL } = root.dataset;
    let data = { expired: [], scheduled: [], history: [] };
    let onOk = null;

    const $ = (s, p = document) => p.querySelector(s);
    const money = (n) =>
        "$" +
        Number(n || 0).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    const fmt = (d) =>
        d ? d.slice(8, 10) + "/" + d.slice(5, 7) + "/" + d.slice(0, 4) : "—";
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
    const val = (n) => $(`#as-modal-body [name="${n}"]`)?.value;

    async function api(url, method = "GET", body) {
        const res = await fetch(url, {
            method,
            credentials: "same-origin",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
            },
            body: body ? JSON.stringify(body) : undefined,
        });
        const j = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = j.errors
                ? Object.values(j.errors)[0][0]
                : j.message || "Request failed";
            throw new Error(msg);
        }
        return j;
    }

    function toast(msg, error = false) {
        const t = $("#as-toast");
        t.textContent = msg;
        t.className = "as-toast" + (error ? " error" : "");
        t.hidden = false;
        clearTimeout(toast.t);
        toast.t = setTimeout(() => (t.hidden = true), 3500);
    }

    async function load() {
        try {
            data = await api(`${base}/data`);
            renderStats(data.stats);
            renderExpired();
            renderScheduled();
            renderHistory();
        } catch (e) {
            toast(e.message, true);
        }
    }

    function renderStats(s) {
        $("#as-stats").innerHTML = `
          <div class="stat red"><b>${s.expired_count}</b><span>Expired (not scheduled)</span></div>
          <div class="stat amber"><b>${s.scheduled_count}</b><span>Scheduled auctions</span></div>
          <div class="stat"><b>${s.sold_count}</b><span>Items sold</span></div>
          <div class="stat green"><b>${money(s.revenue)}</b><span>Total sales</span></div>
          <div class="stat green"><b>${money(s.surplus)}</b><span>Surplus (shop income)</span></div>`;
    }

    function renderExpired() {
        const b = $("#as-expired");
        if (!data.expired.length) {
            b.innerHTML =
                '<tr><td colspan="7" class="empty">No expired items.</td></tr>';
            return;
        }
        b.innerHTML = data.expired
            .map(
                (c) => `<tr>
      <td data-label="Contract"><a href="${CONTRACT_URL}/${c.id}"><strong>${esc(c.contract_no)}</strong></a></td>
      <td data-label="Customer">${esc(c.customer_name)}<small class="muted">${esc(c.phone_number)}</small></td>
      <td data-label="Item">${esc(c.item_name)}</td>
      <td data-label="Loan" class="num">${money(c.loan_amount)}</td>
      <td data-label="Owed now" class="num">${money(c.owed)}</td>
      <td data-label="Overdue"><span class="late">${c.days_overdue} day(s)</span><small class="muted">${fmt(c.due_date)}</small></td>
      <td data-label="Action"><button class="btn" data-schedule="${c.id}">Schedule</button></td></tr>`,
            )
            .join("");
    }

    function renderScheduled() {
        const b = $("#as-scheduled");
        if (!data.scheduled.length) {
            b.innerHTML =
                '<tr><td colspan="6" class="empty">No scheduled auctions.</td></tr>';
            return;
        }
        b.innerHTML = data.scheduled
            .map(
                (a) => `<tr>
      <td data-label="Date">${fmt(a.scheduled_date)}</td>
      <td data-label="Contract"><strong>${esc(a.contract_no)}</strong></td>
      <td data-label="Customer">${esc(a.customer_name)}</td>
      <td data-label="Item">${esc(a.item_name)}</td>
      <td data-label="Starting price" class="num">${money(a.starting_price)}</td>
      <td data-label="Action"><button class="btn" data-result="${a.id}">Record result</button>
          <button class="btn ghost" data-cancel="${a.id}">Cancel</button></td></tr>`,
            )
            .join("");
    }

    function renderHistory() {
        const b = $("#as-history");
        if (!data.history.length) {
            b.innerHTML =
                '<tr><td colspan="8" class="empty">No sales history yet.</td></tr>';
            return;
        }
        b.innerHTML = data.history
            .map(
                (a) => `<tr>
      <td data-label="Date">${fmt(a.sold_date || a.scheduled_date)}</td>
      <td data-label="Contract"><strong>${esc(a.contract_no)}</strong></td>
      <td data-label="Item">${esc(a.item_name)}</td>
      <td data-label="Buyer">${esc(a.buyer_name) || "—"}<small class="muted">${esc(a.buyer_phone)}</small></td>
      <td data-label="Owed" class="num">${a.status === "sold" ? money(a.owed) : "—"}</td>
      <td data-label="Sale price" class="num">${a.status === "sold" ? money(a.sale_price) : "—"}</td>
      <td data-label="Result" class="num ${a.result >= 0 ? "pos" : "neg"}">${a.result === null ? "—" : (a.result >= 0 ? "+" : "") + money(a.result)}</td>
      <td data-label="Status"><span class="badge ${a.status}">${a.status}</span></td></tr>`,
            )
            .join("");
    }

    /* ---------- Modal ---------- */
    function openModal(title, html, okText, handler) {
        $("#as-modal-title").textContent = title;
        $("#as-modal-body").innerHTML = html;
        $("#as-ok").textContent = okText;
        onOk = handler;
        $("#as-modal").hidden = false;
    }
    const closeModal = () => {
        $("#as-modal").hidden = true;
        onOk = null;
    };
    $("#as-x").onclick = $("#as-cancel").onclick = closeModal;
    $("#as-modal").addEventListener("mousedown", (e) => {
        if (e.target.id === "as-modal") closeModal();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeModal();
    });
    $("#as-ok").onclick = async function () {
        if (!onOk) return;
        this.disabled = true;
        try {
            await onOk();
        } catch (e) {
            toast(e.message, true);
        } finally {
            this.disabled = false;
        }
    };

    function openSchedule(id) {
        const c = data.expired.find((x) => x.id == id);
        if (!c) return;
        const tomorrow = new Date(Date.now() + 864e5)
            .toISOString()
            .slice(0, 10);
        openModal(
            "Schedule auction",
            `
          <div class="sum">
            <div><span>${esc(c.contract_no)}</span><strong>${esc(c.customer_name)}</strong></div>
            <div><span>Item</span><span>${esc(c.item_name)}</span></div>
            <div><span>Owed now</span><strong>${money(c.owed)}</strong></div>
          </div>
          <div class="f"><label>Auction date</label><input name="scheduled_date" type="date" value="${tomorrow}"></div>
          <div class="f"><label>Sale Price</label>
            <input name="starting_price" type="number" step="0.01" min="0" value="${c.owed}"></div>`,
            "Schedule",
            async () => {
                const r = await api(`${base}/schedule`, "POST", {
                    loan_contract_id: id,
                    scheduled_date: val("scheduled_date"),
                    starting_price: val("starting_price"),
                });
                closeModal();
                toast(r.message);
                load();
            },
        );
    }

    function openResult(id) {
        const a = data.scheduled.find((x) => x.id == id);
        if (!a) return;
        openModal(
            "Record auction result",
            `
          <div class="sum">
            <div><span>${esc(a.contract_no)}</span><strong>${esc(a.customer_name)}</strong></div>
            <div><span>Item</span><span>${esc(a.item_name)}</span></div>
            <div><span>Starting price</span><strong>${money(a.starting_price)}</strong></div>
          </div>
          <div class="f"><label>Outcome</label>
            <select name="outcome"><option value="sold">Sold / លក់បាន</option><option value="unsold">Unsold / មិនលក់បាន</option></select></div>
          <div id="sold-fields">
            <div class="f"><label>Buyer name</label><input name="buyer_name"></div>
            <div class="f"><label>Buyer phone</label><input name="buyer_phone"></div>
            <div class="f"><label>Sale price ($)</label><input name="sale_price" type="number" step="0.01" min="0" value="${a.starting_price}"></div>
          </div>
          <div class="f"><label>Note (optional)</label><input name="note" maxlength="255"></div>
          <p class="hint">បើតម្លៃលក់ខ្ពស់ជាងប្រាក់ជំពាក់ ចំនួនលើសត្រូវកត់ជាចំណូលហាង។</p>`,
            "Save result",
            async () => {
                const r = await api(`${base}/${id}/result`, "POST", {
                    outcome: val("outcome"),
                    buyer_name: val("buyer_name"),
                    buyer_phone: val("buyer_phone"),
                    sale_price: val("sale_price"),
                    note: val("note"),
                });
                closeModal();
                toast(r.message);
                load();
            },
        );
        const sel = $('#as-modal-body [name="outcome"]');
        sel.onchange = () =>
            ($("#sold-fields").style.display =
                sel.value === "sold" ? "" : "none");
    }

    /* ---------- Events ---------- */
    $("#as-expired").addEventListener("click", (e) => {
        const b = e.target.closest("[data-schedule]");
        if (b) openSchedule(b.dataset.schedule);
    });
    $("#as-scheduled").addEventListener("click", async (e) => {
        const r = e.target.closest("[data-result]");
        if (r) return openResult(r.dataset.result);
        const c = e.target.closest("[data-cancel]");
        if (c && confirm("Cancel this auction?")) {
            try {
                const j = await api(
                    `${base}/${c.dataset.cancel}/cancel`,
                    "POST",
                );
                toast(j.message);
                load();
            } catch (err) {
                toast(err.message, true);
            }
        }
    });
    document.querySelectorAll(".as-tabs .tab").forEach(
        (b) =>
            (b.onclick = () => {
                document
                    .querySelectorAll(".as-tabs .tab")
                    .forEach((x) => x.classList.toggle("active", x === b));
                ["expired", "scheduled", "history"].forEach(
                    (t) => ($("#pane-" + t).hidden = b.dataset.tab !== t),
                );
            }),
    );

    load();
})();

document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("blacklistBtn");
    const modal = document.getElementById("blacklistModal");
    const close = document.getElementById("blacklistClose");
    if (!btn || !modal) return;

    const open = () => modal.classList.add("show");
    const hide = () => modal.classList.remove("show");

    btn.addEventListener("click", open);
    close.addEventListener("click", hide);
    modal.addEventListener("mousedown", (e) => {
        if (e.target === modal) hide();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") hide();
    });
});
