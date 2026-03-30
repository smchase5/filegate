(function () {
    function ready(fn) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", fn);
            return;
        }

        fn();
    }

    ready(function () {
        var config = window.filegateAdmin || {};
        var strings = config.strings || {};
        var reservedExtensions = config.reservedExtensions || {};
        var presets = config.presets || {};

        var form = document.getElementById("filegate-settings-form");
        if (!form) {
            return;
        }

        var tbody = document.getElementById("filegate-custom-types");
        var addRowButton = document.getElementById("filegate-add-row");
        var presetButtons = form.querySelectorAll("[data-filegate-preset]");
        var resetButton = document.getElementById("filegate-reset-defaults");
        var actionInput = document.getElementById("filegate-action");
        var onboardingPanel = form.querySelector(".filegate-onboarding");
        if (!tbody) {
            return;
        }

        var toastStack = document.getElementById("filegate-toast-stack");
        var saveIndicator = document.getElementById("filegate-save-indicator");
        var submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
        var defaultSubmitLabel = submitButton
            ? (submitButton.tagName === "INPUT" ? submitButton.value : submitButton.textContent)
            : "Save Changes";
        var isSaving = false;
        var hasUnsavedCustomChanges = false;

        function getBuiltinCheckbox(key) {
            return form.querySelector('input[name="filegate_settings[enabled_types][' + key + ']"]');
        }

        function getSvgCheckbox() {
            return form.querySelector('input[name="filegate_settings[svg_enabled]"]');
        }

        function clearEmptyState() {
            var empty = tbody.querySelector(".filegate-empty-state");

            if (empty) {
                empty.remove();
            }
        }

        function createRow(index) {
            var row = document.createElement("tr");
            row.className = "filegate-custom-row";
            row.innerHTML =
                '<td>' +
                '<input type="text" name="filegate_settings[custom_types][' + index + '][ext]" class="regular-text filegate-ext-input" placeholder="dwg">' +
                '<p class="filegate-inline-error" hidden></p>' +
                '</td>' +
                '<td>' +
                '<input type="text" name="filegate_settings[custom_types][' + index + '][mime]" class="regular-text filegate-mime-input" placeholder="image/vnd.dwg">' +
                '<p class="filegate-inline-error" hidden></p>' +
                '</td>' +
                '<td>' +
                '<input type="text" name="filegate_settings[custom_types][' + index + '][label]" class="regular-text" placeholder="Optional label">' +
                '</td>' +
                '<td>' +
                '<button type="button" class="button-link-delete filegate-remove-row">' + (strings.removeRow || "Remove") + "</button>" +
                "</td>";
            return row;
        }

        function nextIndex() {
            var highest = -1;

            tbody.querySelectorAll(".filegate-custom-row input.filegate-ext-input").forEach(function (input) {
                var match = input.name.match(/\[custom_types]\[(\d+)]\[ext]$/);

                if (match) {
                    highest = Math.max(highest, parseInt(match[1], 10));
                }
            });

            return highest + 1;
        }

        function normalizeExtension(value) {
            return value.trim().replace(/^\./, "").toLowerCase();
        }

        function isValidExtension(value) {
            return /^[a-z0-9]+(?:[._-][a-z0-9]+)*$/.test(value);
        }

        function isValidMime(value) {
            return /^[a-z0-9!#$&^_.+-]+\/[a-z0-9!#$&^_.+-]+$/i.test(value);
        }

        function setError(input, message) {
            var cell = input.closest("td");
            var error = cell ? cell.querySelector(".filegate-inline-error") : null;
            var row = input.closest(".filegate-custom-row");

            if (!error) {
                return;
            }

            if (message) {
                error.textContent = message;
                error.hidden = false;
                row.classList.add("has-error");
            } else {
                error.textContent = "";
                error.hidden = true;
                if (row && !row.querySelector(".filegate-inline-error:not([hidden])")) {
                    row.classList.remove("has-error");
                }
            }
        }

        function validateRows() {
            var seen = {};
            var valid = true;
            var rows = tbody.querySelectorAll(".filegate-custom-row");

            rows.forEach(function (row) {
                row.classList.remove("has-error");
                row.querySelectorAll(".filegate-inline-error").forEach(function (error) {
                    error.hidden = true;
                    error.textContent = "";
                });
            });

            rows.forEach(function (row) {
                var extInput = row.querySelector(".filegate-ext-input");
                var mimeInput = row.querySelector(".filegate-mime-input");
                var ext = normalizeExtension(extInput.value);
                var mime = mimeInput.value.trim();
                var blank = !ext && !mime && !row.querySelector('input[name$="[label]"]').value.trim();

                extInput.value = ext;

                if (blank) {
                    return;
                }

                if (!isValidExtension(ext)) {
                    setError(extInput, strings.invalidExtension || "Invalid extension.");
                    valid = false;
                    return;
                }

                if (reservedExtensions[ext] || seen[ext]) {
                    setError(extInput, strings.duplicateExt || "Duplicate extension.");
                    valid = false;
                }

                seen[ext] = true;

                if (!isValidMime(mime)) {
                    setError(mimeInput, strings.invalidMime || "Invalid MIME type.");
                    valid = false;
                }
            });

            return valid;
        }

        function setSaveIndicator(state, text) {
            if (!saveIndicator) {
                return;
            }

            saveIndicator.classList.remove("is-saving", "is-unsaved", "is-saved");
            saveIndicator.classList.add("is-" + state);
            saveIndicator.textContent = text;
        }

        function showToast(message, type, details) {
            if (!toastStack || !message) {
                return;
            }

            var toast = document.createElement("div");
            toast.className = "filegate-toast is-" + (type || "success");

            var detailMarkup = "";

            if (details && details.length) {
                detailMarkup =
                    '<ul class="filegate-toast__details">' +
                    details.map(function (detail) {
                        return "<li>" + escapeHtml(detail) + "</li>";
                    }).join("") +
                    "</ul>";
            }

            toast.innerHTML =
                '<div class="filegate-toast__body">' +
                '<strong class="filegate-toast__title">' + escapeHtml(message) + "</strong>" +
                detailMarkup +
                "</div>" +
                '<button type="button" class="filegate-toast__close" aria-label="Dismiss notification">&times;</button>';

            toastStack.appendChild(toast);

            window.setTimeout(function () {
                toast.classList.add("is-visible");
            }, 10);

            var removeToast = function () {
                toast.classList.remove("is-visible");
                window.setTimeout(function () {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 180);
            };

            toast.querySelector(".filegate-toast__close").addEventListener("click", removeToast);
            window.setTimeout(removeToast, 5200);
        }

        function setSavingState(saving) {
            isSaving = saving;

            if (submitButton) {
                if (submitButton.tagName === "INPUT") {
                    submitButton.value = saving ? (strings.saving || "Saving...") : defaultSubmitLabel;
                } else {
                    submitButton.textContent = saving ? (strings.saving || "Saving...") : defaultSubmitLabel;
                }

                submitButton.disabled = saving;
            }

            if (resetButton) {
                resetButton.disabled = saving;
            }

            if (saving) {
                setSaveIndicator("saving", strings.savingStatus || "Saving changes...");
            }
        }

        function saveSettings(options) {
            var formData;
            var saveOptions = options || {};

            if (!config.ajaxUrl || !config.saveNonce) {
                form.submit();
                return;
            }

            formData = new window.FormData(form);
            formData.set("action", "filegate_save_settings");
            formData.set("nonce", config.saveNonce);

            setSavingState(true);

            window.fetch(config.ajaxUrl, {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (payload) {
                    var details = [];

                    if (!payload || !payload.success) {
                        throw new Error(payload && payload.data && payload.data.message ? payload.data.message : (strings.saveFailed || "Save failed."));
                    }

                    if (payload.data && payload.data.summary) {
                        details = details.concat(payload.data.summary);
                    }

                    if (payload.data && payload.data.notices) {
                        details = details.concat(payload.data.notices.map(function (notice) {
                            return notice.message;
                        }).filter(Boolean));
                    }

                    actionInput.value = "";
                    hasUnsavedCustomChanges = false;
                    setSaveIndicator("saved", strings.allSaved || "All changes saved");

                    if (payload.data && payload.data.settings) {
                        config.currentSettings = payload.data.settings;
                    }

                    if (onboardingPanel && payload.data && payload.data.settings) {
                        var hasEnabledTypes = false;

                        Object.keys(payload.data.settings.enabled_types || {}).forEach(function (key) {
                            if (payload.data.settings.enabled_types[key]) {
                                hasEnabledTypes = true;
                            }
                        });

                        if (hasEnabledTypes || payload.data.settings.svg_enabled || (payload.data.settings.custom_types || []).length) {
                            onboardingPanel.hidden = true;
                        }
                    }

                    if (saveOptions.toast === false && payload.data.type === "warning" && details.length) {
                        showToast(
                            payload.data.message || strings.savedWithNotes || "Settings saved with a few notes.",
                            payload.data.type || "warning",
                            details
                        );
                    } else if (saveOptions.toast !== false) {
                        showToast(
                            payload.data.message || strings.saved || "Settings saved.",
                            payload.data.type || "success",
                            details
                        );
                    }

                    if (payload.data.action === "reset") {
                        window.setTimeout(function () {
                            window.location.reload();
                        }, 700);
                    }
                })
                .catch(function (error) {
                    setSaveIndicator("unsaved", strings.unsavedChanges || "Unsaved changes");
                    showToast(error.message || strings.networkError || "A network error prevented FileGate from saving.", "error");
                })
                .finally(function () {
                    setSavingState(false);
                });
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        addRowButton.addEventListener("click", function () {
            clearEmptyState();
            tbody.appendChild(createRow(nextIndex()));
            hasUnsavedCustomChanges = true;
            setSaveIndicator("unsaved", strings.unsavedChanges || "Unsaved changes");
        });

        tbody.addEventListener("click", function (event) {
            if (!event.target.classList.contains("filegate-remove-row")) {
                return;
            }

            var row = event.target.closest(".filegate-custom-row");

            if (row) {
                row.remove();
            }

            if (!tbody.querySelector(".filegate-custom-row")) {
                var empty = document.createElement("tr");
                empty.className = "filegate-empty-state";
                empty.innerHTML = '<td colspan="4">No custom types yet. Add one when you need a format outside the built-in list.</td>';
                tbody.appendChild(empty);
            }

            hasUnsavedCustomChanges = true;
            setSaveIndicator("unsaved", strings.unsavedChanges || "Unsaved changes");
        });

        tbody.addEventListener("input", function (event) {
            if (
                event.target.closest(".filegate-custom-row")
            ) {
                validateRows();
                hasUnsavedCustomChanges = true;
                setSaveIndicator("unsaved", strings.unsavedChanges || "Unsaved changes");
            }
        });

        function applyPreset(presetKey) {
            var preset = presets[presetKey];

            if (!preset) {
                return;
            }

            form.querySelectorAll('input[name^="filegate_settings[enabled_types]"]').forEach(function (checkbox) {
                var match = checkbox.name.match(/\[enabled_types]\[([^\]]+)]$/);
                var key = match ? match[1] : "";

                if (!key) {
                    return;
                }

                checkbox.checked = !!(preset.enabled_keys && preset.enabled_keys.indexOf(key) !== -1);
            });

            if (getSvgCheckbox()) {
                getSvgCheckbox().checked = !!preset.svg_enabled;
            }

            syncCardState();
            setSaveIndicator("unsaved", strings.unsavedChanges || "Unsaved changes");
            saveSettings({ toast: true });
        }

        presetButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                applyPreset(button.getAttribute("data-filegate-preset"));
            });
        });

        resetButton.addEventListener("click", function () {
            if (!window.confirm(strings.resetConfirm || "Reset settings?")) {
                return;
            }

            actionInput.value = "reset";

            if (typeof form.requestSubmit === "function") {
                form.requestSubmit();
                return;
            }

            form.dispatchEvent(new window.Event("submit", { cancelable: true, bubbles: true }));
        });

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            if (isSaving) {
                return;
            }

            if (actionInput.value !== "reset") {
                actionInput.value = "";
            }

            if (!validateRows()) {
                showToast(strings.saveFailed || "FileGate could not save your changes.", "error");
                return;
            }

            saveSettings({ toast: true });
        });

        function syncCardState() {
            form.querySelectorAll(".filegate-card").forEach(function (card) {
                var checkbox = card.querySelector('input[type="checkbox"]');

                if (!checkbox) {
                    return;
                }

                card.classList.toggle("is-enabled", checkbox.checked);
            });
        }

        form.addEventListener("change", function (event) {
            if (event.target.matches('.filegate-card input[type="checkbox"]')) {
                syncCardState();

                if (!hasUnsavedCustomChanges && !isSaving) {
                    saveSettings({ toast: false });
                    return;
                }

                setSaveIndicator("unsaved", strings.unsavedChanges || "Unsaved changes");
            }
        });

        window.addEventListener("beforeunload", function (event) {
            if (!hasUnsavedCustomChanges || isSaving) {
                return;
            }

            event.preventDefault();
            event.returnValue = strings.leaveWarning || "You have unsaved FileGate changes.";
            return event.returnValue;
        });

        syncCardState();
        setSaveIndicator("saved", strings.allSaved || "All changes saved");
    });
})();
