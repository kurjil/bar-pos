document.addEventListener('DOMContentLoaded', function () {
    var printBtn = document.getElementById('printLastReceiptBtn');
    if (!printBtn || !window.APP_URL) {
        return;
    }

    printBtn.addEventListener('click', function () {
        printBtn.disabled = true;
        printBtn.textContent = 'Printing...';

        fetch(window.APP_URL + '/api/sales/print-last', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ csrf_token: window.CSRF_TOKEN })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                alert('Receipt sent to printer: ' + (data.receipt_number || ''));
            } else {
                alert(data.message || 'Print failed. Check printer settings.');
            }
            printBtn.disabled = false;
            printBtn.textContent = 'Print Last Receipt';
        })
        .catch(function () {
            alert('Network error');
            printBtn.disabled = false;
            printBtn.textContent = 'Print Last Receipt';
        });
    });
});
