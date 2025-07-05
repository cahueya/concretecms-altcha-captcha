document.addEventListener('DOMContentLoaded', () => {
    const widget = document.querySelector('altcha-widget');
    const form = widget?.closest('form');
    const externalInput = document.getElementById('altcha-hidden');

    if (!form || !widget || !externalInput) {
        console.error('[Altcha] Widget, form, or external input not found.');
        return;
    }

    // Observe the widget until the internal input appears
    const waitForInput = new MutationObserver(() => {
        const internalInput = widget.querySelector('input[name="altcha"]');

        if (internalInput) {
            console.log('[Altcha] Internal input found:', internalInput);

            // Stop observing once found
            waitForInput.disconnect();

            const copyValue = () => {
                const val = internalInput.value;
                if (val && val.length > 10) {
                    externalInput.value = val;
                    console.log('[Altcha] Copied payload to external input:', val);
                } else {
                    console.warn('[Altcha] Payload not ready or too short:', val);
                }
            };

            // Watch for internal input value changes
            const observer = new MutationObserver(() => {
                copyValue();
            });

            observer.observe(internalInput, {
                attributes: true,
                attributeFilter: ['value'],
            });

            // Also ensure value is copied before form submit
            form.addEventListener('submit', (e) => {
                copyValue();

                if (!externalInput.value || externalInput.value.length < 10) {
                    e.preventDefault();
                    alert('Please complete the CAPTCHA before submitting.');
                    console.warn('[Altcha] Submission blocked — no valid payload.');
                }
            });
        }
    });

    waitForInput.observe(widget, {
        childList: true,
        subtree: true,
    });
});
