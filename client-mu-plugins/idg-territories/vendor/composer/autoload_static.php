<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6b32dae1336a3df174874708c5dd3ef0
{
    public static $files = array (
        '3c3a02bc9945aa3571024986c07a1e55' => __DIR__ . '/..' . '/rinvex/countries/src/helpers.php',
        '11276ef26254e59982b34e22ea937b70' => __DIR__ . '/../..' . '/inc/asset-settings.php',
        'adb8fc101d0964f44cf8e6723955460f' => __DIR__ . '/../..' . '/inc/setup.php',
        '9e2281adf8b3a324ee461180efd537f2' => __DIR__ . '/../..' . '/inc/utils.php',
    );

    public static $prefixLengthsPsr4 = array (
        'R' =>
        array (
            'Rinvex\\Country\\' => 15,
        ),
        'I' =>
        array (
            'IDG\\Territories\\' => 16,
        ),
        'A' =>
        array (
            'Automattic\\Jetpack\\Autoloader\\' => 30,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Rinvex\\Country\\' =>
        array (
            0 => __DIR__ . '/..' . '/rinvex/countries/src',
        ),
        'IDG\\Territories\\' =>
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
        'Automattic\\Jetpack\\Autoloader\\' =>
        array (
            0 => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src',
        ),
    );

    public static $classMap = array (
        'IDG\\Territories\\Geolocation' => __DIR__ . '/../..' . '/inc/class-geolocation.php',
        'IDG\\Territories\\Helpers' => __DIR__ . '/../..' . '/inc/class-helpers.php',
        'IDG\\Territories\\Territory' => __DIR__ . '/../..' . '/inc/class-territory.php',
        'IDG\\Territories\\Territory_Loader' => __DIR__ . '/../..' . '/inc/class-territory-loader.php',
        'IDG\\Territories\\Territory_Taxonomy' => __DIR__ . '/../..' . '/inc/class-territory-taxonomy.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6b32dae1336a3df174874708c5dd3ef0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6b32dae1336a3df174874708c5dd3ef0::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6b32dae1336a3df174874708c5dd3ef0::$classMap;

        }, null, ClassLoader::class);
    }
}