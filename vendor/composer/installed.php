<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '1e699b81b7918519d53a22b4acc486d017f00301',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '1e699b81b7918519d53a22b4acc486d017f00301',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'ifsnop/mysqldump-php' => array(
            'pretty_version' => 'v2.11',
            'version' => '2.11.0.0',
            'reference' => 'ec6a777062b287cd25cb1cd916b3d14c595ebdb8',
            'type' => 'library',
            'install_path' => __DIR__ . '/../ifsnop/mysqldump-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'pilulka/mysql2sqlite' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'caf503eed98f5b2d8b3460e131b89d03bca8d0a1',
            'type' => 'library',
            'install_path' => __DIR__ . '/../pilulka/mysql2sqlite',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'vectorface/mysqlite' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'a7a16688885c526067886f5c56f166ee382712f9',
            'type' => 'library',
            'install_path' => __DIR__ . '/../vectorface/mysqlite',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => true,
        ),
    ),
);
