<?php
return [
    'BE' => [
        'debug' => false,
        'installToolPassword' => '$argon2i$v=19$m=65536,t=16,p=1$Q0xzcWk5LzdqQnRqYmpVUQ$D+JkwANe+OK/yvxjZTssb0vcoJBohTSHmgaHbNK8pw8',
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
        'passwordPolicy' => '',
    ],
    'DB' => [
        'Connections' => [
            'Default' => [
                'charset' => 'utf8mb4',
                'dbname' => 'db',
                'defaultTableOptions' => [
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ],
                'driver' => 'mysqli',
                'host' => 'db',
                'password' => 'db',
                'port' => 3306,
                'user' => 'db',
            ],
        ],
    ],
    'EXTENSIONS' => [
        'backend' => [
            'backendFavicon' => '',
            'backendLogo' => '',
            'loginBackgroundImage' => '',
            'loginFootnote' => '',
            'loginHighlightColor' => '',
            'loginLogo' => '',
            'loginLogoAlt' => '',
        ],
        'developer_mcp' => [
            'endpoint' => [
                'allowProduction' => '0',
            ],
            'phpShellTool' => [
                'enabled' => '1',
            ],
        ],
        'flux' => [
            'autoload' => '1',
            'customLayoutSelector' => '0',
            'debugMode' => '0',
            'doktypes' => '0,1,4',
            'flexFormToIrre' => '0',
            'handleErrors' => '0',
            'inheritanceMode' => 'restricted',
            'pageIntegration' => '1',
            'plugAndPlay' => '0',
            'plugAndPlayDirectory' => 'design',
            'uniqueFileFieldNames' => '0',
        ],
        'scheduler' => [
            'maxLifetime' => '1440',
        ],
        'sentry_client' => [
            'disableDatabaseLogging' => '0',
            'dsn' => false,
            'ignoreMessageRegex' => '',
            'logWriterComponentIgnorelist' => '',
            'release' => '',
            'reportDatabaseConnectionErrors' => '0',
            'reportUserInformation' => 'userid',
            'showEventId' => '1',
        ],
    ],
    'FE' => [
        'cacheHash' => [
            'enforceValidation' => true,
            'excludedParameters' => [
                'q',
            ],
        ],
        'debug' => false,
        'disableNoCacheParameter' => true,
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
    ],
    'GFX' => [
        'processor' => 'GraphicsMagick',
        'processor_effects' => false,
        'processor_enabled' => true,
        'processor_path' => '/usr/bin/',
    ],
    'LOG' => [
        'TYPO3' => [
            'CMS' => [
                'deprecations' => [
                    'writerConfiguration' => [
                        'notice' => [
                            'TYPO3\CMS\Core\Log\Writer\FileWriter' => [
                                'disabled' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'MAIL' => [
        'transport' => 'sendmail',
        'transport_sendmail_command' => '/usr/local/bin/mailpit sendmail -t --smtp-addr 127.0.0.1:1025',
        'transport_smtp_encrypt' => '',
        'transport_smtp_password' => '',
        'transport_smtp_server' => '',
        'transport_smtp_username' => '',
    ],
    'SYS' => [
        'UTF8filesystem' => true,
        'caching' => [
            'cacheConfigurations' => [
                'hash' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                ],
                'pages' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
                'rootline' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
            ],
        ],
        'devIPmask' => '',
        'displayErrors' => 0,
        'encryptionKey' => 'cbcb05daebf029205531f5496ee11cf7b71785b96f07523de7377835e2255eb6d365bd1e0e7d67dc2d9bfe42aac0fe38',
        'exceptionalErrors' => 4096,
        'features' => [
            'frontend.cache.autoTagging' => true,
            'security.system.enforceAllowedFileExtensions' => true,
        ],
        'sitename' => 'FluidTYPO3.org',
        'systemMaintainers' => [
            2,
        ],
        'trustedHostsPattern' => '.*',
    ],
];
