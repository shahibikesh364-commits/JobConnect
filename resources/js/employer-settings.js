const app = document.querySelector(".settings-app");

if (app) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? "";
    const skeleton = document.querySelector(".settings-skeleton");
    const toast = document.querySelector(".settings-toast");
    const tabs = [...app.querySelectorAll("[role='tab']")];
    const panels = [...app.querySelectorAll("[role='tabpanel']")];
    const dirtyForms = new Set();
    let currentUser = {};
    let currentSettings = {};
    let toastTimeout;

    const showToast = (message, kind = "success") => {
        toast.textContent = message;
        toast.dataset.kind = kind;
        toast.hidden = false;
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => { toast.hidden = true; }, 5000);
    };

    const api = async (url, { method = "GET", body, json = true } = {}) => {
        const headers = { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" };
        if (csrf) headers["X-CSRF-TOKEN"] = csrf;
        if (json && body !== undefined) headers["Content-Type"] = "application/json";
        const response = await fetch(url, {
            method,
            headers,
            credentials: "same-origin",
            body: body === undefined ? undefined : (json ? JSON.stringify(body) : body),
        });
        const data = response.headers.get("content-type")?.includes("application/json")
            ? await response.json()
            : {};
        if (response.ok && !response.headers.get("content-type")?.includes("application/json")) {
            throw new Error("The settings service returned an unexpected response. Refresh the page and sign in again.");
        }
        if (!response.ok) {
            const error = new Error(data.message || `Request failed (${response.status}).`);
            error.status = response.status;
            error.errors = data.errors || {};
            throw error;
        }
        return data;
    };

    const setPath = (object, path, value) => {
        const keys = [...path.matchAll(/[^.[\]]+/g)].map((match) => match[0]);
        let cursor = object;
        keys.forEach((key, index) => {
            if (index === keys.length - 1) cursor[key] = value;
            else cursor = cursor[key] ??= {};
        });
    };

    const formDataObject = (form) => {
        const object = {};
        new FormData(form).forEach((value, key) => setPath(object, key, value));
        form.querySelectorAll('input[type="checkbox"][name]').forEach((input) => {
            setPath(object, input.name, input.checked);
        });
        if (form.dataset.formKey === "hiring") {
            object.screening_questions = String(object.screening_questions_text || "")
                .split(/\r?\n/).map((line) => line.trim()).filter(Boolean).slice(0, 10);
            delete object.screening_questions_text;
        }
        return object;
    };

    const clearInlineErrors = (form) => form.querySelectorAll("[data-client-error]").forEach((item) => item.remove());

    const showInlineErrors = (form, errors = {}) => {
        clearInlineErrors(form);
        Object.entries(errors).forEach(([field, messages]) => {
            const selectorName = field.replaceAll(".", "[") + (field.includes(".") ? "]" : "");
            const input = [...form.elements].find((element) => element.name === field || element.name === selectorName);
            if (!input) return;
            const hint = document.createElement("small");
            hint.dataset.clientError = "";
            hint.className = "settings-inline-error";
            hint.setAttribute("role", "alert");
            hint.textContent = Array.isArray(messages) ? messages[0] : String(messages);
            input.closest(".settings-field, .settings-account-extra, div")?.append(hint);
        });
    };

    const markClean = (form) => {
        dirtyForms.delete(form);
        form.dataset.dirty = "false";
        clearInlineErrors(form);
    };

    const fillForm = (form, source) => {
        if (!form || !source) return;
        Object.entries(source).forEach(([key, value]) => {
            if (value === null || value === undefined) return;
            if (key === "screening_questions") {
                const input = form.elements.namedItem("screening_questions_text");
                if (input) input.value = Array.isArray(value) ? value.join("\n") : "";
                return;
            }
            if (typeof value === "object" && !Array.isArray(value)) {
                Object.entries(value).forEach(([childKey, childValue]) => {
                    const input = form.elements.namedItem(`${key}[${childKey}]`);
                    if (input) input.value = childValue ?? "";
                });
                return;
            }
            const input = form.elements.namedItem(key);
            if (!input) return;
            if (input.type === "checkbox") input.checked = Boolean(value);
            else input.value = value;
        });
    };

    const fillSettings = (data) => {
        currentUser = data.user || {};
        currentSettings = data.settings || {};
        const companyForm = app.querySelector('[data-form-key="company"]');
        fillForm(companyForm, currentSettings);
        fillForm(companyForm, { social_links: currentSettings.social_links || {} });

        const accountForm = app.querySelector('[data-form-key="account"]');
        fillForm(accountForm, { ...currentUser, ...(currentSettings || {}) });
        const email = accountForm?.querySelector("[data-account-email]");
        const password = accountForm?.querySelector("[data-email-password]");
        if (email && password) {
            const originalEmail = currentUser.email || "";
            email.dataset.original = originalEmail;
            email.addEventListener("input", () => { password.required = email.value !== originalEmail; });
        }

        const notificationForm = app.querySelector('[data-form-key="notifications"]');
        const preferences = currentSettings.notification_preferences || {};
        Object.entries(preferences).forEach(([category, channels]) => {
            ["email", "in_app"].forEach((channel) => {
                const input = notificationForm?.elements.namedItem(`preferences[${category}][${channel}]`);
                if (input) input.checked = Boolean(channels?.[channel]);
            });
        });

        const hiringForm = app.querySelector('[data-form-key="hiring"]');
        fillForm(hiringForm, currentSettings);
        fillForm(hiringForm, { screening_questions: currentSettings.screening_questions || [] });
        const privacyForm = app.querySelector('[data-form-key="privacy"]');
        fillForm(privacyForm, currentSettings);
        updateLogo(currentSettings.company_logo_url || (currentSettings.company_logo ? `/storage/${currentSettings.company_logo}` : null));
        updateAboutCount();

        const billing = data.billing || {};
        app.querySelector("[data-plan-name]").textContent = billing.plan || "Starter (demo)";
        app.querySelector("[data-jobs-used]").textContent = billing.jobs_used ?? 0;
        app.querySelector("[data-job-limit]").textContent = billing.monthly_job_limit ?? 10;
        app.querySelector("[data-renews-at]").textContent = billing.renews_at || "—";
        renderSessions(data.sessions || []);
        renderTeam(data.team || []);
        [...app.querySelectorAll("[data-settings-form]")].forEach(markClean);
    };

    const activateTab = (tabName, { force = false } = {}) => {
        const currentPanel = app.querySelector('[role="tabpanel"]:not([hidden])');
        const hasDirty = currentPanel && [...currentPanel.querySelectorAll("form")].some((form) => dirtyForms.has(form));
        if (!force && hasDirty && !window.confirm("You have unsaved changes. Discard them and switch sections?")) return false;
        if (hasDirty) currentPanel.querySelectorAll("form").forEach(markClean);
        tabs.forEach((tab) => {
            const selected = tab.dataset.tab === tabName;
            tab.classList.toggle("is-active", selected);
            tab.setAttribute("aria-selected", String(selected));
            tab.tabIndex = selected ? 0 : -1;
        });
        panels.forEach((panel) => { panel.hidden = panel.dataset.panel !== tabName; });
        history.replaceState(null, "", `#${tabName}`);
        return true;
    };

    const updateAboutCount = () => {
        const field = app.querySelector("[data-about-company]");
        const label = app.querySelector("[data-about-count]");
        if (field && label) label.textContent = `${field.value.length} / 1000`;
    };

    let logoObjectUrl;
    const updateLogo = (src) => {
        const preview = app.querySelector("[data-logo-preview]");
        if (!preview) return;
        if (logoObjectUrl && logoObjectUrl !== src) URL.revokeObjectURL(logoObjectUrl);
        logoObjectUrl = src?.startsWith("blob:") ? src : null;
        preview.replaceChildren();
        if (src) {
            const image = document.createElement("img");
            image.src = src;
            image.alt = "Company logo preview";
            preview.append(image);
        } else preview.textContent = "JC";
    };

    const renderSessions = (sessions) => {
        const list = app.querySelector("[data-session-list]");
        if (!list) return;
        list.replaceChildren();
        if (!sessions.length) {
            const empty = document.createElement("p"); empty.className = "settings-muted"; empty.textContent = "No other active sessions."; list.append(empty); return;
        }
        sessions.forEach((session) => {
            const row = document.createElement("div"); row.className = "settings-session-row";
            const detail = document.createElement("span"); detail.textContent = session.device || "Unknown device";
            const time = document.createElement("small"); time.textContent = `${session.current ? "This device · " : ""}${new Date(session.last_active).toLocaleString()}`;
            row.append(detail, time); list.append(row);
        });
    };

    const renderTeam = (members) => {
        const body = app.querySelector("[data-team-list]");
        body.replaceChildren();
        if (!members.length) {
            const row = document.createElement("tr"); const cell = document.createElement("td"); cell.colSpan = 4; cell.className = "settings-muted"; cell.textContent = "No team members yet."; row.append(cell); body.append(row); return;
        }
        members.forEach((member) => {
            const row = document.createElement("tr");
            const identity = document.createElement("td");
            const name = document.createElement("strong"); name.textContent = member.name || member.email;
            const email = document.createElement("small"); email.textContent = member.email;
            identity.append(name, email);
            const roleCell = document.createElement("td");
            const role = document.createElement("select"); role.setAttribute("aria-label", `Role for ${member.email}`);
            ["admin", "recruiter", "viewer"].forEach((value) => { const option = document.createElement("option"); option.value = value; option.textContent = value[0].toUpperCase() + value.slice(1); role.append(option); });
            role.value = member.role;
            role.addEventListener("change", async () => {
                try { await api(`/api/employer/team/${member.id}`, { method: "PATCH", body: { role: role.value } }); showToast("Team role updated."); }
                catch (error) { showToast(error.message, "error"); role.value = member.role; }
            });
            roleCell.append(role);
            const status = document.createElement("td"); const badge = document.createElement("span"); badge.className = `settings-status-badge is-${member.status}`; badge.textContent = member.status; status.append(badge);
            const actions = document.createElement("td"); actions.className = "settings-row-actions";
            if (member.status === "pending") {
                const resend = document.createElement("button"); resend.type = "button"; resend.textContent = "Resend";
                resend.addEventListener("click", async () => { try { const result = await api(`/api/employer/team/${member.id}/resend`, { method: "POST", body: {} }); showToast(result.message); } catch (error) { showToast(error.message, "error"); } });
                actions.append(resend);
            }
            const remove = document.createElement("button"); remove.type = "button"; remove.textContent = "Remove";
            remove.addEventListener("click", async () => {
                if (!window.confirm(`Remove ${member.email} from this team?`)) return;
                try { await api(`/api/employer/team/${member.id}`, { method: "DELETE" }); row.remove(); showToast("Team member removed."); }
                catch (error) { showToast(error.message, "error"); }
            });
            actions.append(remove); row.append(identity, roleCell, status, actions); body.append(row);
        });
    };

    const saveForm = async (form) => {
        if (!form.reportValidity()) return;
        const button = form.querySelector('[type="submit"]');
        const oldText = button?.textContent;
        if (button) { button.disabled = true; button.textContent = "Saving…"; }
        clearInlineErrors(form);
        try {
            const body = formDataObject(form);
            const result = await api(form.dataset.endpoint, { method: form.dataset.method || "PUT", body });
            markClean(form);
            const feedback = form.querySelector(".settings-save-feedback");
            if (feedback) feedback.textContent = result.message || "Saved.";
            if (form.dataset.formKey === "account") {
                currentUser = { ...currentUser, ...(result.user || {}) };
                const password = form.querySelector("[data-email-password]"); if (password) { password.value = ""; password.required = false; }
                const email = form.querySelector("[data-account-email]"); if (email) email.dataset.original = email.value;
            }
            if (form.dataset.formKey === "company" || form.dataset.formKey === "privacy") currentSettings = { ...currentSettings, ...(result.settings || {}) };
            showToast(result.message || "Settings saved.");
        } catch (error) {
            showInlineErrors(form, error.errors);
            showToast(error.message, "error");
        } finally {
            if (button) { button.disabled = false; button.textContent = oldText; }
        }
    };

    app.addEventListener("input", (event) => {
        const form = event.target.closest("form[data-settings-form]");
        if (form) { dirtyForms.add(form); form.dataset.dirty = "true"; }
        if (event.target.matches("[data-about-company]")) updateAboutCount();
        if (event.target.matches("[data-new-password]")) updateStrength(event.target);
    });
    app.addEventListener("change", (event) => {
        const form = event.target.closest("form[data-settings-form]");
        if (form) { dirtyForms.add(form); form.dataset.dirty = "true"; }
    });

    const passwordForm = app.querySelector('[data-form-key="password"]');
    const newPassword = passwordForm?.elements.namedItem("password");
    const confirmPassword = passwordForm?.elements.namedItem("password_confirmation");
    const validatePasswordMatch = () => {
        if (confirmPassword) confirmPassword.setCustomValidity(
            confirmPassword.value && newPassword?.value !== confirmPassword.value ? "Passwords do not match." : "",
        );
    };
    newPassword?.addEventListener("input", validatePasswordMatch);
    confirmPassword?.addEventListener("input", validatePasswordMatch);

    app.addEventListener("submit", async (event) => {
        const form = event.target;
        if (form.matches("[data-settings-form]")) { event.preventDefault(); await saveForm(form); }
        if (form.matches("[data-team-invite]")) {
            event.preventDefault();
            if (!form.reportValidity()) return;
            const button = form.querySelector('[type="submit"]'); button.disabled = true;
            try {
                const body = Object.fromEntries(new FormData(form));
                const result = await api("/api/employer/team", { method: "POST", body });
                form.reset(); showToast(result.message);
                const team = await api("/api/employer/team"); renderTeam(team.members || []);
                if (result.invite_url && !result.message.includes("sent")) showToast(`${result.message} Invite link: ${result.invite_url}`, "error");
            } catch (error) { showInlineErrors(form, error.errors); showToast(error.message, "error"); }
            finally { button.disabled = false; }
        }
        if (form.matches("[data-delete-form]")) {
            event.preventDefault();
            if (!form.reportValidity()) return;
            const button = form.querySelector('[type="submit"]'); if (button) button.disabled = true;
            try { await api("/api/employer/account", { method: "DELETE", body: Object.fromEntries(new FormData(form)) }); window.location.assign("/"); }
            catch (error) { showInlineErrors(form, error.errors); showToast(error.message, "error"); if (button) button.disabled = false; }
        }
    });

    tabs.forEach((tab, index) => {
        tab.addEventListener("click", () => activateTab(tab.dataset.tab));
        tab.addEventListener("keydown", (event) => {
            if (!["ArrowRight", "ArrowLeft", "Home", "End"].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === "Home" ? 0 : event.key === "End" ? tabs.length - 1 : (index + (event.key === "ArrowRight" ? 1 : -1) + tabs.length) % tabs.length;
            tabs[next].focus(); activateTab(tabs[next].dataset.tab);
        });
    });

    app.querySelectorAll("[data-show-password]").forEach((button) => button.addEventListener("click", () => {
        const input = button.closest(".settings-password-input")?.querySelector("input");
        if (!input) return;
        const show = input.type === "password"; input.type = show ? "text" : "password";
        button.textContent = show ? "Hide" : "Show"; button.setAttribute("aria-pressed", String(show));
        button.setAttribute("aria-label", `${show ? "Hide" : "Show"} ${input.name.replaceAll("_", " ")}`);
    }));

    app.querySelector("[data-logo-input]")?.addEventListener("change", async (event) => {
        const file = event.target.files?.[0]; if (!file) return;
        if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size > 2 * 1024 * 1024) { showToast("Choose a JPG, PNG, or WebP image up to 2 MB.", "error"); event.target.value = ""; return; }
        logoObjectUrl = URL.createObjectURL(file); updateLogo(logoObjectUrl);
        const body = new FormData(); body.append("logo", file);
        try { const result = await api("/api/employer/settings/logo", { method: "POST", body, json: false }); currentSettings.company_logo = result.logo_path; currentSettings.company_logo_url = result.logo_url; showToast(result.message); }
        catch (error) { updateLogo(currentSettings.company_logo_url || (currentSettings.company_logo ? `/storage/${currentSettings.company_logo}` : null)); showToast(error.message, "error"); }
        event.target.value = "";
    });

    app.querySelector("[data-remove-logo]")?.addEventListener("click", async () => {
        try { const result = await api("/api/employer/settings/logo", { method: "DELETE", body: {} }); currentSettings.company_logo = null; currentSettings.company_logo_url = null; updateLogo(null); showToast(result.message); }
        catch (error) { showToast(error.message, "error"); }
    });

    app.querySelector("[data-logout-sessions]")?.addEventListener("click", async () => {
        const password = window.prompt("Enter your current password to sign out other devices:");
        if (!password) return;
        try { const result = await api("/api/employer/settings/sessions/logout-others", { method: "POST", body: { current_password: password } }); showToast(result.message); const data = await api("/api/employer/settings/sessions"); renderSessions(data.sessions || []); }
        catch (error) { showToast(error.message, "error"); }
    });

    app.querySelector("[data-deactivate-account]")?.addEventListener("click", async () => {
        if (!window.confirm("Deactivate this account? You will be signed out and will not be able to sign in until support restores access.")) return;
        const current_password = window.prompt("Enter your current password to confirm:");
        if (!current_password) return;
        try { await api("/api/employer/settings/deactivate", { method: "POST", body: { current_password } }); window.location.assign("/login"); }
        catch (error) { showToast(error.message, "error"); }
    });

    const updateStrength = (input) => {
        const value = input.value;
        const score = [value.length >= 8, /[A-Z]/.test(value), /[a-z]/.test(value), /\d/.test(value), /[^A-Za-z0-9]/.test(value)].filter(Boolean).length;
        const bar = app.querySelector("[data-strength-bar]"); const label = app.querySelector("[data-strength-label]");
        if (bar) { bar.style.width = `${score * 20}%`; bar.dataset.score = score; }
        if (label) label.textContent = value ? ["Very weak", "Weak", "Fair", "Good", "Strong", "Very strong"][score] : "Use at least 8 characters.";
    };

    window.addEventListener("beforeunload", (event) => {
        if (!dirtyForms.size) return;
        event.preventDefault(); event.returnValue = "";
    });

    (async () => {
        try {
            const data = await api("/api/employer/settings");
            fillSettings(data);
            const defaultTab = location.hash.slice(1);
            activateTab(panels.some((panel) => panel.dataset.panel === defaultTab) ? defaultTab : "company", { force: true });
            skeleton.hidden = true; app.hidden = false; app.setAttribute("aria-busy", "false");
            try {
                const team = await api("/api/employer/team"); renderTeam(team.members || []);
            } catch (error) {
                if (error.status === 403) {
                    app.querySelector("[data-team-invite]").hidden = true;
                    app.querySelector("[data-team-permission]").hidden = false;
                    app.querySelector("[data-team-list]").closest(".settings-table-wrap").hidden = true;
                }
                else showToast(error.message, "error");
            }
        } catch (error) {
            skeleton.hidden = true; app.hidden = false; app.setAttribute("aria-busy", "false");
            showToast(error.message || "Could not load settings. Refresh to try again.", "error");
        }
    })();
}
