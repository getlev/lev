<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteed5e5cf0aa1e2139f2db7445511e366
{
    public static $files = array (
        '5255c38a0faeba867671b61dfda6d864' => __DIR__ . '/..' . '/paragonie/random_compat/lib/random.php',
    );

    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'RobThree\\Auth\\' => 14,
        ),
        'L' => 
        array (
            'Lev\\Plugin\\Login\\' => 17,
            'Lev\\Plugin\\Console\\' => 19,
        ),
        'B' => 
        array (
            'Birke\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'RobThree\\Auth\\' => 
        array (
            0 => __DIR__ . '/..' . '/robthree/twofactorauth/lib',
        ),
        'Lev\\Plugin\\Login\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
        'Lev\\Plugin\\Console\\' => 
        array (
            0 => __DIR__ . '/../..' . '/cli',
        ),
        'Birke\\' => 
        array (
            0 => __DIR__ . '/..' . '/birke/rememberme/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'B' => 
        array (
            'BaconQrCode' => 
            array (
                0 => __DIR__ . '/..' . '/bacon/bacon-qr-code/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Lev\\Plugin\\LoginPlugin' => __DIR__ . '/../..' . '/login.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticIniteed5e5cf0aa1e2139f2db7445511e366::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticIniteed5e5cf0aa1e2139f2db7445511e366::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticIniteed5e5cf0aa1e2139f2db7445511e366::$prefixesPsr0;
            $loader->classMap = ComposerStaticIniteed5e5cf0aa1e2139f2db7445511e366::$classMap;

        }, null, ClassLoader::class);
    }
}
