document.addEventListener("DOMContentLoaded", function () {
    /* ============================================================
     * 1. OPEN / CLOSE MODAL
     * ============================================================ */
    const btnAdd = document.querySelector(".btn-add");
    const popUpBox = document.getElementById("customerModal");
    const btnCloseX = document.getElementById("closeModalBtn");
    const btnCancel = document.getElementById("cancelModalBtn");

    function openModal() {
        if (popUpBox) popUpBox.classList.add("show-modal");
    }

    function closeModal() {
        if (popUpBox) popUpBox.classList.remove("show-modal");
    }

    if (btnAdd) {
        btnAdd.addEventListener("click", function (e) {
            e.preventDefault();
            openModal();
        });
    }
    if (btnCloseX) {
        btnCloseX.addEventListener("click", function (e) {
            e.preventDefault();
            closeModal();
        });
    }
    if (btnCancel) {
        btnCancel.addEventListener("click", function (e) {
            e.preventDefault();
            closeModal();
        });
    }
    if (popUpBox) {
        // ចុចក្រៅប្រអប់ដើម្បីបិទ
        popUpBox.addEventListener("click", function (e) {
            if (e.target === popUpBox) closeModal();
        });
    }

    // ចុច Esc ដើម្បីបិទ
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeModal();
    });

    // បើកឡើងវិញស្វ័យប្រវត្តិ ពេល server ត្រឡប់មកវិញជាមួយ error
    if (popUpBox && popUpBox.querySelector(".alert-error")) {
        openModal();
        const firstError = popUpBox.querySelector(".input-error");
        if (firstError) {
            setTimeout(function () {
                firstError.scrollIntoView({ block: "center" });
            }, 300);
        }
    }
    /* ============================================================
     * 2. SEARCH + PROVINCE FILTER + STATUS FILTER + STATUS SORT
     * ============================================================ */
    const searchInput = document.getElementById("customer_search_input");
    const provinceInput = document.getElementById("province_filter_input");
    const statusFilter = document.getElementById("status_filter_select");
    const statusSort = document.getElementById("status_sort_select");
    const resetBtn = document.getElementById("reset_filters_btn");
    const tableBody = document.getElementById("customer-table-body");
    const noResultRow = document.getElementById("no-result-row");

    if (tableBody) {
        // យកតែជួរអតិថិជនពិតៗ (មាន data-status)
        const allRows = Array.from(
            tableBody.querySelectorAll("tr[data-status]"),
        );

        function applyFilters() {
            const keyword = searchInput
                ? searchInput.value.trim().toLowerCase()
                : "";
            const province = provinceInput
                ? provinceInput.value.trim().toLowerCase()
                : "";
            const status = statusFilter ? statusFilter.value : "all";
            const sortMode = statusSort ? statusSort.value : "default";

            // ១. ត្រង
            const visibleRows = allRows.filter(function (row) {
                // ស្វែងរកតែ ID, ឈ្មោះ, Passport, ទូរស័ព្ទ
                const searchText = [
                    row.children[1] ? row.children[1].textContent : "",
                    row.children[2] ? row.children[2].textContent : "",
                    row.children[3] ? row.children[3].textContent : "",
                    row.children[4] ? row.children[4].textContent : "",
                ]
                    .join(" ")
                    .toLowerCase();

                const matchKeyword =
                    keyword === "" || searchText.includes(keyword);
                const matchProvince =
                    province === "" ||
                    (row.dataset.province || "").includes(province);
                const matchStatus =
                    status === "all" || row.dataset.status === status;

                return matchKeyword && matchProvince && matchStatus;
            });

            // ២. តម្រៀប
            visibleRows.sort(function (a, b) {
                if (
                    sortMode === "active_first" ||
                    sortMode === "inactive_first"
                ) {
                    const aActive = a.dataset.status === "active" ? 1 : 0;
                    const bActive = b.dataset.status === "active" ? 1 : 0;
                    if (aActive !== bActive) {
                        return sortMode === "active_first"
                            ? bActive - aActive
                            : aActive - bActive;
                    }
                }
                return Number(a.dataset.index) - Number(b.dataset.index);
            });

            // ៣. លាក់ទាំងអស់ រួចបង្ហាញតាមលំដាប់ថ្មី
            allRows.forEach(function (row) {
                row.style.display = "none";
            });
            visibleRows.forEach(function (row) {
                row.style.display = "";
                tableBody.insertBefore(row, noResultRow || null);
            });

            // ៤. សារ "រកមិនឃើញ"
            if (noResultRow) {
                noResultRow.style.display =
                    visibleRows.length === 0 && allRows.length > 0
                        ? ""
                        : "none";
            }
        }

        if (searchInput) searchInput.addEventListener("input", applyFilters);
        if (provinceInput)
            provinceInput.addEventListener("input", applyFilters);
        if (statusFilter) statusFilter.addEventListener("change", applyFilters);
        if (statusSort) statusSort.addEventListener("change", applyFilters);

        if (resetBtn) {
            resetBtn.addEventListener("click", function () {
                if (searchInput) searchInput.value = "";
                if (provinceInput) provinceInput.value = "";
                if (statusFilter) statusFilter.value = "all";
                if (statusSort) statusSort.value = "default";
                applyFilters();
            });
        }
    }

    /* ============================================================
     * LAYER FILTERS DROPDOWN (បើក/បិទ)
     * ============================================================ */
    const layerBtn = document.getElementById("layer_filter_btn");
    const layerDropdown = document.getElementById("layer_dropdown");
    const layerCloseBtn = document.getElementById("layer_close_btn");

    if (layerBtn && layerDropdown) {
        function toggleLayer(forceOpen) {
            const willOpen =
                typeof forceOpen === "boolean"
                    ? forceOpen
                    : !layerDropdown.classList.contains("show");
            layerDropdown.classList.toggle("show", willOpen);
            layerBtn.classList.toggle("open", willOpen);
        }

        layerBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            toggleLayer();
        });

        if (layerCloseBtn) {
            layerCloseBtn.addEventListener("click", function () {
                toggleLayer(false);
            });
        }

        document.addEventListener("click", function (e) {
            if (
                !layerDropdown.contains(e.target) &&
                !layerBtn.contains(e.target)
            ) {
                toggleLayer(false);
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") toggleLayer(false);
        });
    }
    /* ============================================================
     * 3. CLIENT-SIDE FORM VALIDATION
     * ============================================================ */
    const form = document.querySelector(".modal-form");

    if (form) {
        // សារខ្មែរ និងអង់គ្លេសតាមឈ្មោះ field (ប្រើ name ក្នុង HTML)
        const labels = {
            customer_name: [
                "សូមបញ្ចូលឈ្មោះអតិថិជន",
                "Please enter customer name",
            ],
            gender: ["សូមជ្រើសរើសភេទ", "Please select gender"],
            dob: ["សូមបញ្ចូលថ្ងៃខែឆ្នាំកំណើត", "Please enter date of birth"],
            passport_id: [
                "សូមបញ្ចូលអត្តសញ្ញាណប័ណ្ណ ឬ លិខិតឆ្លងដែន",
                "Please enter ID or passport",
            ],
            phone_number: ["សូមបញ្ចូលលេខទូរស័ព្ទ", "Please enter phone number"],
            email: ["សូមបញ្ចូលអ៊ីមែល", "Please enter email"],
            address_village: [
                "សូមបញ្ចូលភូមិ-ឃុំ-សង្កាត់",
                "Please enter village-commune",
            ],
            address_district: [
                "សូមបញ្ចូលក្រុង-ស្រុក-ខណ្ឌ",
                "Please enter district",
            ],
            address_province: [
                "សូមបញ្ចូលខេត្ត-រាជធានី",
                "Please enter province-capital",
            ],
        };

        function getRequiredMessage(field) {
            const m = labels[field.name];
            if (m) return m[0] + " / " + m[1];

            // បើគ្មានក្នុងបញ្ជី: ប្រើអក្សរខ្មែរពី label របស់ field
            const label = field
                .closest(".form-group")
                ?.querySelector("label .khmer-os");
            if (label) {
                const prefix =
                    field.tagName === "SELECT" ? "សូមជ្រើសរើស" : "សូមបញ្ចូល";
                return prefix + label.textContent.trim();
            }
            return "សូមបំពេញព័ត៌មាននេះ / This field is required";
        }

        function clearError(field) {
            field.classList.remove("input-error");
            const group = field.closest(".form-group");
            if (group) {
                group.querySelectorAll(".error-message").forEach(function (el) {
                    el.remove();
                });
            }
        }

        function showError(field, message) {
            clearError(field);
            field.classList.add("input-error");

            const span = document.createElement("span");
            span.className = "error-message khmer-os";
            span.textContent = message;

            // ដាក់ភ្លាមៗក្រោម input
            field.insertAdjacentElement("afterend", span);
        }

        function validateField(field) {
            const value = (field.value || "").trim();

            if (field.hasAttribute("required") && !value) {
                showError(field, getRequiredMessage(field));
                return false;
            }

            if (field.type === "email" && value && !field.checkValidity()) {
                showError(
                    field,
                    "ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ / Invalid email format",
                );
                return false;
            }

            if (field.type === "number" && value !== "" && Number(value) < 0) {
                showError(
                    field,
                    "ត្រូវតែជាចំនួនគត់ ០ ឬធំជាង / Must be 0 or greater",
                );
                return false;
            }

            if (field.type === "file" && field.files.length > 0) {
                if (!field.files[0].type.startsWith("image/")) {
                    showError(
                        field,
                        "ឯកសារត្រូវតែជារូបភាព / File must be an image",
                    );
                    return false;
                }
            }

            clearError(field);
            return true;
        }

        const fields = form.querySelectorAll("input, select, textarea");

        // Validate ពេលចុច Save
        form.addEventListener("submit", function (e) {
            let firstInvalid = null;

            fields.forEach(function (field) {
                if (!validateField(field) && !firstInvalid) {
                    firstInvalid = field;
                }
            });

            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
                firstInvalid.focus();
            }
        });

        // ពេលកែ field ដែលមាន error: ពិនិត្យម្តងទៀត
        fields.forEach(function (field) {
            const recheck = function () {
                if (field.classList.contains("input-error")) {
                    validateField(field);
                }
            };
            field.addEventListener("input", recheck);
            field.addEventListener("change", recheck);
        });
    }
});

