export function houseStepForField(field) {
    if (['latitude', 'longitude'].includes(field)) return 2;
    if (['owner_mode', 'owner_user_id', 'password', 'password_confirmation'].includes(field) || field.startsWith('new_owner_')) return 3;
    return 1;
}

export function hasHouseLocation(latitude, longitude) {
    if (latitude == null || longitude == null) return false;
    if (String(latitude).trim() === '' || String(longitude).trim() === '') return false;
    const lat = Number(latitude);
    const lng = Number(longitude);
    return Number.isFinite(lat) && Number.isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
}

export function initializeHouseWizards() {
    document.querySelectorAll('[data-house-wizard]:not([data-wizard-ready])').forEach(form => {
        form.dataset.wizardReady = 'true';
        form.noValidate = true;
        let step = 1;
        let submitting = false;
        const sections = [...form.querySelectorAll('[data-house-step]')];
        const indicators = [...form.querySelectorAll('[data-step-indicator]')];
        const existingNumbers = JSON.parse(form.dataset.existingNumbers || '{}');
        const field = name => form.elements.namedItem(name);
        const number = field('number');
        const head = field('head_name');
        const picker = form.querySelector('[data-location-picker]');
        const lat = field('latitude');
        const lng = field('longitude');
        const nav = form.querySelector('[data-wizard-nav]');
        const back = form.querySelector('[data-wizard-back]');
        const next = form.querySelector('[data-wizard-next]');
        const actions = form.querySelector('[data-wizard-submit]');
        const locationError = form.querySelector('[data-location-error]');
        const duplicateBox = form.querySelector('[data-duplicate-house]');
        const ownerMode = () => form.querySelector('input[name="owner_mode"]:checked')?.value || 'later';
        const hasLocation = () => hasHouseLocation(lat.value, lng.value);
        const confirmedLocation = () => hasLocation() && picker.dataset.locationConfirmed === 'true';
        const setVisible = (element, visible) => {
            element.hidden = !visible;
            element.style.display = visible ? '' : 'none';
        };
        const revealField = input => {
            let details = input.closest('details');
            while (details) {
                details.open = true;
                details = details.parentElement.closest('details');
            }
            input.focus();
        };
        const updateReview = () => {
            form.querySelector('[data-house-name]').textContent = `Rumah No. ${number.value || '…'}`;
            form.querySelector('[data-review-house]').textContent = `Rumah No. ${number.value || '…'} · ${head.value || 'Kepala keluarga belum diisi'}`;
            form.querySelector('[data-review-location]').textContent = confirmedLocation() ? 'Titik rumah sudah dipilih' : 'Belum dipastikan · dapat disimpan sebagai draf';
            form.querySelector('[data-review-owner]').textContent = ownerMode() === 'new'
                ? `Akun baru: ${field('new_owner_name').value || 'Nama belum diisi'}`
                : ownerMode() === 'existing'
                    ? (field('owner_user_id').selectedOptions[0]?.textContent || 'Belum dipilih')
                    : 'Diatur nanti';
        };
        const showStep = (value, focus = true) => {
            step = Math.max(1, Math.min(3, value));
            sections.forEach(section => setVisible(section, Number(section.dataset.houseStep) === step));
            indicators.forEach(indicator => {
                if (Number(indicator.dataset.stepIndicator) === step) indicator.setAttribute('aria-current', 'step');
                else indicator.removeAttribute('aria-current');
                indicator.classList.toggle('text-primary', Number(indicator.dataset.stepIndicator) === step);
            });
            setVisible(nav, true);
            setVisible(back, step > 1);
            setVisible(next, step < 3);
            setVisible(actions, step === 3);
            form.querySelector('[data-step-status]').textContent = `Langkah ${step} dari 3 · ${['Data rumah', 'Lokasi', 'Pengelola'][step - 1]}`;
            if (step === 2) picker.dispatchEvent(new Event('location-picker:shown'));
            updateReview();
            if (focus) sections[step - 1].querySelector('h2').focus();
        };
        const checkDuplicate = () => {
            const url = existingNumbers[Number(number.value)];
            const duplicate = Boolean(number.value && url);
            setVisible(duplicateBox, duplicate);
            number.setCustomValidity(duplicate ? `Rumah No. ${Number(number.value)} sudah terdaftar. Buka rumah tersebut atau gunakan nomor lain.` : '');
            number.setAttribute('aria-invalid', duplicate ? 'true' : 'false');
            if (duplicate) {
                form.querySelector('[data-duplicate-message]').textContent = `Rumah No. ${Number(number.value)} sudah terdaftar.`;
                form.querySelector('[data-duplicate-link]').href = url;
            }
        };
        const updateOwnerFields = () => {
            const mode = ownerMode();
            form.querySelectorAll('[data-owner-fields]').forEach(group => {
                const active = group.dataset.ownerFields === mode;
                setVisible(group, active);
                group.querySelectorAll('input, select').forEach(input => {
                    input.disabled = !active;
                    input.required = active && input.name !== 'new_owner_phone';
                });
            });
            updateReview();
        };
        const validateSection = value => {
            if (value === 1) checkDuplicate();
            const invalid = [...sections[value - 1].querySelectorAll('input, select, textarea')]
                .find(input => !input.disabled && !input.checkValidity());
            if (invalid) {
                showStep(value);
                revealField(invalid);
                invalid.reportValidity();
                return false;
            }
            if (value === 2 && (lat.value || lng.value) && !confirmedLocation()) {
                locationError.textContent = 'Pastikan titik rumah di peta, lalu tekan Gunakan titik ini.';
                setVisible(locationError, true);
                showStep(2);
                picker.querySelector('[data-confirm-point]').focus();
                return false;
            }
            return true;
        };
        next.addEventListener('click', () => {
            if (validateSection(step)) showStep(step + 1);
        });
        back.addEventListener('click', () => showStep(step - 1));
        form.querySelectorAll('[data-go-step]').forEach(button => button.addEventListener('click', () => showStep(Number(button.dataset.goStep))));
        form.querySelector('[data-edit-owner]').addEventListener('click', () => form.querySelector('input[name="owner_mode"]:checked').focus());
        form.querySelectorAll('input[name="owner_mode"]').forEach(input => input.addEventListener('change', updateOwnerFields));
        form.addEventListener('input', () => {
            field('password_confirmation').setCustomValidity('');
            updateReview();
        });
        number.addEventListener('blur', checkDuplicate);
        number.addEventListener('input', () => {
            number.setCustomValidity('');
            number.removeAttribute('aria-invalid');
            setVisible(duplicateBox, false);
        });
        picker.addEventListener('location-change', () => {
            setVisible(locationError, false);
            updateReview();
        });
        field('owner_user_id').addEventListener('change', updateReview);
        form.querySelector('[data-show-password]').addEventListener('click', event => {
            const visible = field('password').type === 'password';
            field('password').type = visible ? 'text' : 'password';
            field('password_confirmation').type = visible ? 'text' : 'password';
            event.currentTarget.setAttribute('aria-pressed', String(visible));
            event.currentTarget.textContent = visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
        });
        form.addEventListener('submit', event => {
            if (submitting) {
                event.preventDefault();
                return;
            }
            if (step < 3) {
                event.preventDefault();
                if (validateSection(step)) showStep(step + 1);
                return;
            }
            if (ownerMode() === 'new' && field('password').value !== field('password_confirmation').value) {
                field('password_confirmation').setCustomValidity('Kata sandi belum sama. Ketik ulang kata sandi yang sama.');
            }
            if (![1, 2, 3].every(validateSection)) {
                event.preventDefault();
                return;
            }
            const publish = event.submitter?.hasAttribute('data-publish') || false;
            if (publish && !confirmedLocation()) {
                event.preventDefault();
                showStep(2);
                locationError.textContent = 'Pilih lokasi rumah sebelum menampilkannya untuk pengunjung. Anda juga dapat menyimpan sebagai draf.';
                setVisible(locationError, true);
                return;
            }
            form.querySelector('[data-publish-value]').value = publish ? '1' : '0';
            submitting = true;
            form.querySelectorAll('button').forEach(button => button.disabled = true);
            form.querySelector('[data-save-status]').textContent = 'Menyimpan rumah…';
        });
        updateOwnerFields();
        const errorFields = JSON.parse(form.dataset.errorFields || '[]');
        showStep(errorFields.length ? houseStepForField(errorFields[0]) : 1, false);
        if (errorFields.length) {
            const input = field(errorFields[0].replace(/\.([^.[\]]+)/g, '[$1]'));
            if (input?.focus) {
                input.setAttribute('aria-invalid', 'true');
                revealField(input);
            }
        }
    });
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', initializeHouseWizards);
    document.addEventListener('livewire:navigated', initializeHouseWizards);
    if (document.readyState !== 'loading') initializeHouseWizards();
}
