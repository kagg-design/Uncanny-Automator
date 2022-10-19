<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit61211dda973ce267f1218b16c678a536
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'C' => 
        array (
            'ChrisKonnertz\\StringCalc' => 
            array (
                0 => __DIR__ . '/..' . '/chriskonnertz/string-calc/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit61211dda973ce267f1218b16c678a536::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit61211dda973ce267f1218b16c678a536::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit61211dda973ce267f1218b16c678a536::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit61211dda973ce267f1218b16c678a536::$classMap;

        }, null, ClassLoader::class);
    }
}
