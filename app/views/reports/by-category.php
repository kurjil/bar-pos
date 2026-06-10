<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('by-category', 'by-category'); ?>
<h2 class="h4 mb-3">Sales by Category</h2>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0 small">
    <thead class="table-light">
        <tr>
            <th>Category</th>
            <th class="text-end">Transactions</th>
            <th class="text-end">Qty Sold</th>
            <th class="text-end">Revenue</th>
            <th class="text-end">Cost</th>
            <th class="text-end">Gross Profit</th>
            <th class="text-end">Avg Trans</th>
            <th class="text-end">Margin %</th>
        </tr>
    </thead>
    <tbody>
        <?php $totalRevenue = 0; $totalCost = 0; $totalProfit = 0; $totalTxns = 0; ?>
        <?php foreach ($rows as $r): ?>
            <?php 
                $profit = (float)$r['gross_profit'];
                $revenue = (float)$r['revenue'];
                $margin = $revenue > 0 ? ($profit / $revenue * 100) : 0;
                $totalRevenue += $revenue;
                $totalCost += (float)$r['cost'];
                $totalProfit += $profit;
                $totalTxns += (int)$r['transaction_count'];
            ?>
            <tr>
                <td><a href="<?= e(appConfig('url')) ?>/sales?category_id=<?= (int) $r['id'] ?>"><?= e($r['category_name']) ?></a></td>
                <td class="text-end"><?= (int) $r['transaction_count'] ?></td>
                <td class="text-end"><?= (int) $r['total_qty'] ?></td>
                <td class="text-end fw-bold"><?= Formatter::money($revenue) ?></td>
                <td class="text-end text-muted"><?= Formatter::money((float) $r['cost']) ?></td>
                <td class="text-end text-success fw-bold"><?= Formatter::money($profit) ?></td>
                <td class="text-end"><?= Formatter::money((float) $r['avg_transaction']) ?></td>
                <td class="text-end"><span class="badge bg-info"><?= number_format($margin, 1) ?>%</span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light">
        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="text-end fw-bold"><?= $totalTxns ?></td>
            <td class="text-end fw-bold">-</td>
            <td class="text-end fw-bold"><?= Formatter::money($totalRevenue) ?></td>
            <td class="text-end text-muted fw-bold"><?= Formatter::money($totalCost) ?></td>
            <td class="text-end text-success fw-bold"><?= Formatter::money($totalProfit) ?></td>
            <td class="text-end fw-bold"><?= Formatter::money($totalRevenue / max(1, $totalTxns)) ?></td>
            <td class="text-end"><strong><?= number_format(($totalRevenue > 0 ? $totalProfit / $totalRevenue * 100 : 0), 1) ?>%</strong></td>
        </tr>
    </tfoot>
</table></div></div>
