<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo defined('APP_NAME') ? APP_NAME : 'CASOCE'; ?> - <?php echo isset($pageTitle) ? $pageTitle : 'Portal'; ?></title>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <?php $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'); ?>
    <script src="<?php echo $baseDir; ?>/public/js/sync-engine.js"></script>
    
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>