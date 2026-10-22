(() => {
    // ការពារចុច Save ស្ទួន
    document.querySelectorAll("form.js-save").forEach((f) =>
        f.addEventListener("submit", () => {
            const b = f.querySelector(".st-card-foot .btn");
            if (b) b.disabled = true;
        }),
    );

    // ឧទាហរណ៍ Interest
    const rate = document.getElementById("in_rate"),
        max = document.getElementById("in_max"),
        ex = document.getElementById("in_example");
    const calcIn = () => {
        const loan = (1000 * (+max.value || 0)) / 100,
            mo = (loan * (+rate.value || 0)) / 100;
        ex.innerHTML =
            "ឧទាហរណ៍៖ ទំនិញ <b>$1,000</b> → ខ្ចីបាន <b>$" +
            loan.toFixed(2) +
            "</b> → ការប្រាក់ក្នុងមួយខែ <b>$" +
            mo.toFixed(2) +
            "</b>";
    };
    if (rate && max) {
        rate.oninput = max.oninput = calcIn;
        calcIn();
    }

    // ឧទាហរណ៍ Currency
    const kr = document.getElementById("cu_rate"),
        ce = document.getElementById("cu_example");
    const calcCu = () => {
        ce.innerHTML =
            "ឧទាហរណ៍៖ <b>$100</b> = <b>" +
            Math.round(100 * (+kr.value || 0)).toLocaleString() +
            " ៛</b>";
    };
    if (kr) {
        kr.oninput = calcCu;
        calcCu();
    }
})();
