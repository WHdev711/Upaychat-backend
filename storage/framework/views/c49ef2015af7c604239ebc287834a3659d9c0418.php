<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Fav Icon  -->
    <link href="<?php echo e(asset('favicon.png')); ?>" rel="shortcut icon" type="image/png">
    <link href="<?php echo e(asset('favicon.png')); ?>" rel="icon" type="image/png">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Scripts -->
    <script src="<?php echo e(asset('js/app.js')); ?>" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo e(asset('backend/plugins/fontawesome-free/css/all.min.css')); ?>">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?php echo e(asset('backend/plugins/icheck-bootstrap/icheck-bootstrap.min.css')); ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo e(asset('backend/dist/css/adminlte.min.css')); ?>">

</head>
<body class="hold-transition login-page">
<div id="app">

    <main class="py-4">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>

<script src="<?php echo e(asset('backend/plugins/jquery/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/dist/js/adminlte.min.js')); ?>"></script>
</body>
</html>
<?php /**PATH F:\work2\UpayChat\Backend\admin\resources\views/layouts/app.blade.php ENDPATH**/ ?>