// ======================== Update Information Customer ===================
// 🌟 មុខងារបញ្ជាសកល (Global Event Delegation) ធានាចុចដើរលោតភ្លាមៗ ១០០% ទោះបីជាតារាងលោតតាម AJAX ក៏ដោយ
document.addEventListener("click", function (e) {
    // ស្វែងរកប៊ូតុង Edit តាមរយៈ Class ផ្លូវការ ឬ Class រូបខ្មៅដៃ
    const btn =
        e.target.closest(".btn-trigger-advanced-edit") ||
        e.target.closest(".edit-btn-icon");
    if (!btn) return; // បើមិនមែនចុចចំប៊ូតុង Edit ទេ មិនបាច់ធ្វើការឡើយ

    e.preventDefault();
    e.stopPropagation();

    // ១. ទាញយកទិន្នន័យចេញពី data-* របស់ប៊ូតុងដែលបានចុច
    const id = btn.getAttribute("data-id") || btn.dataset.id;
    const name = btn.getAttribute("data-name") || btn.dataset.name;
    const gender = btn.getAttribute("data-gender") || btn.dataset.gender;
    const dob = btn.getAttribute("data-dob") || btn.dataset.dob;
    const phone = btn.getAttribute("data-phone") || btn.dataset.phone;
    const passport = btn.getAttribute("data-passport") || btn.dataset.passport;
    const email = btn.getAttribute("data-email") || btn.dataset.email;
    const house = btn.getAttribute("data-house") || btn.dataset.house;
    const street = btn.getAttribute("data-street") || btn.dataset.street;
    const village = btn.getAttribute("data-village") || btn.dataset.village;
    const district = btn.getAttribute("data-district") || btn.dataset.district;
    const province = btn.getAttribute("data-province") || btn.dataset.province;
    const gName = btn.getAttribute("data-gname") || btn.dataset.gname;
    const gPhone = btn.getAttribute("data-gphone") || btn.dataset.gphone;
    const pledges = btn.getAttribute("data-pledges") || btn.dataset.pledges;
    const type = btn.getAttribute("data-type") || btn.dataset.type;
    const status = btn.getAttribute("data-status") || btn.dataset.status;
    const notes = btn.getAttribute("data-notes") || btn.dataset.notes;

    const advForm = document.getElementById("advancedUpdateForm");
    if (!advForm) {
        alert("រកមិនឃើញ Form ដែលមាន id='advancedUpdateForm' ឡើយ!");
        return;
    }

    // ២. ប្តូរផ្លូវលីង URL Action ឱ្យរត់ទៅរកផ្លូវកែប្រែក្នុង Controller របស់អ្នក
    advForm.action = "/customer/" + id + "/update";

    // ៣. បាញ់ទិន្នន័យចាស់ចូលទៅក្នុងប្រអប់ Inputs នីមួយៗនៃផ្ទាំង Pop-up ថ្មី
    if (document.getElementById("sep_customer_name"))
        document.getElementById("sep_customer_name").value = name || "";
    if (document.getElementById("sep_gender"))
        document.getElementById("sep_gender").value = gender ? gender : "";
    if (document.getElementById("sep_dob"))
        document.getElementById("sep_dob").value = dob ? dob : "";
    if (document.getElementById("sep_phone_number"))
        document.getElementById("sep_phone_number").value =
            phone && phone !== "null" ? phone : "";
    if (document.getElementById("sep_passport_id"))
        document.getElementById("sep_passport_id").value =
            passport && passport !== "null" ? passport : "";
    if (document.getElementById("sep_email"))
        document.getElementById("sep_email").value =
            email && email !== "null" ? email : "";

    if (document.getElementById("sep_address_house"))
        document.getElementById("sep_address_house").value =
            house && house !== "null" ? house : "";
    if (document.getElementById("sep_address_street"))
        document.getElementById("sep_address_street").value =
            street && street !== "null" ? street : "";
    if (document.getElementById("sep_address_village"))
        document.getElementById("sep_address_village").value =
            village && village !== "null" ? village : "";
    if (document.getElementById("sep_address_district"))
        document.getElementById("sep_address_district").value =
            district && district !== "null" ? district : "";
    if (document.getElementById("sep_address_province"))
        document.getElementById("sep_address_province").value =
            province && province !== "null" ? province : "";

    if (document.getElementById("sep_guarantor_name"))
        document.getElementById("sep_guarantor_name").value =
            gName && gName !== "null" ? gName : "";
    if (document.getElementById("sep_guarantor_phone"))
        document.getElementById("sep_guarantor_phone").value =
            gPhone && gPhone !== "null" ? gPhone : "";

    if (document.getElementById("sep_active_pledges"))
        document.getElementById("sep_active_pledges").value = pledges
            ? pledges
            : 0;
    if (document.getElementById("sep_customer_type"))
        document.getElementById("sep_customer_type").value = type
            ? type
            : "regular";
    if (document.getElementById("sep_customer_status"))
        document.getElementById("sep_customer_status").value = status
            ? status
            : "active";
    if (document.getElementById("sep_notes"))
        document.getElementById("sep_notes").value =
            notes && notes !== "null" ? notes : "";

    // ៤. 🌟 បង្ខំបើកផ្ទាំង Pop-up ឈ្មោះ ID ថ្មី "my_hidden_edit_box" ដោយប្រើសិទ្ធិខ្ពស់ !important
    const targetModal = document.getElementById("my_hidden_edit_box");
    if (targetModal) {
        targetModal.style.setProperty("display", "flex", "important");
    } else {
        alert(
            "រកមិនឃើញផ្ទាំង Pop-up ទាំងមូលដែលមាន id='my_hidden_edit_box' ឡើយ!",
        );
    }
});

