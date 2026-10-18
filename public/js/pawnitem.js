//Check and alert error when the use lost input data
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("pawnForm");
    if (!form) return;

    const messages = {
        customer_search:
            "សូមបញ្ចូលឈ្មោះ ឬ ID អតិថិជន / Please enter the customer name or ID",
        category: "សូមជ្រើសរើសប្រភេទ / Please select a category",
        item_name: "សូមបញ្ចូលឈ្មោះទំនិញ / Please enter the item name",
        serial_number:
            "សូមបញ្ចូលលេខសៀរៀល ឬ លេខកូដ / Please enter the IMEI, serial number, or tag",
        storage_location:
            "សូមបញ្ចូលទីតាំងរក្សាទុក / Please enter the storage location",
        accessories: "បញ្ចូលគ្រឿងបន្លាស់ (ឧ. ប្រអប់, ដុំសាក, វិក្កយបត្រ)",
        market_value:
            "សូមបញ្ចូលតម្លៃទីផ្សារប៉ាន់ស្មាន / Please enter the estimated market value",
        approved_loan:
            "សូមបញ្ចូលប្រាក់កម្ចីដែលបានអនុម័ត / Please enter the approved loan amount",
        interest_rate:
            "សូមបញ្ចូលអត្រាការប្រាក់ / Please enter the monthly interest rate",
        pawn_date: "សូមជ្រើសរើសថ្ងៃបញ្ចាំ / Please select the pawn date",
        due_date: "សូមជ្រើសរើសថ្ងៃផុតកំណត់ / Please select the due date",
    };

    const invalidMessages = {
        market_value: "តម្លៃមិនត្រឹមត្រូវ / Invalid value",
        approved_loan: "ចំនួនទឹកប្រាក់មិនត្រឹមត្រូវ / Invalid amount",
        interest_rate: "អត្រាការប្រាក់មិនត្រឹមត្រូវ / Invalid interest rate",
    };

    const searchInput = document.getElementById("customer_search");
    const hiddenId = document.getElementById("selected_customer_id");

    // បង្ហាញសារក្រហមក្រោម input
    function showError(field, message) {
        clearError(field);
        field.classList.add("input-error");

        const div = document.createElement("div");
        div.className = "error-text";
        div.textContent = message;

        // ដាក់ក្រោម field (ចំពោះ customer_search ដាក់ក្រោម input មុន dropdown)
        field.insertAdjacentElement("afterend", div);
    }

    function clearError(field) {
        field.classList.remove("input-error");
        const next = field.nextElementSibling;
        if (next && next.classList.contains("error-text")) next.remove();
    }

    // ពិនិត្យ field មួយ ➜ return true បើត្រឹមត្រូវ
    function validateField(field) {
        const key = field.name || field.id;
        const value = field.value.trim();

        if (field.hasAttribute("required") && value === "") {
            showError(
                field,
                messages[key] ||
                    "សូមបំពេញចន្លោះនេះ / Please fill out this field",
            );
            return false;
        }

        if (field.type === "number" && value !== "" && Number(value) <= 0) {
            showError(
                field,
                invalidMessages[key] ||
                    "តម្លៃត្រូវធំជាង 0 / Value must be greater than 0",
            );
            return false;
        }

        // ត្រូវជ្រើសអតិថិជនពីបញ្ជី (មិនមែនវាយឥតគិតទេ)
        if (field === searchInput && value !== "" && !hiddenId.value) {
            showError(
                field,
                "សូមជ្រើសរើសអតិថិជនពីបញ្ជី / Please select a customer from the list",
            );
            return false;
        }

        clearError(field);
        return true;
    }

    const fields = form.querySelectorAll("[required]");

    // លុបសារក្រហមពេលអ្នកប្រើចាប់ផ្តើមកែ
    fields.forEach((field) => {
        field.addEventListener("input", () => clearError(field));
        field.addEventListener("change", () => clearError(field));
    });

    // ពិនិត្យពេលចុច Submit
    form.addEventListener("submit", (e) => {
        let firstInvalid = null;

        fields.forEach((field) => {
            if (!validateField(field) && !firstInvalid) {
                firstInvalid = field;
            }
        });

        if (firstInvalid) {
            e.preventDefault();
            firstInvalid.focus();
        }
    });
});

