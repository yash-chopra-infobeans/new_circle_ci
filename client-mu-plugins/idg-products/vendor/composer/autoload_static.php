<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9c73768bf184748213d7f546060e3bfb
{
    public static $files = array (
        '5b908e831573627c9897de36768fd9f9' => __DIR__ . '/../..' . '/inc/asset-settings.php',
        '40c3a3b7df7d25cb8d77d7d7df85b70c' => __DIR__ . '/../..' . '/inc/utils.php',
        '3c1fe6555f3de55fcfc27caaefd6ce7c' => __DIR__ . '/../..' . '/inc/setup.php',
    );

    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'IDG\\Products\\' => 13,
        ),
        'A' => 
        array (
            'Automattic\\Jetpack\\Autoloader\\' => 30,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'IDG\\Products\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
        'Automattic\\Jetpack\\Autoloader\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'D' => 
        array (
            'Detection' => 
            array (
                0 => __DIR__ . '/..' . '/mobiledetect/mobiledetectlib/namespaced',
            ),
        ),
    );

    public static $classMap = array (
        'IDG\\Products\\API\\Product' => __DIR__ . '/../..' . '/inc/api/class-product.php',
        'IDG\\Products\\Article' => __DIR__ . '/../..' . '/inc/class-article.php',
        'IDG\\Products\\Data_Layer' => __DIR__ . '/../..' . '/inc/class-data-layer.php',
        'IDG\\Products\\Link_Wrapping' => __DIR__ . '/../..' . '/inc/class-link-wrapping.php',
        'IDG\\Products\\Product' => __DIR__ . '/../..' . '/inc/class-product.php',
        'IDG\\Products\\Product_Post_Type' => __DIR__ . '/../..' . '/inc/class-product-post-type.php',
        'IDG\\Products\\Reviews' => __DIR__ . '/../..' . '/inc/class-reviews.php',
        'IDG\\Products\\Search' => __DIR__ . '/../..' . '/inc/class-search.php',
        'IDG\\Products\\Subtag' => __DIR__ . '/../..' . '/inc/class-subtag.php',
        'IDG\\Products\\Transform' => __DIR__ . '/../..' . '/inc/class-transform.php',
        'IDG\\Products\\Vendors\\Amazon' => __DIR__ . '/../..' . '/inc/vendors/class-amazon.php',
        'Mobile_Detect' => __DIR__ . '/..' . '/mobiledetect/mobiledetectlib/Mobile_Detect.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9c73768bf184748213d7f546060e3bfb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9c73768bf184748213d7f546060e3bfb::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit9c73768bf184748213d7f546060e3bfb::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit9c73768bf184748213d7f546060e3bfb::$classMap;

        }, null, ClassLoader::class);
    }
}
