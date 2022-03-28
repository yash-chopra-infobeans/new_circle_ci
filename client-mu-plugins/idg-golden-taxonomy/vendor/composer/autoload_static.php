<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc7a38568acf76d370b540f2d8dd50edb
{
    public static $files = array (
        '8c5ed64e788effb7d494be37490ccded' => __DIR__ . '/../..' . '/inc/asset-settings.php',
        'b823fb752c830c281e34e4be8c7ea940' => __DIR__ . '/../..' . '/inc/setup.php',
        '378e9e3f172a2f830189108ad0dfd3cb' => __DIR__ . '/../..' . '/inc/functions.php',
        'e43cd765c94a48a4e1ea7006bcc24bd0' => __DIR__ . '/../..' . '/inc/utils.php',
    );

    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'IDG\\' => 4,
        ),
        'A' => 
        array (
            'Automattic\\Jetpack\\Autoloader\\' => 30,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'IDG\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
        'Automattic\\Jetpack\\Autoloader\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src',
        ),
    );

    public static $classMap = array (
        'IDG\\Golden_Taxonomy\\Data_Layer' => __DIR__ . '/../..' . '/inc/class-data-layer.php',
        'IDG\\Golden_Taxonomy\\Meta_Boxes' => __DIR__ . '/../..' . '/inc/class-meta-boxes.php',
        'IDG\\Golden_Taxonomy\\Taxonomy' => __DIR__ . '/../..' . '/inc/class-taxonomy.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc7a38568acf76d370b540f2d8dd50edb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc7a38568acf76d370b540f2d8dd50edb::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc7a38568acf76d370b540f2d8dd50edb::$classMap;

        }, null, ClassLoader::class);
    }
}