//----------- ទាញឈ្មោះអតិថិជនពីតារាង customers ------------
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("customer_search");
    const hiddenIdInput = document.getElementById("selected_customer_id");
    const dropdown = document.getElementById("search_results_dropdown");
    if (!searchInput || !hiddenIdInput || !dropdown) return;

    const SEARCH_URL = "/api/search-customers";
    let items = [];
    let activeIndex = -1;
    let requestId = 0;
    let timer;

    searchInput.parentNode.style.position = "relative";
    Object.assign(dropdown.style, {
        position: "absolute",
        top: "100%",
        left: "0",
        right: "0",
        marginTop: "4px",
        maxHeight: "240px",
        overflowY: "auto",
        backgroundColor: "#fff",
        border: "1px solid #cbd5e1",
        borderRadius: "6px",
        boxShadow: "0 10px 15px -3px rgba(0,0,0,.1)",
        zIndex: "99999",
        display: "none",
    });

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

    function showMessage(text, color = "#94a3b8") {
        dropdown.innerHTML = `<div style="padding:12px;color:${color};font-size:13px;text-align:center;">${text}</div>`;
        dropdown.style.display = "block";
    }

    function highlight() {
        [...dropdown.children].forEach((el, i) => {
            el.style.background = i === activeIndex ? "#eaf1ff" : "transparent";
        });
    }

    function pick(c) {
        searchInput.value = `${c.customer_name} (ID: ${c.id})`;
        hiddenIdInput.value = c.id;
        searchInput.style.borderColor = "";
        dropdown.style.display = "none";
    }

    function render() {
        dropdown.innerHTML = "";
        items.forEach((c, i) => {
            const row = document.createElement("div");
            row.style.cssText =
                "padding:10px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9;";
            row.innerHTML = `
                <div style="font-size:14px;color:#1e293b;">
                    <strong>ID: ${esc(c.id)}</strong> -
                    <span style="font-weight:600;color:#2563eb;">${esc(c.customer_name)}</span>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">📞 ${esc(c.phone_number) || "—"}</div>`;
            row.addEventListener("mouseenter", () => {
                activeIndex = i;
                highlight();
            });
            row.addEventListener("mousedown", (e) => {
                e.preventDefault();
                pick(c);
            });
            dropdown.appendChild(row);
        });
        dropdown.style.display = "block";
    }

    async function performSearch() {
        const query = searchInput.value.trim();
        if (!query) {
            dropdown.style.display = "none";
            return;
        }

        const myRequest = ++requestId;
        try {
            const res = await fetch(
                `${SEARCH_URL}?query=${encodeURIComponent(query)}`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    credentials: "same-origin",
                },
            );
            if (myRequest !== requestId) return;
            if (!res.ok) {
                showMessage(`មានបញ្ហា Server (${res.status})`, "#dc2626");
                return;
            }

            items = await res.json();
            activeIndex = -1;
            items.length
                ? render()
                : showMessage("រកមិនឃើញអតិថិជន / No customer found");
        } catch (err) {
            console.error("Error fetching customers:", err);
            showMessage("មិនអាចតភ្ជាប់ Server បាន", "#dc2626");
        }
    }

    searchInput.addEventListener("input", () => {
        hiddenIdInput.value = ""; // វាយថ្មី → ត្រូវជ្រើសម្តងទៀត
        clearTimeout(timer);
        timer = setTimeout(performSearch, 250);
    });

    searchInput.addEventListener("keydown", (e) => {
        if (dropdown.style.display === "none" || !items.length) return;
        if (e.key === "ArrowDown") {
            activeIndex = (activeIndex + 1) % items.length;
            highlight();
            e.preventDefault();
        } else if (e.key === "ArrowUp") {
            activeIndex = (activeIndex - 1 + items.length) % items.length;
            highlight();
            e.preventDefault();
        } else if (e.key === "Enter" && activeIndex >= 0) {
            pick(items[activeIndex]);
            e.preventDefault();
        } else if (e.key === "Escape") {
            dropdown.style.display = "none";
        }
    });

    searchInput.addEventListener("focus", () => {
        if (searchInput.value.trim() && dropdown.children.length)
            dropdown.style.display = "block";
    });

    document.addEventListener("click", (e) => {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target))
            dropdown.style.display = "none";
    });

    // មិនឱ្យ submit បើមិនទាន់ជ្រើសអតិថិជន
    const form = searchInput.closest("form");
    if (form) {
        form.addEventListener("submit", (e) => {
            if (!hiddenIdInput.value) {
                e.preventDefault();
                searchInput.focus();
                searchInput.style.borderColor = "#dc2626";
                alert(
                    "សូមជ្រើសអតិថិជនពីបញ្ជី / Please choose a customer from the list",
                );
            }
        });
    }
});


