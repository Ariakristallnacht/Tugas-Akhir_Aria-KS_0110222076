(() => {
    (function () {
        "use strict";

        const form = document.querySelector('form[action$="/login"]');

        if (! form) {
            return;
        }

        const submitButton = form.querySelector(".login-button");
        const passwordInput = form.querySelector('input[name="password"]');
        const passwordToggle = form.querySelector(".password-toggle");
        const passwordToggleShowIcon = form.querySelector(".password-toggle-icon-show");
        const passwordToggleHideIcon = form.querySelector(".password-toggle-icon-hide");
        const syncInputValue = (input, { trim = false } = {}) => {
            if (! input) {
                return;
            }

            // Browser autofill can update the live value without firing input events.
            if (trim) {
                input.value = input.value.trim();
            }

            input.setAttribute("value", input.value);
            input.dispatchEvent(new Event("input", { bubbles: true }));
            input.dispatchEvent(new Event("change", { bubbles: true }));
        };

        let isSubmitting = false;

        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener("click", () => {
                const isPasswordVisible = passwordInput.type === "text";

                passwordInput.type = isPasswordVisible ? "password" : "text";
                passwordToggle.setAttribute("aria-pressed", String(! isPasswordVisible));
                passwordToggle.setAttribute(
                    "aria-label",
                    isPasswordVisible ? "Tampilkan password" : "Sembunyikan password",
                );

                passwordToggleShowIcon?.classList.toggle("hidden", ! isPasswordVisible);
                passwordToggleHideIcon?.classList.toggle("hidden", isPasswordVisible);
            });
        }

        form.addEventListener("submit", (event) => {
            event.preventDefault();

            if (isSubmitting) {
                return;
            }

            const emailInput = form.querySelector('input[name="email"]');
            syncInputValue(emailInput, { trim: true });
            syncInputValue(passwordInput);

            if (! form.reportValidity()) {
                return;
            }

            isSubmitting = true;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = "Logging In...";
            }

            HTMLFormElement.prototype.submit.call(form);
        });
    })();
})();
