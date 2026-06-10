<?php use App\Helpers\Formatter; ?>
<?php $reportForm = function ($action, $exportType = null) use ($from, $to) { ?>
<form method="GET" action="<?= e(appConfig('url')) ?>/reports/<?= $action ?>" class="row g-2 mb-4 align-items-end">
    <div class="col-auto"><label class="form-label small mb-0">From</label><input type="date" name="from" class="form-control" value="<?= e($from) ?>"></div>
    <div class="col-auto"><label class="form-label small mb-0">To</label><input type="date" name="to" class="form-control" value="<?= e($to) ?>"></div>
    <div class="col-auto"><button type="submit" class="btn btn-primary">Filter</button></div>
    <?php if ($exportType): ?>
    <div class="col-auto">
        <a href="<?= e(appConfig('url')) ?>/reports/export/<?= e($exportType) ?>?from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-success">
            Export Excel
        </a>
    </div>
    <?php endif; ?>
</form>
<?php }; ?>