// =============================================================
document.addEventListener("DOMContentLoaded", function () {
    /* ---------- Modal ---------- */
    const modal = document.getElementById("pawnModal");
    const openModal = () => modal && modal.classList.add("show-modal");
    const closeModal = () => modal && modal.classList.remove("show-modal");

    document
        .getElementById("openPawnModalBtn")
        ?.addEventListener("click", openModal);
    document
        .getElementById("closePawnModalBtn")
        ?.addEventListener("click", closeModal);
    document
        .getElementById("cancelPawnModalBtn")
        ?.addEventListener("click", closeModal);
    modal?.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeModal();
    });

    // បើកឡើងវិញពេល server ត្រឡប់មកជាមួយ error
    if (modal && modal.querySelector(".alert-error")) openModal();

    /* ---------- Search + Category + Status filter ---------- */
    const searchInput = document.getElementById("table_search_input");
    const categoryFilter = document.getElementById("category_filter");
    const statusFilter = document.getElementById("item_status_filter");
    const rows = Array.from(
        document.querySelectorAll(
            "#inventory_data_table tbody tr[data-status]",
        ),
    );
    const noResult = document.getElementById("pawn-no-result");

    function applyFilters() {
        const kw = (searchInput?.value || "").trim().toLowerCase();
        const cat = categoryFilter?.value || "all";
        const st = statusFilter?.value || "all";
        let visible = 0;

        rows.forEach(function (row) {
            const text = [
                row.children[0]?.textContent,
                row.querySelector(".customer-name-td")?.textContent,
                row.querySelector(".item-name-td")?.textContent,
                row.children[5]?.textContent,
            ]
                .join(" ")
                .toLowerCase();

            const show =
                (kw === "" || text.includes(kw)) &&
                (cat === "all" || row.dataset.category === cat) &&
                (st === "all" || row.dataset.status === st);

            row.style.display = show ? "" : "none";
            if (show) visible++;
        });

        if (noResult)
            noResult.style.display =
                visible === 0 && rows.length > 0 ? "" : "none";
    }

    searchInput?.addEventListener("input", applyFilters);
    categoryFilter?.addEventListener("change", applyFilters);
    statusFilter?.addEventListener("change", applyFilters);
});



document.addEventListener("DOMContentLoaded", function () {
    const inputs = document.querySelectorAll(
        'input[name="item_photo"], input[name="customer_avatar"], input[name="id_card_photo"]'
    );

    inputs.forEach(function (fileInput) {
        fileInput.addEventListener("change", async function () {
            const file = this.files[0];
            if (!file || !file.type.startsWith("image/")) return;

            try {
                const img = new Image();
                const url = URL.createObjectURL(file);
                img.src = url;
                await img.decode();

                const MAX = 1600;
                const scale = Math.min(1, MAX / Math.max(img.width, img.height));
                const canvas = document.createElement("canvas");
                canvas.width = Math.round(img.width * scale);
                canvas.height = Math.round(img.height * scale);
                canvas.getContext("2d").drawImage(img, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(url);

                canvas.toBlob(
                    function (blob) {
                        if (!blob) return;
                        const name = file.name.replace(/\.[^.]+$/, "") + ".jpg";
                        const resized = new File([blob], name, { type: "image/jpeg" });
                        const dt = new DataTransfer();
                        dt.items.add(resized);
                        fileInput.files = dt.files;
                    },
                    "image/jpeg",
                    0.8
                );
            } catch (e) {
                console.warn("Image resize skipped:", e);
            }
        });
    });
});