<?php
spl_autoload_register(function (string $class): void {
    $map = ['Pendu\\Tests\\' => __DIR__ . '/tests/', 'Pendu\\' => __DIR__ . '/src/'];
    foreach ($map as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) { require $file; return; }
        }
    }
});
