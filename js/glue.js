document.addEventListener('DOMContentLoaded', function () {
    const widgets = document.querySelectorAll('altcha-widget');

    if (!widgets.length) {
        console.warn('[Altcha] No altcha widgets found.');
        return;
    }

    widgets.forEach(widget => {
        console.log('[Altcha] Widget found.');

        widget.addEventListener('change', (event) => {
            console.log('[Altcha] change event triggered', event);

            const payload = event?.target?.value;

            if (!payload) {
                console.warn('[Altcha] No payload received on change event.');
                return;
            }

            const hiddenInput = widget.querySelector('input[type="hidden"][name="altcha"]') || document.getElementById('altcha-hidden');

            if (!hiddenInput) {
                console.warn('[Altcha] Hidden input not found.');
                return;
            }

            hiddenInput.value = payload;
            console.log('[Altcha] Payload copied to hidden input.');
        });

        console.log('[Altcha] Widget initialized and listener attached.');
    });
});
