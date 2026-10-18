(() => {
    const root = document.getElementById("pm-app");
    if (!root) return;
    const { data: DATA_URL, contract: CONTRACT_URL } = root.dataset;

    let rows = [],
        timer;

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
    const TYPE = {
        renew: "Renew",
        extend: "Extend",
        redeem: "Close",
        auction: "Auction",
    };

    function toast(msg, error = false) {
        const t = $("#pm-toast");
        if (!t) return;
        t.textContent = msg;
        t.className = "pm-toast" + (error ? " error" : "");
        t.hidden = false;
        clearTimeout(toast.t);
        toast.t = setTimeout(() => (t.hidden = true), 3500);
    }

    async function load() {
        const qs = new URLSearchParams({
            search: $("#pm-search").value,
            status: $("#pm-status").value,
        });
        try {
            const res = await fetch(`${DATA_URL}?${qs}`, {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            });
            if (!res.ok) throw new Error("Server error " + res.status);
            const j = await res.json();
            rows = j.contracts || [];
            renderStats(j.stats);
            renderSchedules();
            renderContracts(j.transactions || []);
        } catch (e) {
            toast(e.message, true);
        }
    }

    function renderStats(s) {
        $("#pm-stats").innerHTML = `
      <div class="stat green"><b>${money(s.collected_today)}</b><span>Collected today</span></div>
      <div class="stat green"><b>${money(s.collected_month)}</b><span>Collected this month</span></div>
      <div class="stat red"><b>${money(s.overdue_amount)}</b><span>Overdue interest · ${s.overdue_count} installment(s)</span></div>
      <div class="stat amber"><b>${money(s.soon_amount)}</b><span>Due in 7 days · ${s.soon_count} installment(s)</span></div>
      <a class="stat brass" href="${CONTRACT_URL}"><b>${s.expired_contracts}</b><span>Expired loan contracts → open</span></a>`;
    }

    function rowBadge(r) {
        if (r.contract_status === "redeemed")
            return '<span class="badge paid">Redeemed</span>';
        if (r.contract_status === "auctioned")
            return '<span class="badge overdue">Auctioned</span>';
        if (!r.total_count)
            return '<span class="badge unpaid">No schedule</span>';
        if (!r.next_due) return '<span class="badge paid">All paid</span>';
        if (r.is_overdue)
            return `<span class="badge overdue">Overdue</span><span class="late-days">${r.days_overdue} day(s) late</span>`;
        return '<span class="badge unpaid">Unpaid</span>';
    }

    function renderSchedules() {
        const body = $("#pm-sched");
        if (!rows.length) {
            body.innerHTML =
                '<tr><td colspan="6" class="empty">No customers found.</td></tr>';
            return;
        }
        body.innerHTML = rows
            .map(
                (
                    r,
                ) => `<tr class="row-link" data-href="${CONTRACT_URL}/${r.id}" style="cursor:pointer;">
      <td><a href="${CONTRACT_URL}/${r.id}">${esc(r.customer_name)}</a><small class="muted">${esc(r.phone_number)}</small></td>
      <td>${esc(r.item_name)}<small class="muted">${esc(r.contract_no)}</small></td>
      <td>${fmt(r.next_due)}</td>
      <td class="num">${r.next_due ? money(r.next_amount) : "—"}</td>
      <td>${r.paid_count}/${r.total_count}</td>
      <td>${rowBadge(r)}</td></tr>`,
            )
            .join("");
    }

    function renderContracts(list) {
        const body = $("#pm-contract");
        if (!list.length) {
            body.innerHTML =
                '<tr><td colspan="7" class="empty">No loan contract payments yet. Renew, extend or close a contract from the Loan Contract page.</td></tr>';
            return;
        }
        body.innerHTML = list
            .map(
                (t) => `<tr>
      <td>${fmt(t.date)}</td>
      <td>${esc(t.contract_no)}</td>
      <td>${esc(t.customer_name)}</td>
      <td>${esc(t.item_name)}</td>
      <td><span class="badge ${t.type}">${TYPE[t.type] || esc(t.type)}</span></td>
      <td class="num">${money(t.amount)}</td>
      <td>${esc(t.note)}</td></tr>`,
            )
            .join("");
    }

    /* ---------- Events ---------- */
    // ចុចជួរ → ទៅទំព័រលម្អិត
    $("#pm-sched").addEventListener("click", (e) => {
        const tr = e.target.closest("tr[data-href]");
        if (tr) window.location = tr.dataset.href;
    });

    document.querySelectorAll(".pm-tabs .tab").forEach(
        (b) =>
            (b.onclick = () => {
                const tab = b.dataset.tab;
                document
                    .querySelectorAll(".pm-tabs .tab")
                    .forEach((x) => x.classList.toggle("active", x === b));
                $("#pane-schedules").hidden = tab !== "schedules";
                $("#pane-contracts").hidden = tab !== "contracts";
                $("#pm-status").style.display =
                    tab === "schedules" ? "" : "none";
            }),
    );
    $("#pm-status").addEventListener("change", load);
    $("#pm-search").addEventListener("input", () => {
        clearTimeout(timer);
        timer = setTimeout(load, 300);
    });

    // ?search=... ពីទំព័រផ្សេង
    const pre = new URLSearchParams(location.search).get("search");
    if (pre) $("#pm-search").value = pre;

    load();
})();
