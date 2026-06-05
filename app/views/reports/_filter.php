<?php use App\Helpers\Formatter; ?>
<?php $reportForm = function ($action) use ($from, $to) { ?>
<form method="GET" action="<?= e(appConfig('url')) ?>/reports/<?= $action ?>" class="row g-2 mb-4">
    <div class="col-auto"><input type="date" name="from" class="form-control" value="<?= e($from) ?>"></div>
    <div class="col-auto"><input type="date" name="to" class="form-control" value="<?= e($to) ?>"></div>
    <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
</form>
<?php }; ?>
