<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit69fc28174abe912f71ebf710ee161e8f
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'Lev\\Plugin\\FlexObjects\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Lev\\Plugin\\FlexObjects\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Lev\\Plugin\\FlexObjectsPlugin' => __DIR__ . '/../..' . '/flex-objects.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit69fc28174abe912f71ebf710ee161e8f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit69fc28174abe912f71ebf710ee161e8f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit69fc28174abe912f71ebf710ee161e8f::$classMap;

        }, null, ClassLoader::class);
    }
}
