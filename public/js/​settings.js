document.addEventListener("DOMContentLoaded", function () {
    var root = document.getElementById("st-app");
    if (!root) return;
    var tabs = document.querySelectorAll(".st-tabs .tab");
    var panes = document.querySelectorAll("[data-pane]");

    function show(t) {
        tabs.forEach(function (b) {
            b.classList.toggle("active", b.dataset.tab === t);
        });
        panes.forEach(function (p) {
            p.hidden = p.dataset.pane !== t;
        });
    }
    tabs.forEach(function (b) {
        b.addEventListener("click", function () {
            show(b.dataset.tab);
        });
    });
    show(root.dataset.tab || "shop");

    var rate = document.getElementById("in_rate"),
        max = document.getElementById("in_max"),
        ex = document.getElementById("in_example");
    function ie() {
        if (!rate || !max || !ex) return;
        var loan = (1000 * (+max.value || 0)) / 100,
            m = (loan * (+rate.value || 0)) / 100;
        ex.innerHTML =
            "ឧទាហរណ៍៖ ទំនិញ <b>$1,000</b> → ខ្ចីបាន <b>$" +
            loan.toFixed(2) +
            "</b> → ការប្រាក់ ១ ខែ <b>$" +
            m.toFixed(2) +
            "</b>";
    }
    [rate, max].forEach(function (e) {
        if (e) e.addEventListener("input", ie);
    });
    ie();

    var kr = document.getElementById("cu_rate"),
        ce = document.getElementById("cu_example");
    function ce_() {
        if (kr && ce)
            ce.innerHTML =
                "ឧទាហរណ៍៖ <b>$10</b> = <b>" +
                (10 * (+kr.value || 0)).toLocaleString() +
                " ៛</b>";
    }
    if (kr) kr.addEventListener("input", ce_);
    ce_();
});
(() => {
    const root = document.getElementById("st-app");
    if (!root) return;

    const tabs = root.querySelectorAll(".st-tabs .tab[data-tab]");
    const panes = root.querySelectorAll("[data-pane]");
    const $ = (id) => document.getElementById(id);

    /* ---------- ប្តូរ Tab ---------- */
    function show(name) {
        if (![...panes].some((p) => p.dataset.pane === name)) name = "shop";
        panes.forEach((p) => {
            p.hidden = p.dataset.pane !== name;
        });
        tabs.forEach((t) => {
            const on = t.dataset.tab === name;
            t.classList.toggle("active", on);
            t.setAttribute("aria-selected", on ? "true" : "false");
        });
    }
    tabs.forEach((t) => t.addEventListener("click", () => show(t.dataset.tab)));
    // បន្ទាប់ពី Save ត្រឡប់មក Tab ដដែល (មកពី session('tab'))
    show(root.dataset.tab || "shop");

    /* ---------- ឧទាហរណ៍គណនា: Interest ---------- */
    const symbolInput = root.querySelector('[name="currency_symbol"]');
    const sym = () => (symbolInput && symbolInput.value) || "$";
    const fmt = (n) =>
        Number(n).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

    function interestExample() {
        const out = $("in_example");
        if (!out) return;
        const rate = parseFloat($("in_rate").value) || 0;
        const max = parseFloat($("in_max").value) || 0;
        const value = 1000;
        const loan = (value * max) / 100;
        const monthly = (loan * rate) / 100;
        out.innerHTML = `ឧទាហរណ៍៖ ទំនិញតម្លៃ <b>${sym()}${fmt(value)}</b>
            ខ្ចីបានអតិបរមា <b>${sym()}${fmt(loan)}</b>
            ការប្រាក់ ១ ខែ <b>${sym()}${fmt(monthly)}</b>`;
    }
    ["in_rate", "in_max"].forEach((id) => {
        const el = $(id);
        if (el) el.addEventListener("input", interestExample);
    });
    interestExample();

    /* ---------- ឧទាហរណ៍គណនា: Currency ---------- */
    function currencyExample() {
        const out = $("cu_example");
        if (!out) return;
        const rate = parseFloat($("cu_rate").value) || 0;
        out.innerHTML = `ឧទាហរណ៍៖ <b>${sym()}100.00</b> =
            <b>${Math.round(100 * rate).toLocaleString("en-US")} ៛</b>`;
    }
    const cu = $("cu_rate");
    if (cu) cu.addEventListener("input", currencyExample);
    if (symbolInput)
        symbolInput.addEventListener("input", () => {
            currencyExample();
            interestExample();
        });
    currencyExample();
})();

/* ---------- Restore form: custom error messages ---------- */
const rf = $("restore-form");
if (rf) {
    const err = $("restore-err");
    const file = rf.querySelector('[name="sql_file"]');
    const conf = rf.querySelector('[name="confirm"]');

    const fail = (msg, el) => {
        err.textContent = msg;
        err.hidden = false;
        el.focus();
    };
    ["input", "change"].forEach((ev) =>
        rf.addEventListener(ev, () => (err.hidden = true)),
    );

    rf.addEventListener("submit", (e) => {
        err.hidden = true;

        if (!file.files.length) {
            e.preventDefault();
            return fail("សូមជ្រើសរើសឯកសារ Backup (.sql) ជាមុន។", file);
        }
        if (!/\.(sql|txt)$/i.test(file.files[0].name)) {
            e.preventDefault();
            return fail("ឯកសារត្រូវតែជាប្រភេទ .sql ប៉ុណ្ណោះ។", file);
        }
        if (conf.value.trim() !== "RESTORE") {
            e.preventDefault();
            return fail("សូមវាយពាក្យ RESTORE ឱ្យត្រូវ ដើម្បីបញ្ជាក់។", conf);
        }
        if (!confirm("ទិន្នន័យបច្ចុប្បន្នទាំងអស់នឹងត្រូវជំនួស។ បន្តដែរឬទេ?")) {
            e.preventDefault();
        }
    });
}
