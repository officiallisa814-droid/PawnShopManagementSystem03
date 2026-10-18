(() => {
    const root = document.getElementById("lc-app");
    if (!root) return;
    const csrf = root.dataset.csrf,
        base = root.dataset.base;
    let customers = JSON.parse(root.dataset.customers || "[]");
    let contracts = [];

    const $ = (s, p = document) => p.querySelector(s);
    const money = (n) =>
        "$" +
        Number(n || 0).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    const fmtDate = (d) => {
        const x = new Date(d);
        return (
            String(x.getDate()).padStart(2, "0") +
            "/" +
            String(x.getMonth() + 1).padStart(2, "0") +
            "/" +
            x.getFullYear()
        );
    };
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
    const LABEL = {
        active: "Active",
        renewed: "Renewed",
        redeemed: "Redeemed",
        expired: "Expired",
        auctioned: "Auctioned",
    };

    async function api(url, method = "GET", body) {
        const res = await fetch(url, {
            method,
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                "X-Requested-With": "XMLHttpRequest",
            },
            body: body ? JSON.stringify(body) : undefined,
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            const e = new Error(json.message || "Request failed");
            e.errors = json.errors;
            throw e;
        }
        return json;
    }

    function toast(msg, error = false) {
        const t = $("#lc-toast");
        t.textContent = msg;
        t.className = "lc-toast" + (error ? " error" : "");
        t.hidden = false;
        clearTimeout(toast.t);
        toast.t = setTimeout(() => (t.hidden = true), 3500);
    }

    /* ---------- GET data ---------- */
    async function load() {
        const qs = new URLSearchParams({
            search: $("#lc-search").value,
            status: $("#lc-status").value,
        });
        try {
            const { stats, contracts: list } = await api(`${base}/data?${qs}`);
            contracts = list;
            renderStats(stats);
            renderTable();
        } catch (e) {
            toast(e.message, true);
        }
    }

    function renderStats(s) {
        $("#lc-stats").innerHTML = `
      <div class="stat"><b>${s.open_count}</b><span>Open contracts</span></div>
      <div class="stat brass"><b>${money(s.principal_out)}</b><span>Principal lent out</span></div>
      <div class="stat brass"><b>${money(s.interest_accrued)}</b><span>Interest accrued</span></div>
      <div class="stat"><b>${s.due_7_days}</b><span>Due within 7 days</span></div>
      <div class="stat warn"><b>${s.expired_count}</b><span>Expired</span></div>
      <div class="stat"><b>${s.redeemed_count}</b><span>Redeemed</span></div>
      <div class="stat bad"><b>${s.auctioned_count}</b><span>Auctioned</span></div>`;
    }

    function renderTable() {
        const body = $("#lc-body");
        if (!contracts.length) {
            body.innerHTML =
                '<tr><td colspan="11" class="empty">No contracts found. Use Actions → Create Pawn Contract.</td></tr>';
            return;
        }
        body.innerHTML = contracts
            .map((c) => {
                let opts = '<option value="">Actions</option>';
                if (c.is_open)
                    opts +=
                        '<option value="renew">Renew Contract</option><option value="extend">Extend Loan Period</option><option value="redeem">Close Contract</option>';
                if (c.status === "expired")
                    opts += '<option value="auction">Auction item</option>';
                opts += '<option value="print">Print Pawn Agreement</option>';
                return `<tr>
        <td><strong>${esc(c.contract_no)}</strong></td>
        <td>${esc(c.customer?.customer_name)}<br><small>${esc(c.customer?.phone_number)}</small></td>
        <td>${esc(c.item_name)}</td>
        <td class="num">${money(c.loan_amount)}</td>
        <td class="num">${c.interest_rate}% / mo</td>
        <td>${fmtDate(c.loan_date)}</td>
        <td>${fmtDate(c.due_date)}${c.days_overdue ? `<span class="overdue">${c.days_overdue} days overdue</span>` : ""}</td>
        <td class="num">${c.is_open ? money(c.accrued_interest) : "—"}</td>
        <td class="num">${c.is_open ? money(c.total_due) : "—"}</td>
        <td><span class="badge ${c.status}">${LABEL[c.status]}</span></td>
        <td><select class="lc-select row-act" data-id="${c.id}">${opts}</select></td></tr>`;
            })
            .join("");
    }

    /* ---------- Modal helper ---------- */
    let onOk = null;
    function openModal(title, html, okText, handler) {
        $("#lc-modal-title").textContent = title;
        $("#lc-modal-body").innerHTML = html;
        $("#lc-modal-ok").textContent = okText;
        onOk = handler;
        $("#lc-modal").hidden = false;
        const first = $("#lc-modal-body input, #lc-modal-body select");
        first && first.focus();
    }
    const closeModal = () => {
        $("#lc-modal").hidden = true;
        onOk = null;
    };
    $("#lc-modal-x").onclick = $("#lc-modal-cancel").onclick = closeModal;
    $("#lc-modal").addEventListener("mousedown", (e) => {
        if (e.target.id === "lc-modal") closeModal();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !$("#lc-modal").hidden) closeModal();
    });
    $("#lc-modal-ok").onclick = async function () {
        if (!onOk) return;
        this.disabled = true;
        try {
            await onOk();
        } catch (e) {
            $("#lc-modal-body")
                .querySelectorAll(".err")
                .forEach((n) => n.remove());
            if (e.errors)
                Object.entries(e.errors).forEach(([k, v]) => {
                    const el = $(`#lc-modal-body [name="${k}"]`);
                    if (el)
                        el.insertAdjacentHTML(
                            "afterend",
                            `<span class="err">${esc(v[0])}</span>`,
                        );
                });
            toast(e.message, true);
        } finally {
            this.disabled = false;
        }
    };

    const val = (n) => $(`#lc-modal-body [name="${n}"]`)?.value;

    /* ---------- Create Pawn Contract ---------- */
    function openCreate(preCustomerId, prePawnItemId) {
        const today = new Date().toISOString().slice(0, 10);

        openModal(
            "Create Pawn Contract",
            `
      <div class="f" style="position:relative;display:flex;flex-direction:column;width:100%;">
        <label>Customer (ស្វែងរកតាមឈ្មោះ ឬលេខទូរស័ព្ទ)</label>
        <div style="position:relative;width:100%;display:flex;align-items:center;margin-top:4px;">
          <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;pointer-events:none;z-index:10;"></i>
          <input type="text" id="cust_search_input" autocomplete="off" placeholder="វាយឈ្មោះ ឬលេខទូរស័ព្ទ..." style="width:100%;padding:10px 12px 10px 38px !important;border:1px solid #cbd5e1;border-radius:6px;outline:none;box-sizing:border-box;display:block;">
        </div>
        <input type="hidden" name="customer_id" id="cust_hidden_id">
        <input type="hidden" name="pawn_item_id" id="item_hidden_id">
        <div id="cust_dropdown_results" style="position:absolute;top:100%;left:0;right:0;max-height:180px;overflow-y:auto;background:white;border:1px solid #cbd5e1;border-radius:6px;display:none;z-index:99999;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);"></div>
      </div>

      <div id="items-wrap" style="margin-top:15px;">
        <label style="font-weight:600;font-size:13px;color:#475569;">Pawn item (ជ្រើសទំនិញមួយ)</label>
        <div id="items-list" style="margin-top:6px;display:flex;flex-direction:column;gap:8px;max-height:230px;overflow-y:auto;">
          <div style="color:#94a3b8;font-size:13px;padding:10px;border:1px dashed #cbd5e1;border-radius:8px;">សូមជ្រើសអតិថិជនជាមុន</div>
        </div>
      </div>

      <div class="grid2" style="margin-top:15px;">
        <div class="f"><label>Item name</label><input id="item_name_ro" readonly style="background:#f1f5f9;"></div>
        <div class="f"><label>Appraised value ($)</label><input name="item_value" id="item_value_ro" readonly style="background:#f1f5f9;"></div>
      </div>

      <div class="grid2">
        <div class="f"><label>Loan amount ($)</label><input name="loan_amount" type="number" step="0.01" min="1"></div>
        <div class="f"><label>Interest rate (% per month)</label><input name="interest_rate" type="number" step="0.01" min="0" value="3"></div>
        <div class="f"><label>Term (months)</label><input name="term_months" type="number" min="1" value="1"></div>
        <div class="f"><label>Loan date</label><input name="loan_date" type="date" value="${today}"></div>
      </div>
      <div class="f"><label>Note</label><textarea name="item_description" rows="2"></textarea></div>
      <div class="calc" id="calc"></div>`,
            "Save contract",
            async () => {
                if (!val("customer_id"))
                    throw new Error("Please select a customer.");
                if (!val("pawn_item_id"))
                    throw new Error("Please select a pawn item.");

                const body = {
                    customer_id: val("customer_id"),
                    pawn_item_id: val("pawn_item_id"),
                    item_description: val("item_description"),
                    loan_amount: val("loan_amount"),
                    interest_rate: val("interest_rate"),
                    term_months: val("term_months"),
                    loan_date: val("loan_date"),
                };
                const r = await api(base, "POST", body);
                closeModal();
                toast(r.message);
                load();
            },
        );

        const calc = () => {
            const p = +val("loan_amount") || 0,
                r = +val("interest_rate") || 0,
                m = +val("term_months") || 1;
            const interest = ((p * r) / 100) * m,
                d = new Date(val("loan_date") || Date.now());
            d.setMonth(d.getMonth() + m);
            $("#calc").innerHTML = `
        <div><span>Interest for ${m} month(s)</span><span>${money(interest)}</span></div>
        <div><span>Due date</span><span>${fmtDate(d)}</span></div>
        <div class="total"><span>Customer pays at due date</span><span>${money(p + interest)}</span></div>`;
        };

        const srcInput = $("#cust_search_input");
        const dropdown = $("#cust_dropdown_results");
        const itemsList = $("#items-list");

        function pickItem(it) {
            $("#item_hidden_id").value = it.id;
            $("#item_name_ro").value = it.item_name;
            $("#item_value_ro").value = it.market_value;
            itemsList.querySelectorAll(".item-card").forEach((card) => {
                const on = card.dataset.id == it.id;
                card.style.borderColor = on ? "#2563eb" : "#e2e8f0";
                card.style.background = on ? "#eff6ff" : "#fff";
                const radio = card.querySelector("input");
                if (radio) radio.checked = on;
            });

            const loanInput = $('#lc-modal-body [name="loan_amount"]');
            if (loanInput) {
                loanInput.value = (
                    it.approved_loan || it.market_value * 0.7
                ).toFixed(2);
                loanInput.dispatchEvent(new Event("input", { bubbles: true }));
            }
        }

        function clearItem() {
            $("#item_hidden_id").value = "";
            $("#item_name_ro").value = "";
            $("#item_value_ro").value = "";
        }

        async function loadItems(customerId) {
            clearItem();
            itemsList.innerHTML =
                '<div style="color:#94a3b8;font-size:13px;padding:10px;">Loading…</div>';
            try {
                const items = await api(
                    `${window.location.origin}/api/customers/${customerId}/pawn-items`,
                );
                if (!items.length) {
                    itemsList.innerHTML = `<div style="color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px;font-size:13px;">
            អតិថិជននេះមិនទាន់មានទំនិញ។ សូមបន្ថែមក្នុង <a href="${window.location.origin}/pawnItem" style="color:#2563eb;font-weight:600;">ទំព័រ Pawn Item</a> ជាមុន។</div>`;
                    return;
                }
                itemsList.innerHTML = items
                    .map(
                        (it) => `
          <label class="item-card" data-id="${it.id}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:8px;cursor:${it.available ? "pointer" : "not-allowed"};opacity:${it.available ? 1 : 0.55};background:#fff;">
            <input type="radio" name="_item_radio" ${it.available ? "" : "disabled"} style="margin:0;">
            <div style="flex:1;min-width:0;">
              <div style="font-weight:600;color:#1e293b;">${esc(it.item_name)}
                ${it.is_newest ? '<span style="background:#dcfce7;color:#15803d;font-size:11px;padding:2px 7px;border-radius:10px;margin-left:6px;">NEW</span>' : ""}
              </div>
              <div style="font-size:12px;color:#64748b;margin-top:2px;">
                ${esc(it.category || "")} · IMEI/Serial: ${esc(it.serial || "—")} · ${esc(it.created_at || "")}
              </div>
              ${it.available ? "" : `<div style="font-size:12px;color:#b91c1c;margin-top:2px;">${esc(it.reason)}</div>`}
            </div>
            <div style="font-weight:700;color:#16a34a;">${money(it.market_value)}</div>
          </label>`,
                    )
                    .join("");

                itemsList.querySelectorAll(".item-card").forEach((card) => {
                    const it = items.find((x) => x.id == card.dataset.id);
                    if (it.available)
                        card.addEventListener("click", () => pickItem(it));
                });

                if (prePawnItemId) {
                    const pre = items.find(
                        (x) => x.id == prePawnItemId && x.available,
                    );
                    if (pre) pickItem(pre);
                }
            } catch (e) {
                itemsList.innerHTML = `<div style="color:#b91c1c;font-size:13px;padding:10px;">${esc(e.message)}</div>`;
            }
        }

        function selectCustomer(c) {
            srcInput.value = c.customer_name;
            $("#cust_hidden_id").value = c.id;
            dropdown.style.display = "none";
            loadItems(c.id);
        }

        let searchTimer;
        srcInput.addEventListener("input", function () {
            clearTimeout(searchTimer);
            const q = this.value.trim().toLowerCase();
            $("#cust_hidden_id").value = "";
            clearItem();

            if (!q) {
                dropdown.style.display = "none";
                itemsList.innerHTML =
                    '<div style="color:#94a3b8;font-size:13px;padding:10px;border:1px dashed #cbd5e1;border-radius:8px;">សូមជ្រើសអតិថិជនជាមុន</div>';
                return;
            }

            searchTimer = setTimeout(() => {
                const matched = customers.filter(
                    (c) =>
                        c.customer_name.toLowerCase().includes(q) ||
                        (c.phone_number && c.phone_number.includes(q)) ||
                        c.id.toString() === q,
                );
                dropdown.innerHTML = "";
                if (!matched.length) {
                    dropdown.innerHTML =
                        '<div style="padding:10px 12px;color:#94a3b8;font-size:13px;">រកមិនឃើញអតិថិជន</div>';
                }
                matched.forEach((c) => {
                    const row = document.createElement("div");
                    row.style.cssText =
                        "padding:8px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9;text-align:left;";
                    row.innerHTML = `
            <div style="font-size:13.5px;color:#1e293b;"><strong>ID: ${c.id}</strong> - <span style="font-weight:600;color:#2563eb;">${esc(c.customer_name)}</span></div>
            <div style="font-size:11.5px;color:#64748b;margin-top:2px;">${esc(c.phone_number) || "—"}</div>`;
                    row.onmouseenter = () =>
                        (row.style.backgroundColor = "#f8fafc");
                    row.onmouseleave = () =>
                        (row.style.backgroundColor = "transparent");
                    row.onclick = () => selectCustomer(c);
                    dropdown.appendChild(row);
                });
                dropdown.style.display = "block";
            }, 200);
        });

        document.addEventListener("click", function handler(e) {
            if (!document.body.contains(srcInput)) {
                document.removeEventListener("click", handler);
                return;
            }
            if (!srcInput.contains(e.target) && !dropdown.contains(e.target))
                dropdown.style.display = "none";
        });

        $("#lc-modal-body").addEventListener("input", calc);
        calc();

        if (preCustomerId) {
            const c = customers.find((x) => x.id == preCustomerId);
            if (c) selectCustomer(c);
        }
    }

    /* ---------- Row actions ---------- */
    const summary = (c) => `
    <div class="calc">
      <div><span>${esc(c.contract_no)} — ${esc(c.customer?.customer_name)}</span><span>${esc(c.item_name)}</span></div>
      <div><span>Principal</span><span>${money(c.loan_amount)}</span></div>
      <div><span>Interest (${c.months_elapsed} month(s) × ${c.interest_rate}%)</span><span>${money(c.accrued_interest)}</span></div>
      <div class="total"><span>Total to redeem today</span><span>${money(c.total_due)}</span></div>
    </div>`;

    function rowAction(id, action) {
        const c = contracts.find((x) => x.id == id);
        if (!c) return;
        if (action === "print")
            return window.open(`${base}/${id}/print`, "_blank");
        const post = (path, body) =>
            api(`${base}/${id}/${path}`, "POST", body).then((r) => {
                closeModal();
                toast(r.message);
                load();
            });

        if (action === "renew")
            openModal(
                "Renew Contract",
                summary(c) +
                    `<p>The customer pays the interest above (<strong>${money(c.accrued_interest)}</strong>). Interest restarts today and the due date moves ${c.term_months} month(s) ahead.</p>`,
                "Confirm renewal",
                () => post("renew"),
            );

        if (action === "extend")
            openModal(
                "Extend Loan Period",
                summary(c) +
                    `
      <div class="grid2" style="margin-top:12px">
        <div class="f"><label>Extend by (days)</label><input name="days" type="number" min="1" value="15"></div>
        <div class="f"><label>Extension fee ($, optional)</label><input name="fee" type="number" step="0.01" min="0" value="0"></div>
      </div><p>Current due date: <strong>${fmtDate(c.due_date)}</strong>. Interest keeps accruing until the contract is renewed or closed.</p>`,
                "Extend",
                () => post("extend", { days: val("days"), fee: val("fee") }),
            );

        if (action === "redeem")
            openModal(
                "Close Contract",
                summary(c) +
                    "<p>Collect the total above from the customer and return the pawned item.</p>",
                "Close & return item",
                () => post("redeem"),
            );

        if (action === "auction")
            openModal(
                "Auction item",
                summary(c) +
                    `
      <div class="f" style="margin-top:12px"><label>Sale amount ($)</label><input name="sale_amount" type="number" step="0.01" min="0"></div>`,
                "Mark as auctioned",
                () => post("auction", { sale_amount: val("sale_amount") || 0 }),
            );
    }

    /* ---------- Events ---------- */
    $("#lc-actions").addEventListener("change", (e) => {
        const v = e.target.value;
        e.target.value = "";
        if (v === "create") openCreate();
        if (v === "refresh") load();
    });
    $("#lc-body").addEventListener("change", (e) => {
        if (e.target.classList.contains("row-act")) {
            const v = e.target.value;
            e.target.value = "";
            v && rowAction(e.target.dataset.id, v);
        }
    });
    $("#lc-status").addEventListener("change", load);
    let timer;
    $("#lc-search").addEventListener("input", () => {
        clearTimeout(timer);
        timer = setTimeout(load, 300);
    });

    // ពីទំព័រ Pawn Item: /loanContract?pawn_item=ID
    const preItem = new URLSearchParams(window.location.search).get(
        "pawn_item",
    );
    if (preItem) {
        fetch(`${window.location.origin}/api/pawn-item-owner/${preItem}`, {
            headers: { Accept: "application/json" },
        })
            .then((r) => r.json())
            .then((d) => openCreate(d.customer_id, preItem))
            .catch(() => {});
    }

    load();
})();
