import '../css/register.css';

(function () {
    'use strict';

    const form = document.getElementById('register-form');

    if (!form) {
        return;
    }

    const normalize = function (value) {
        return (value || '').toString().toLowerCase().trim();
    };

    const todayIso = new Date().toISOString().slice(0, 10);

    // ---------- Dependent select wilayah (endpoint dan error handling dipertahankan) ----------
    const provinceSelect = document.getElementById('provinsi_code');
    const citySelect = document.getElementById('kota_code');
    const loadingHint = document.getElementById('kota_loading');
    const errorHint = document.getElementById('kota_error');
    const preselectedCity = JSON.parse(document.getElementById('preselected-city')?.textContent || '""');

    // ---------- Combobox searchable ringan (tanpa library) ----------
    const comboboxes = new Map();

    const buildCombobox = function (select) {
        if (!select || comboboxes.has(select.id)) {
            return comboboxes.get(select?.id);
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'search-combobox';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control search-combobox-input';
        input.id = select.id + '_search';
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-controls', select.id + '_listbox');
        input.setAttribute('aria-autocomplete', 'list');
        input.placeholder = select.dataset.placeholder || 'Ketik untuk mencari…';
        input.disabled = select.disabled;

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'search-combobox-toggle';
        toggle.setAttribute('aria-label', 'Buka daftar pilihan');
        toggle.setAttribute('aria-controls', input.id);
        toggle.innerHTML = '<i class="ti ti-chevron-down" aria-hidden="true"></i>';

        const clear = document.createElement('button');
        clear.type = 'button';
        clear.className = 'search-combobox-clear';
        clear.setAttribute('aria-label', 'Hapus pilihan');
        clear.innerHTML = '<i class="ti ti-x" aria-hidden="true"></i>';

        const list = document.createElement('ul');
        list.className = 'search-combobox-list d-none';
        list.id = select.id + '_listbox';
        list.setAttribute('role', 'listbox');

        const state = {
            activeIndex: -1,
            open: false,
            filtered: [],
        };

        const closeList = function () {
            state.open = false;
            list.classList.add('d-none');
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            state.activeIndex = -1;
        };

        const optionNodes = function () {
            return Array.from(list.querySelectorAll('[role="option"]'));
        };

        const render = function (filterText) {
            const query = normalize(filterText);
            const options = Array.from(select.options).filter((option) => option.value !== '');
            state.filtered = options.filter((option) => normalize(option.textContent).includes(query));

            list.innerHTML = '';

            if (state.filtered.length === 0) {
                const empty = document.createElement('li');
                empty.className = 'search-combobox-empty';
                empty.textContent = 'Tidak ada hasil.';
                list.appendChild(empty);
                return;
            }

            state.filtered.forEach(function (option, index) {
                const item = document.createElement('li');
                item.className = 'search-combobox-option';
                item.id = select.id + '_option_' + index;
                item.setAttribute('role', 'option');
                item.dataset.value = option.value;
                item.dataset.label = option.textContent.trim();
                item.setAttribute('aria-selected', option.value === select.value ? 'true' : 'false');
                item.textContent = option.textContent.trim();
                item.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    selectOption(item);
                });
                list.appendChild(item);
            });
        };

        const openList = function () {
            if (input.disabled) {
                return;
            }
            render(input.dataset.filter === '1' ? input.value : '');
            state.open = true;
            list.classList.remove('d-none');
            input.setAttribute('aria-expanded', 'true');
        };

        const setActive = function (index) {
            const nodes = optionNodes();
            if (nodes.length === 0) {
                state.activeIndex = -1;
                return;
            }
            state.activeIndex = (index + nodes.length) % nodes.length;
            nodes.forEach(function (node, i) {
                node.classList.toggle('is-active', i === state.activeIndex);
                node.setAttribute('aria-selected', i === state.activeIndex ? 'true' : 'false');
            });
            input.setAttribute('aria-activedescendant', nodes[state.activeIndex].id);
            nodes[state.activeIndex].scrollIntoView({ block: 'nearest' });
        };

        const notifyChange = function () {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const selectOption = function (item) {
            select.value = item.dataset.value || '';
            input.value = item.dataset.label || '';
            input.dataset.filter = '0';
            input.classList.remove('is-invalid');
            clear.style.display = 'inline-flex';
            closeList();
            notifyChange();
        };

        const resetInput = function () {
            input.value = '';
            input.dataset.filter = '0';
            clear.style.display = 'none';
        };

        input.addEventListener('focus', function () {
            openList();
            input.select();
            input.dataset.filter = '0';
        });

        input.addEventListener('input', function () {
            input.dataset.filter = '1';
            select.value = '';
            clear.style.display = 'none';
            render(input.value);
            list.classList.remove('d-none');
            state.open = true;
            input.setAttribute('aria-expanded', 'true');
            state.activeIndex = -1;
        });

        input.addEventListener('keydown', function (event) {
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    if (!state.open) {
                        openList();
                    }
                    setActive(state.activeIndex + 1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    if (!state.open) {
                        openList();
                    }
                    setActive(state.activeIndex - 1);
                    break;
                case 'Enter':
                    event.preventDefault();
                    if (state.open && state.activeIndex >= 0) {
                        const nodes = optionNodes();
                        if (nodes[state.activeIndex]) {
                            selectOption(nodes[state.activeIndex]);
                        }
                    }
                    break;
                case 'Escape':
                    event.preventDefault();
                    closeList();
                    break;
                case 'Tab':
                    closeList();
                    break;
            }
        });

        input.addEventListener('blur', function () {
            window.setTimeout(function () {
                if (select.value === '' && input.value.trim() !== '') {
                    input.classList.add('is-invalid');
                }
            }, 120);
        });

        toggle.addEventListener('click', function () {
            if (state.open) {
                closeList();
            } else {
                openList();
                input.focus();
            }
        });

        clear.addEventListener('click', function () {
            select.value = '';
            resetInput();
            input.classList.remove('is-invalid');
            input.focus();
            notifyChange();
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                closeList();
            }
        });

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(input);
        wrapper.appendChild(clear);
        wrapper.appendChild(toggle);
        wrapper.appendChild(list);
        select.classList.add('d-none');
        select.setAttribute('aria-hidden', 'true');
        select.tabIndex = -1;

        const refresh = function () {
            const selected = select.options[select.selectedIndex];
            if (selected && selected.value !== '') {
                input.value = selected.textContent.trim();
                clear.style.display = 'inline-flex';
            } else {
                resetInput();
            }
            input.disabled = select.disabled;
            if (!select.disabled && state.open) {
                render(input.dataset.filter === '1' ? input.value : '');
            }
        };

        comboboxes.set(select.id, { refresh });
        refresh();

        return comboboxes.get(select.id);
    };

    const refreshCombobox = function (select) {
        const combobox = comboboxes.get(select?.id);
        if (combobox) {
            combobox.refresh();
        }
    };

    const setLoading = function (isLoading) {
        if (loadingHint) {
            loadingHint.classList.toggle('d-none', !isLoading);
        }
        if (citySelect) {
            citySelect.disabled = isLoading;
            refreshCombobox(citySelect);
        }
    };

    const setError = function (message) {
        if (errorHint) {
            errorHint.textContent = message || '';
            errorHint.classList.toggle('d-block', Boolean(message));
        }
        if (citySelect) {
            citySelect.classList.toggle('is-invalid', Boolean(message));
            const cityInput = document.getElementById(citySelect.id + '_search');
            if (cityInput) {
                cityInput.classList.toggle('is-invalid', Boolean(message));
            }
        }
    };

    const loadCities = async function (provinceCode, preselect) {
        if (!provinceCode) {
            citySelect.innerHTML = '<option value="">Pilih provinsi terlebih dahulu</option>';
            citySelect.disabled = true;
            setError('');
            refreshCombobox(citySelect);
            return;
        }

        setLoading(true);
        setError('');

        try {
            const url = '/wilayah/provinsi/' + encodeURIComponent(provinceCode) + '/kota';
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Gagal memuat daftar kota (HTTP ' + response.status + ').');
            }

            const cities = await response.json();

            citySelect.innerHTML = '<option value="">Pilih kota/kabupaten</option>';
            cities.forEach(function (city) {
                const option = document.createElement('option');
                option.value = city.code;
                option.textContent = city.nama;
                if (preselect && city.code === preselect) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });

            citySelect.disabled = false;
            setError('');
        } catch (err) {
            citySelect.innerHTML = '<option value="">Pilih kota/kabupaten</option>';
            citySelect.disabled = true;
            setError(err.message || 'Gagal memuat kota. Coba lagi.');
        } finally {
            setLoading(false);
            refreshCombobox(citySelect);
        }
    };

    const provinceCombobox = buildCombobox(provinceSelect);
    buildCombobox(citySelect);

    if (provinceSelect) {
        provinceSelect.addEventListener('change', function () {
            if (citySelect) {
                citySelect.innerHTML = '<option value="">Memuat daftar kota…</option>';
                citySelect.value = '';
                refreshCombobox(citySelect);
            }
            loadCities(this.value, '');
        });

        if (provinceSelect.value) {
            loadCities(provinceSelect.value, preselectedCity);
        }
    }

    if (citySelect) {
        citySelect.addEventListener('change', function () {
            setError('');
            refreshCombobox(citySelect);
        });
    }

    // ---------- Proteksi input NIK & penghitung ----------
    const nikInput = document.getElementById('nik');
    const nikCounter = document.getElementById('nik-counter');

    const countNik = function () {
        const digits = (nikInput.value || '').replace(/\D+/g, '').slice(0, 16);
        if (digits !== nikInput.value) {
            nikInput.value = digits;
        }
        if (nikCounter) {
            nikCounter.textContent = digits.length + '/16';
        }
    };

    if (nikInput) {
        nikInput.addEventListener('input', countNik);
        countNik();
    }

    // ---------- Format & validasi nomor HP Indonesia ----------
    const phoneInput = document.getElementById('phone');
    const phoneHint = document.getElementById('phone-error');
    const phonePattern = /^(?:\+62|62|0)8\d{7,12}$/;

    const normalizePhone = function (value) {
        return (value || '').replace(/[\s\-().]/g, '');
    };

    const validatePhone = function () {
        if (!phoneInput) {
            return true;
        }
        const valid = phonePattern.test(normalizePhone(phoneInput.value));
        phoneInput.classList.toggle('is-invalid', !valid && phoneInput.value.trim() !== '');
        if (phoneHint) {
            phoneHint.classList.toggle('d-block', !valid && phoneInput.value.trim() !== '');
        }
        return valid;
    };

    if (phoneInput) {
        phoneInput.addEventListener('blur', validatePhone);
        phoneInput.addEventListener('input', function () {
            if (phoneInput.classList.contains('is-invalid')) {
                validatePhone();
            }
        });
    }

    // ---------- Toggle tampilkan kata sandi ----------
    const togglePassword = function (buttonId, inputId, iconId) {
        const button = document.getElementById(buttonId);
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (!button || !input || !icon) {
            return;
        }

        button.addEventListener('click', function () {
            const shown = input.type === 'text';
            input.type = shown ? 'password' : 'text';
            button.setAttribute('aria-pressed', String(!shown));
            icon.classList.toggle('ti-eye', shown);
            icon.classList.toggle('ti-eye-off', !shown);
        });
    };

    togglePassword('toggle-password', 'password', 'password-eye');
    togglePassword('toggle-password-confirmation', 'password_confirmation', 'password-confirmation-eye');

    // ---------- Indikator kekuatan kata sandi ----------
    const passwordInput = document.getElementById('password');
    const strength = document.getElementById('password-strength');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthLabel = document.getElementById('password-strength-label');
    const strengthNames = ['Lemah', 'Lemah', 'Sedang', 'Sedang', 'Kuat'];

    const scorePassword = function (value) {
        let score = 0;
        if (value.length >= 8) {
            score += 1;
        }
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) {
            score += 1;
        }
        if (/\d/.test(value)) {
            score += 1;
        }
        if (/[^A-Za-z0-9]/.test(value) || value.length >= 12) {
            score += 1;
        }
        return Math.min(score, 4);
    };

    if (passwordInput && strength) {
        passwordInput.addEventListener('input', function () {
            const value = passwordInput.value;
            const score = value === '' ? 0 : Math.max(1, scorePassword(value));
            strength.dataset.strength = String(score);
            if (strengthBar) {
                strengthBar.style.width = String(score * 25) + '%';
            }
            if (strengthLabel) {
                strengthLabel.textContent = strengthNames[score];
            }
        });
    }

    // ---------- Konfirmasi kata sandi ----------
    const confirmationInput = document.getElementById('password_confirmation');
    const confirmationError = document.getElementById('password-confirmation-error');

    const validateConfirmation = function () {
        if (!confirmationInput || !passwordInput) {
            return true;
        }
        const mismatch = confirmationInput.value !== '' && confirmationInput.value !== passwordInput.value;
        confirmationInput.classList.toggle('is-invalid', mismatch);
        if (confirmationError) {
            confirmationError.classList.toggle('d-block', mismatch);
        }
        return !mismatch;
    };

    if (confirmationInput) {
        confirmationInput.addEventListener('input', validateConfirmation);
    }
    if (passwordInput && confirmationInput) {
        passwordInput.addEventListener('input', validateConfirmation);
    }

    // ---------- Batas tanggal lahir ----------
    const birthdateInput = document.getElementById('tanggal_lahir');

    if (birthdateInput) {
        birthdateInput.max = todayIso;
        if (!birthdateInput.min) {
            birthdateInput.min = '1900-01-01';
        }
    }

    // ---------- Penghitung karakter alamat ----------
    const addressInput = document.getElementById('alamat_rumah');
    const addressCounter = document.getElementById('alamat-counter');

    const countAddress = function () {
        if (addressCounter && addressInput) {
            addressCounter.textContent = String(addressInput.value.length) + '/1000';
        }
    };

    if (addressInput) {
        addressInput.addEventListener('input', countAddress);
        countAddress();
    }

    // ---------- Ringkasan error & auto-scroll ke field pertama yang salah ----------
    const firstInvalid = function () {
        return form.querySelector('.is-invalid:not(.d-none), [aria-invalid="true"]');
    };

    const focusFirstInvalid = function () {
        const target = firstInvalid();
        if (!target) {
            return;
        }
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.setTimeout(function () {
            target.focus({ preventScroll: true });
        }, 150);
    };

    if (form.querySelector('.is-invalid')) {
        window.requestAnimationFrame(focusFirstInvalid);
    } else if (document.getElementById('error-summary')) {
        window.requestAnimationFrame(function () {
            document.getElementById('error-summary').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    // ---------- Submit: validasi ringan + disable tombol + spinner ----------
    const submitButton = document.getElementById('register-submit');

    form.addEventListener('submit', function (event) {
        countNik();

        const phoneValid = validatePhone();
        const confirmed = validateConfirmation();

        if (!phoneValid || !confirmed) {
            event.preventDefault();
            focusFirstInvalid();
            return;
        }

        if (submitButton && !form.checkValidity()) {
            // Biar browser menampilkan pesan required bawaan; fokus ke field pertama.
            window.setTimeout(focusFirstInvalid, 0);
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            const label = submitButton.querySelector('.register-submit-label');
            if (label) {
                label.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memproses pendaftaran…';
            }
        }
    });
})();
