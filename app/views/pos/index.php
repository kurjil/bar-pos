<?php use App\Helpers\Formatter; ?>
<div class="pos-layout d-flex">
    <div class="pos-products flex-grow-1 p-3">
        <div class="mb-3 d-flex gap-2 flex-wrap">
            <input type="search" id="product-search" class="form-control form-control-lg" placeholder="Search products..." style="max-width:320px">
            <button type="button" class="btn btn-outline-secondary btn-lg category-btn active" data-category="">All</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="btn btn-outline-secondary btn-lg category-btn" data-category="<?= (int) $cat['id'] ?>"><?= e($cat['name']) ?></button>
            <?php endforeach; ?>
        </div>
        <div id="product-grid" class="row g-2">
            <?php foreach ($products as $p): ?>
                <div class="col-6 col-md-4 col-lg-3 product-item" data-id="<?= (int) $p['id'] ?>"
                     data-name="<?= e($p['name']) ?>" data-price="<?= (float) $p['selling_price'] ?>"
                     data-stock="<?= (int) $p['stock_quantity'] ?>" data-category="<?= (int) $p['category_id'] ?>">
                    <button type="button" class="btn btn-light w-100 h-100 pos-product-btn p-3 text-start">
                        <div class="fw-bold"><?= e($p['name']) ?></div>
                        <div class="text-primary"><?= Formatter::money((float) $p['selling_price']) ?></div>
                        <small class="text-muted">Stock: <?= (int) $p['stock_quantity'] ?></small>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <aside class="pos-cart bg-white border-start p-3" style="width:380px;min-width:320px">
        <h5 class="mb-3">Cart</h5>
        <div id="cart-items" class="mb-3" style="max-height:45vh;overflow-y:auto"></div>
        <div class="border-top pt-3">
            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong id="cart-subtotal">0.00</strong></div>
            <div class="mb-2">
                <label class="form-label small">Discount</label>
                <div class="input-group input-group-sm">
                    <select id="discount-type" class="form-select"><option value="NONE">None</option><option value="PERCENTAGE">%</option><option value="FIXED">Fixed</option></select>
                    <input type="number" id="discount-value" class="form-control" min="0" step="0.01" value="0">
                </div>
            </div>
            <div class="d-flex justify-content-between mb-3 fs-5"><span>Total</span><strong id="cart-total">0.00</strong></div>
            <div class="mb-3">
                <label class="form-label">Payment</label>
                <select id="payment-method" class="form-select form-select-lg">
                    <option value="CASH">Cash</option>
                    <option value="MOBILE_MONEY">Mobile Money</option>
                    <option value="CARD">Card</option>
                </select>
            </div>
            <button type="button" id="checkout-btn" class="btn btn-success btn-lg w-100" disabled>Complete Sale</button>
            <button type="button" id="clear-cart-btn" class="btn btn-outline-danger w-100 mt-2">Clear Cart</button>
        </div>
        <div id="checkout-msg" class="mt-3"></div>
    </aside>
</div>
