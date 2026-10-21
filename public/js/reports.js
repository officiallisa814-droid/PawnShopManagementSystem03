(() => {
    const root = document.getElementById("rp-app");
    if (!root) return;
    const DATA_URL = root.dataset.data;
    let report = null;

    const $ = (s, p = document) => p.querySelector(s);
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
    const fmt = (d) =>
        d ? d.slice(8, 10) + "/" + d.slice(5, 7) + "/" + d.slice(0, 4) : "";

    function toast(msg) {
        const t = $("#rp-toast");
        t.textContent = msg;
        t.hidden = false;
        clearTimeout(toast.t);
        toast.t = setTimeout(() => (t.hidden = true), 3500);
    }

    async function run() {
        const qs = new URLSearchParams({
            type: $("#rp-type").value,
            from: $("#rp-from").value,
            to: $("#rp-to").value,
        });
        $("#rp-body").innerHTML = '<tr><td class="empty">Loading…</td></tr>';
        try {
            const res = await fetch(`${DATA_URL}?${qs}`, {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            });
            if (!res.ok) throw new Error("Server error " + res.status);
            report = await res.json();
            render();
        } catch (e) {
            toast(e.message);
        }
    }

    function render() {
        const r = report;
        $("#rp-title").textContent = r.title;
        $("#rp-period").textContent =
            `Period: ${fmt(r.from)} – ${fmt(r.to)}   ·   Generated: ${new Date().toLocaleString()}`;

        $("#rp-summary").innerHTML = (r.summary || [])
            .map(
                (s) =>
                    `<div class="stat"><b>${esc(s[1])}</b><span>${esc(s[0])}</span></div>`,
            )
            .join("");

        const note = $("#rp-note");
        note.hidden = !r.note;
        note.textContent = r.note || "";

        $("#rp-head").innerHTML =
            "<tr>" +
            r.columns.map((c) => `<th>${esc(c)}</th>`).join("") +
            "</tr>";
        $("#rp-body").innerHTML = r.rows.length
            ? r.rows
                  .map(
                      (row) =>
                          "<tr>" +
                          row
                              .map(
                                  (v, i) =>
                                      `<td data-label="${esc(r.columns[i])}">${esc(v ?? "—")}</td>`,
                              )
                              .join("") +
                          "</tr>",
                  )
                  .join("")
            : `<tr><td class="empty" colspan="${r.columns.length}">No data for this period.</td></tr>`;
    }

    /* ---------- Export ---------- */
    function needData() {
        if (!report) {
            toast("Generate a report first.");
            return false;
        }
        return true;
    }

    function csvCell(v) {
        return '"' + String(v ?? "").replace(/"/g, '""') + '"';
    }

    function exportCsv() {
        if (!needData()) return;
        const lines = [];
        lines.push([report.title].map(csvCell).join(","));
        lines.push(
            [`Period: ${fmt(report.from)} - ${fmt(report.to)}`]
                .map(csvCell)
                .join(","),
        );
        lines.push("");
        (report.summary || []).forEach((s) =>
            lines.push(s.map(csvCell).join(",")),
        );
        lines.push("");
        lines.push(report.columns.map(csvCell).join(","));
        report.rows.forEach((r) => lines.push(r.map(csvCell).join(",")));

        const blob = new Blob(["\uFEFF" + lines.join("\r\n")], {
            type: "text/csv;charset=utf-8;",
        });
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = `${$("#rp-type").value}-report-${report.from}_${report.to}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    }

    $("#rp-run").onclick = run;
    $("#rp-type").onchange = run;
    $("#rp-print").onclick = () => needData() && window.print();
    $("#rp-pdf").onclick = () => {
        if (!needData()) return;
        toast(
            "ជ្រើស 'Save as PDF' ក្នុងផ្ទាំង Print / Choose 'Save as PDF' as the printer",
        );
        setTimeout(() => window.print(), 600);
    };
    $("#rp-excel").onclick = exportCsv;

    run();
})();
