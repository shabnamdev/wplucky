<?php defined('ABSPATH') || exit ("no access");  ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
<style>
	body {
		background: -webkit-linear-gradient(100deg, #f4fff6 30%, #eaf9ec 50%);
		background: -o-linear-gradient(100deg, #f4fff6 30%, #eaf9ec 50%);
		background: -moz-linear-gradient(100deg, #f4fff6 30%, #eaf9ec 50%);
		background: linear-gradient(100deg, #f4fff6 30%, #eaf9ec 50%);
	}
	#wpbody-content > div:not(#main-guard-page) {
		display: none;
	}
</style>
<div id="main-guard-inner">
	<?php include __DIR__.'/license-form.php' ?>
    <!-- /.license-input -->
    <div class="background-status">
        <?php if ($this->c15fd51e29734304c8302b): ?>
            <?php include __DIR__.'/assets/unlocked.svg' ?>
        <?php else: ?>
            <?php include __DIR__.'/assets/lock.svg' ?>
        <?php endif; ?>
    </div>
    <!-- /.background-status -->
</div>
<!-- /#main-guard-inner -->