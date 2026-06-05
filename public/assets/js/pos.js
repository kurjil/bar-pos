(function () {
    'use strict';

    const cart = {};
    const grid = document.getElementById('product-grid');
    const cartEl = document.getElementById('cart-items');
    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    const searchInput = document.getElementById('product-search');

    function formatMoney(n) {
        return parseFloat(n).toFixed(2);
    }

    function renderCart() {
        cartEl.innerHTML = '';
        let subtotal = 0;
        const ids = Object.keys(cart);

        ids.forEach(function (id) {
            const item = cart[id];
            const line = item.price * item.qty;
            subtotal += line;

            const row = document.createElement('div');
            row.className = 'border rounded p-2 mb-2';
            row.innerHTML =
                '<div class="d-flex justify-content-between align-items-start">' +
                '<strong>' + item.name + '</strong>' +
                '<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item" data-id="' + id + '">&times;</button></div>' +
                '<div class="d-flex align-items-center gap-2 mt-2">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary qty-minus" data-id="' + id + '">-</button>' +
                '<span class="px-2">' + item.qty + '</span>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary qty-plus" data-id="' + id + '">+</button>' +
                '<span class="ms-auto">' + formatMoney(line) + '</span></div>';
            cartEl.appendChild(row);
        });

        const discountType = document.getElementById('discount-type').value;
        const discountVal = parseFloat(document.getElementById('discount-value').value) || 0;
        let discount = 0;
        if (discountType === 'PERCENTAGE') {
            discount = subtotal * Math.min(discountVal, 20) / 100;
        } else if (discountType === 'FIXED') {
            discount = Math.min(discountVal, subtotal);
        }

        const total = subtotal - discount;
        subtotalEl.textContent = formatMoney(subtotal);
        totalEl.textContent = formatMoney(total);
        checkoutBtn.disabled = ids.length === 0;
    }

    function addToCart(el) {
        const id = el.dataset.id;
        const stock = parseInt(el.dataset.stock, 10);
        if (!cart[id]) {
            cart[id] = { product_id: parseInt(id, 10), name: el.dataset.name, price: parseFloat(el.dataset.price), qty: 0, stock: stock };
        }
        if (cart[id].qty >= stock) return;
        cart[id].qty++;
        renderCart();
    }

    grid.addEventListener('click', function (e) {
        const btn = e.target.closest('.pos-product-btn');
        if (!btn) return;
        addToCart(btn.closest('.product-item'));
    });

    cartEl.addEventListener('click', function (e) {
        const id = e.target.dataset.id;
        if (!id) return;
        if (e.target.classList.contains('remove-item')) {
            delete cart[id];
        } else if (e.target.classList.contains('qty-minus') && cart[id].qty > 1) {
            cart[id].qty--;
        } else if (e.target.classList.contains('qty-plus') && cart[id].qty < cart[id].stock) {
            cart[id].qty++;
        }
        renderCart();
    });

    document.getElementById('discount-type').addEventListener('change', renderCart);
    document.getElementById('discount-value').addEventListener('input', renderCart);
    document.getElementById('clear-cart-btn').addEventListener('click', function () {
        Object.keys(cart).forEach(function (k) { delete cart[k]; });
        renderCart();
    });

    document.querySelectorAll('.category-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.category-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            const cat = btn.dataset.category;
            document.querySelectorAll('.product-item').forEach(function (item) {
                item.style.display = !cat || item.dataset.category === cat ? '' : 'none';
            });
        });
    });

    let searchTimer;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            const q = searchInput.value.toLowerCase();
            document.querySelectorAll('.product-item').forEach(function (item) {
                item.style.display = item.dataset.name.toLowerCase().includes(q) ? '' : 'none';
            });
        }, 300);
    });

    checkoutBtn.addEventListener('click', function () {
        checkoutBtn.disabled = true;
        const items = Object.values(cart).map(function (i) {
            return { product_id: i.product_id, quantity: i.qty };
        });

        fetch(window.APP_URL + '/api/pos/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({
                csrf_token: window.CSRF_TOKEN,
                items: items,
                payment_method: document.getElementById('payment-method').value,
                discount_type: document.getElementById('discount-type').value,
                discount_value: parseFloat(document.getElementById('discount-value').value) || 0
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            const msg = document.getElementById('checkout-msg');
            if (data.success) {
                msg.innerHTML = '<div class="alert alert-success">Sale complete! Receipt: ' + data.data.receipt_number + '</div>';
                Object.keys(cart).forEach(function (k) { delete cart[k]; });
                renderCart();
                setTimeout(function () { location.reload(); }, 1500);
            } else {
                msg.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Checkout failed') + '</div>';
                checkoutBtn.disabled = false;
            }
        })
        .catch(function () {
            document.getElementById('checkout-msg').innerHTML = '<div class="alert alert-danger">Network error</div>';
            checkoutBtn.disabled = false;
        });
    });
})();