// 🌟 មុខងារបិទផ្ទាំង Pop-up ថ្មី ឱ្យត្រូវតាមឈ្មោះ ID សម្ងាត់ថ្មី
function closeMyHiddenEditBox() {
    const targetModal = document.getElementById("my_hidden_edit_box");
    if (targetModal) {
        targetModal.style.setProperty("display", "none", "important");
    }
}

// ======================== Export Report (CSV) ===================
document.addEventListener("DOMContentLoaded", function () {
    const exportBtn = document.getElementById("exportBtn");
    if (!exportBtn) return;

    exportBtn.addEventListener("click", function () {
        const headers = [
            "ID",
            "Name",
            "Gender",
            "Date of Birth",
            "ID/Passport",
            "Phone",
            "Email",
            "House",
            "Street",
            "Village",
            "District",
            "Province",
            "Guarantor",
            "Guarantor Phone",
            "Type",
            "Status",
            "Items",
        ];

        // យកតែជួរដែលកំពុងបង្ហាញ (បន្ទាប់ពីស្វែងរក/ត្រង)
        const rows = Array.from(
            document.querySelectorAll("#customer-table-body tr[data-export]"),
        ).filter((tr) => tr.style.display !== "none");

        if (rows.length === 0) {
            alert("មិនមានទិន្នន័យសម្រាប់ Export / No data to export");
            return;
        }

        const esc = (v) => {
            const s = v === null || v === undefined ? "" : String(v);
            // ការពារ Excel formula injection
            const safe = /^[=+\-@]/.test(s) ? "'" + s : s;
            return '"' + safe.replace(/"/g, '""') + '"';
        };

        const lines = [headers.map(esc).join(",")];
        rows.forEach((tr) => {
            const data = JSON.parse(tr.getAttribute("data-export"));
            lines.push(data.map(esc).join(","));
        });

        // \uFEFF = BOM ឱ្យ Excel អាន Khmer ត្រឹមត្រូវ
        const blob = new Blob(["\uFEFF" + lines.join("\r\n")], {
            type: "text/csv;charset=utf-8;",
        });

        const d = new Date();
        const stamp =
            d.getFullYear() +
            String(d.getMonth() + 1).padStart(2, "0") +
            String(d.getDate()).padStart(2, "0");

        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = "customers_" + stamp + ".csv";
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(a.href);
    });
});

// ======================== View Blacklist ===================
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("blacklistBtn");
    const modal = document.getElementById("blacklistModal");
    const closeBtn = document.getElementById("blacklistClose");
    if (!btn || !modal) return;

    const open = () => modal.classList.add("show");
    const hide = () => modal.classList.remove("show");

    btn.addEventListener("click", open);
    if (closeBtn) closeBtn.addEventListener("click", hide);
    modal.addEventListener("mousedown", (e) => {
        if (e.target === modal) hide();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") hide();
    });
});
