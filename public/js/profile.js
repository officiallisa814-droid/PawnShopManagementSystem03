(() => {
    const app = document.getElementById("pf-app");
    if (!app) return;

    const $ = (s, p = app) => p.querySelector(s);
    const $$ = (s, p = app) => [...p.querySelectorAll(s)];

    /* ---------- Toast ---------- */
    const toast = $("#pf-toast");
    if (toast) setTimeout(() => toast.remove(), 4000);

    /* ---------- Tabs ---------- */
    const tabs = $$(".pf-tab");
    const panes = $$(".pf-pane");
    function show(name) {
        if (!panes.some((p) => p.dataset.pane === name)) name = "info";
        panes.forEach((p) => (p.hidden = p.dataset.pane !== name));
        tabs.forEach((t) => {
            const on = t.dataset.tab === name;
            t.classList.toggle("active", on);
            t.setAttribute("aria-selected", on ? "true" : "false");
        });
    }
    tabs.forEach((t) => t.addEventListener("click", () => show(t.dataset.tab)));
    show(app.dataset.tab || "info");

    /* ---------- Error helpers ---------- */
    const setErr = (form, name, msg) => {
        const el = form.elements[name];
        if (!el) return;
        const field = el.closest(".pf-field");
        field.classList.toggle("invalid", !!msg);
        const out = field.querySelector(".pf-err");
        if (out) out.textContent = msg || "";
    };
    $$("form.pf-pane").forEach((form) =>
        form.addEventListener("input", (e) => {
            if (e.target.name) setErr(form, e.target.name, "");
        }),
    );

    function runChecks(form, checks) {
        let firstBad = null;
        Object.keys(checks).forEach((name) => {
            const msg = checks[name];
            setErr(form, name, msg);
            if (msg && !firstBad) firstBad = form.elements[name];
        });
        return firstBad;
    }
    function lockOnSubmit(form) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = "កំពុងរក្សាទុក…";
        }
    }

    /* ---------- Personal info form ---------- */
    const info = $("#info-form");
    info.addEventListener("submit", (e) => {
        const v = (n) => info.elements[n].value.trim();
        const bad = runChecks(info, {
            name: !v("name")
                ? "សូមបញ្ចូលឈ្មោះរបស់អ្នក។"
                : v("name").length < 2
                  ? "ឈ្មោះត្រូវមានយ៉ាងតិច ២ តួអក្សរ។"
                  : "",
            email: !v("email")
                ? "សូមបញ្ចូលអ៊ីមែល។"
                : !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v("email"))
                  ? "ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ (ឧ. name@example.com)។"
                  : "",
            phone:
                v("phone") && !/^[0-9+\-\s()]{6,20}$/.test(v("phone"))
                    ? "លេខទូរស័ព្ទមិនត្រឹមត្រូវ។"
                    : "",
        });
        if (bad) {
            e.preventDefault();
            bad.focus();
        } else lockOnSubmit(info);
    });

    /* ---------- Password form ---------- */
    const pwForm = $("#pw-form");
    const pw = pwForm.elements["password"];
    const meter = $(".pf-meter");
    const strengthLbl = $("#pf-strength");
    const RULES = {
        len: (v) => v.length >= 8,
        let: (v) => /[A-Za-z]/.test(v),
        num: (v) => /\d/.test(v),
    };

    pw.addEventListener("input", () => {
        const v = pw.value;
        $$(".pf-rules li").forEach((li) =>
            li.classList.toggle("ok", RULES[li.dataset.rule](v)),
        );
        let s = 0;
        if (v.length >= 8) s++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
        if (/\d/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        if (!v) s = 0;
        meter.dataset.s = s;
        strengthLbl.textContent = ["", "ខ្សោយ", "មធ្យម", "ល្អ", "ខ្លាំង"][s];
    });

    pwForm.addEventListener("submit", (e) => {
        const f = pwForm.elements;
        const bad = runChecks(pwForm, {
            current_password: !f.current_password.value
                ? "សូមបញ្ចូលពាក្យសម្ងាត់បច្ចុប្បន្ន។"
                : "",
            password: !pw.value
                ? "សូមបញ្ចូលពាក្យសម្ងាត់ថ្មី។"
                : !(
                        RULES.len(pw.value) &&
                        RULES.let(pw.value) &&
                        RULES.num(pw.value)
                    )
                  ? "ពាក្យសម្ងាត់ត្រូវមាន ៨ តួអក្សរ មានអក្សរ និងលេខ។"
                  : "",
            password_confirmation: !f.password_confirmation.value
                ? "សូមបញ្ជាក់ពាក្យសម្ងាត់ថ្មី។"
                : f.password_confirmation.value !== pw.value
                  ? "ការបញ្ជាក់ពាក្យសម្ងាត់មិនត្រូវគ្នា។"
                  : "",
        });
        if (bad) {
            e.preventDefault();
            bad.focus();
        } else lockOnSubmit(pwForm);
    });

    // បង្ហាញ / លាក់ ពាក្យសម្ងាត់
    $$("[data-eye]").forEach((b) =>
        b.addEventListener("click", () => {
            const input = b.parentElement.querySelector("input");
            const reveal = input.type === "password";
            input.type = reveal ? "text" : "password";
            b.innerHTML = `<i class="fa-regular fa-eye${reveal ? "-slash" : ""}"></i>`;
        }),
    );

    /* ---------- Avatar preview ---------- */
    const input = $("#avatar-input");
    const img = $("#pf-avatar-img");
    const letter = $("#pf-avatar-letter");
    const bar = $("#avatar-actions");
    const rm = $("#avatar-remove-form");
    const aerr = $("#avatar-err");
    const original = { src: img.getAttribute("src") || "", had: !img.hidden };

    function restore() {
        input.value = "";
        if (original.had) {
            img.src = original.src;
            img.hidden = false;
            letter.hidden = true;
        } else {
            img.hidden = true;
            letter.hidden = false;
        }
        bar.hidden = true;
        if (rm) rm.hidden = false;
    }

    input.addEventListener("change", () => {
        aerr.textContent = "";
        const file = input.files[0];
        if (!file) return;

        if (!/^image\/(png|jpe?g|webp)$/i.test(file.type)) {
            aerr.textContent = "អនុញ្ញាតតែ JPG, PNG ឬ WEBP ប៉ុណ្ណោះ។";
            restore();
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            aerr.textContent = "រូបភាពធំពេក (អតិបរមា 2MB)។";
            restore();
            return;
        }
        img.src = URL.createObjectURL(file);
        img.hidden = false;
        letter.hidden = true;
        bar.hidden = false;
        if (rm) rm.hidden = true;
    });
    $("#avatar-cancel").addEventListener("click", () => {
        aerr.textContent = "";
        restore();
    });
})();
