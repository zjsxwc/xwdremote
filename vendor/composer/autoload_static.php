<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9862c59106a88118cd2d17265333025b
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Workerman\\' => 10,
            'Wangchao\\Xwdremote\\' => 19,
        ),
        'C' => 
        array (
            'Channel\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman',
        ),
        'Wangchao\\Xwdremote\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Channel\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/channel/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9862c59106a88118cd2d17265333025b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9862c59106a88118cd2d17265333025b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9862c59106a88118cd2d17265333025b::$classMap;

        }, null, ClassLoader::class);
    }
}
