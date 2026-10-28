(() => {
    const app = document.getElementById("us-app");
    if (!app) return;

    const $ = (s, p = app) => p.querySelector(s);
    const dlg = $("#us-dialog");
    const form = $("#us-form");
    const el = (n) => form.elements.namedItem(n);

    /* ---------- Toast ---------- */
    const toast = $("#us-toast");
    if (toast) setTimeout(() => toast.remove(), 4500);

    /* ---------- Open dialog (create / edit) ---------- */
    function setErr(name, msg) {
        const field = el(name).closest(".us-field");
        field.classList.toggle("invalid", !!msg);
        field.querySelector(".us-err").textContent = msg || "";
    }
    ["name", "email", "phone", "role", "password"].forEach((n) =>
        el(n).addEventListener("input", () => setErr(n, "")),
    );

    function openDialog(mode, data = {}) {
        form.reset();
        ["name", "email", "phone", "role", "password"].forEach((n) =>
            setErr(n, ""),
        );

        const edit = mode === "edit";
        const self = data.self === "1";

        form.action = edit
            ? `${app.dataset.update}/${data.id}`
            : app.dataset.store;
        el("_method").disabled = !edit;
        el("_mode").value = mode;
        el("_id").value = data.id || "";

        $("#us-dlg-title").innerHTML = edit
            ? '<i class="fa-solid fa-user-pen"></i> កែអ្នកប្រើ'
            : '<i class="fa-solid fa-user-plus"></i> បន្ថែមអ្នកប្រើ';

        el("name").value = data.name || "";
        el("email").value = data.email || "";
        el("phone").value = data.phone || "";
        el("role").value = data.role || "cashier";
        el("is_active").checked = edit ? data.active === "1" : true;

        // Password: ចាំបាច់ពេលបង្កើត, ស្រេចចិត្តពេលកែ
        $("#u-pass-req").hidden = edit;
        $("#u-pass-lbl").textContent = edit
            ? "ពាក្យសម្ងាត់ថ្មី"
            : "ពាក្យសម្ងាត់";
        $("#u-pass-hint").textContent = edit
            ? "ទុកទទេ បើមិនចង់ប្តូរ"
            : "យ៉ាងតិច ៨ តួអក្សរ មានអក្សរ និងលេខ";

        // គណនីខ្លួនឯង: មិនអាចប្តូរ Role / ផ្អាក
        el("role").disabled = self;
        el("is_active").disabled = self;
        $("#u-self-note").hidden = !self;

        dlg.showModal();
        el("name").focus();
    }

    $("#us-add").addEventListener("click", () => openDialog("create"));
    app.addEventListener("click", (e) => {
        const btn = e.target.closest(".js-edit");
        if (btn) openDialog("edit", { ...btn.closest("tr").dataset });
        const closer = e.target.closest("[data-close]");
        if (closer) closer.closest("dialog").close();
    });
    // ចុចលើផ្ទៃខាងក្រៅ → បិទ
    dlg.addEventListener("mousedown", (e) => {
        if (e.target === dlg) dlg.close();
    });

    /* ---------- Client-side validation ---------- */
    form.addEventListener("submit", (e) => {
        const v = (n) => el(n).value.trim();
        const edit = el("_mode").value === "edit";
        const pass = el("password").value;
        const errs = {
            name: !v("name")
                ? "សូមបញ្ចូលឈ្មោះ។"
                : v("name").length < 2
                  ? "ឈ្មោះត្រូវមានយ៉ាងតិច ២ តួអក្សរ។"
                  : "",
            email: !v("email")
                ? "សូមបញ្ចូលអ៊ីមែល។"
                : !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v("email"))
                  ? "ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ។"
                  : "",
            phone:
                v("phone") && !/^[0-9+\-\s()]{6,20}$/.test(v("phone"))
                    ? "លេខទូរស័ព្ទមិនត្រឹមត្រូវ។"
                    : "",
            role: "",
            password:
                !edit && !pass
                    ? "សូមបញ្ចូលពាក្យសម្ងាត់។"
                    : pass &&
                        !(
                            pass.length >= 8 &&
                            /[A-Za-z]/.test(pass) &&
                            /\d/.test(pass)
                        )
                      ? "ត្រូវមាន ៨ តួអក្សរ មានអក្សរ និងលេខ។"
                      : "",
        };

        let first = null;
        Object.keys(errs).forEach((n) => {
            setErr(n, errs[n]);
            if (errs[n] && !first) first = el(n);
        });
        if (first) {
            e.preventDefault();
            first.focus();
        }
    });

    /* ---------- Approve pending signup ---------- */
    const apDlg = $("#us-approve");
    const apForm = $("#us-approve-form");
    if (apDlg) {
        app.addEventListener("click", (e) => {
            const b = e.target.closest(".js-approve");
            if (!b) return;
            apForm.action = `${app.dataset.update}/${b.dataset.id}/approve`;
            apForm.reset();
            $("#ap-err").textContent = "";
            $("#us-ap-name").innerHTML =
                `<i class="fa-solid fa-user"></i> ${b.dataset.name}`;
            apDlg.showModal();
        });
        apDlg.addEventListener("mousedown", (e) => {
            if (e.target === apDlg) apDlg.close();
        });
        apForm.addEventListener("submit", (e) => {
            if (!apForm.elements.role.value) {
                e.preventDefault();
                $("#ap-err").textContent = "សូមជ្រើសរើស Role ជាមុន។";
            }
        });
    }

    /* ---------- បើក Dialog ឡើងវិញ ពេល Server រកឃើញកំហុស ---------- */
    let old = {};
    try {
        old = JSON.parse(app.dataset.old || "{}");
    } catch (_) {}

    if (old._mode) {
        const row = old._id
            ? app.querySelector(`tr[data-id="${old._id}"]`)
            : null;
        openDialog(old._mode, row ? { ...row.dataset } : {});
        ["name", "email", "phone"].forEach((n) => {
            if (old[n] !== undefined) el(n).value = old[n];
        });
        if (old.role && !el("role").disabled) el("role").value = old.role;
        if (!el("is_active").disabled)
            el("is_active").checked = !!old.is_active;
    }
})();
