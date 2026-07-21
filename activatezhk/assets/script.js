function remove_licence(el) {
    let response = confirm(zhaket_guard.confirm_msg);
    if (response !== true) return;

    if (el.classList.contains('disable')) return;
    el.classList = 'disable';
    const licenseInput = document.querySelector('#code-style'),
        thisEl = document.getElementById('license-message'),
        resultDiv = message_div_prepare(thisEl);

    fetch(zhaket_guard.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: zhaket_guard.this_slug + '_request_deactivate',
            nonce: zhaket_guard.nonce,
        })
    })
    .then(response => response.json())
    .then(result => {
        console.log(result);
        resultDiv.classList.remove('waiting');
        el.classList = '';
        guard_show_ajax_message(thisEl, result);
    })
    .catch(() => {
        resultDiv.classList.remove('waiting');
        el.classList = '';
        thisEl.style.background = 'red';
        const resultElement = thisEl.querySelector('.result');
        if (resultElement) resultElement.innerHTML = zhaket_guard.wrong_license_message;
    });
}

function recheck_licence(el) {
    if (el.classList.contains('disable')) return;
    el.classList = 'disable';
    const licenseInput = document.querySelector('#code-style'),
        thisEl = document.getElementById('license-message'),
        resultDiv = message_div_prepare(thisEl);

    fetch(zhaket_guard.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: zhaket_guard.this_slug + '_request_recheck',
            nonce: zhaket_guard.nonce,
        })
    })
    .then(response => response.json())
    .then(result => {
        console.log(result);
        resultDiv.classList.remove('waiting');
        el.classList = '';
        guard_show_ajax_message(thisEl, result, false);
    })
    .catch(() => {
        resultDiv.classList.remove('waiting');
        el.classList = '';
        thisEl.style.background = 'red';
        const resultElement = thisEl.querySelector('.result');
        if (resultElement) resultElement.innerHTML = zhaket_guard.wrong_license_message;
    });
}

function install_licence(el) {
    if (el.classList.contains('disable')) return;
    el.classList = 'disable';
    const licenseInput = document.querySelector('#license-input'),
        license = licenseInput ? licenseInput.value : '',
        thisEl = document.getElementById('license-message'),
        resultDiv = message_div_prepare(thisEl);

    if (license.length < 10) {
        thisEl.style.background = 'red';
        const resultElement = thisEl.querySelector('.result');
        if (resultElement) resultElement.innerHTML = zhaket_guard.please_add_valid_license;
        if (licenseInput) {
            licenseInput.removeAttribute('disabled');
            licenseInput.focus();
        }
        resultDiv.classList.remove('waiting');
        el.classList = '';
        return;
    }

    if (licenseInput) licenseInput.setAttribute('disabled', 'disabled');

    fetch(zhaket_guard.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: zhaket_guard.this_slug + '_request_active',
            license: license,
            nonce: zhaket_guard.nonce,
        })
    })
    .then(response => response.json())
    .then(result => {
        console.log(result);
        resultDiv.classList.remove('waiting');
        el.classList = '';
        if (licenseInput) {
            licenseInput.removeAttribute('disabled');
            licenseInput.focus();
        }
        guard_show_ajax_message(thisEl, result);
    })
    .catch(result => {
        console.log(result);
        resultDiv.classList.remove('waiting');
        el.classList = '';
        if (licenseInput) {
            licenseInput.removeAttribute('disabled');
            licenseInput.focus();
        }
        thisEl.style.background = 'red';
        const resultElement = thisEl.querySelector('.result');
        if (resultElement) resultElement.innerHTML = zhaket_guard.wrong_license_message;
    });
}

function guard_page_html() {
    fetch(zhaket_guard.ajax_url + '?' + new URLSearchParams({
        action: zhaket_guard.this_slug + '_guard_html',
        nonce: zhaket_guard.nonce,
    }))
    .then(response => response.text())
    .then(result => {
        const mainGuardPage = document.getElementById('main-guard-page');
        if (mainGuardPage) mainGuardPage.innerHTML = result;
    })
    .catch(error => console.error('Error:', error));
}

function message_div_prepare(thisEl) {
    thisEl.innerHTML = '';
    thisEl.style.background = '#445a93';

    // Show element with flex display
    thisEl.style.display = 'flex';

    // Add slide down effect (simplified)
    const height = thisEl.scrollHeight;
    thisEl.style.overflow = 'hidden';
    thisEl.style.height = '0';
    thisEl.style.transition = 'height 0.3s ease';

    setTimeout(() => {
        thisEl.style.height = 'unset';
    }, 10);

    thisEl.innerHTML = '<div class="result waiting"></div>';
    return thisEl.querySelector('.result');
}

function guard_show_ajax_message(thisEl, result, return_html = true) {
    if (result.message !== undefined) {
        let style = {};
        if (result.status !== undefined) {
            if (result.status === false) {
                style = { background: 'red' };
            } else {
                setTimeout(function() {
                    window.location.reload();
                }, 3000);
            }
        }

        // Apply styles
        if (style.background) {
            thisEl.style.background = style.background;
        }

        const resultElement = thisEl.querySelector('.result');
        if (resultElement) {
            // Add class if needed
            if (style.background === 'red') {
                resultElement.classList.add('background-red');
            }

            // Append message
            resultElement.innerHTML += result.message;

            // Simple slide down animation
            resultElement.style.display = 'block';
            resultElement.style.overflow = 'hidden';
            resultElement.style.height = '0';
            resultElement.style.transition = 'height 0.15s ease';

            setTimeout(() => {
                resultElement.style.height = 'unset';
            }, 10);
        }
    } else {
        console.log(result);
        thisEl.style.background = 'red';

        const resultElement = thisEl.querySelector('.result');
        if (resultElement) {
            resultElement.innerHTML += zhaket_guard.view_problem_console_log;

            // Simple slide down animation
            resultElement.style.display = 'block';
            resultElement.style.overflow = 'hidden';
            resultElement.style.height = '0';
            resultElement.style.transition = 'height 0.15s ease';

            setTimeout(() => {
                resultElement.style.height = 'unset';
            }, 10);
        }
    }

    if (result.status === true && return_html === true) {
        setTimeout(function() {
            guard_page_html();
        }, 1000);
    }
}
