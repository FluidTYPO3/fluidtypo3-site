<?php
(function () {
    $logConfiguration =& $GLOBALS['TYPO3_CONF_VARS']['LOG'];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['rootline']['backend'] =
        \TYPO3\CMS\Core\Cache\Backend\ApcuBackend::class;
    unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['rootline']['options']['compression']);

    $logConfiguration['TYPO3']['CMS']['deprecations']
        ['writerConfiguration'][\Psr\Log\LogLevel::NOTICE][\TYPO3\CMS\Core\Log\Writer\FileWriter::class]
        ['disabled'] = true;

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_client']['dsn'] = getenv('SENTRY_DSN');
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] =
        Networkteam\SentryClient\ProductionExceptionHandler::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = Networkteam\SentryClient\DebugExceptionHandler::class;

    if (getenv('IS_DDEV_PROJECT')) {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = 1;
    }

    $getRequiredEnv = static function (string $name): string {
        $value = getenv($name);
        if ($value === false || $value === '') {
            throw new \RuntimeException(sprintf('Missing required environment variable "%s".', $name), 1747429751);
        }

        return $value;
    };

    $GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword'] = $getRequiredEnv(
        'TYPO3_INSTALL_TOOL_PASSWORD'
    );
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = $getRequiredEnv(
        'TYPO3_SYS_ENCRYPTION_KEY'
    );
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $getRequiredEnv(
        'TYPO3_DEFAULT_MAIL_FROM_EMAIL_ADDRESS'
    );
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $getRequiredEnv(
        'TYPO3_DEFAULT_MAIL_FROM_EMAIL_NAME'
    );
    $mailTransportSendmailCommand = getenv('TYPO3_MAIL_TRANSPORT_SENDMAIL_COMMAND');
    if ($mailTransportSendmailCommand !== false && trim($mailTransportSendmailCommand) !== '') {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_sendmail_command'] = trim($mailTransportSendmailCommand);
    }

    $databaseConnection = [
        'charset' => 'utf8mb4',
        'dbname' => $getRequiredEnv('TYPO3_DB_NAME'),
        'driver' => getenv('TYPO3_DB_DRIVER') ?: 'mysqli',
        'host' => $getRequiredEnv('TYPO3_DB_HOST'),
        'initCommands' => 'SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\')), '
            . 'collation_connection=\'utf8mb4_unicode_ci\';',
        'password' => $getRequiredEnv('TYPO3_DB_PASSWORD'),
        'defaultTableOptions' => [
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'user' => $getRequiredEnv('TYPO3_DB_USER'),
    ];

    if (getenv('TYPO3_DB_UNIX_SOCKET')) {
        $databaseConnection['unix_socket'] = getenv('TYPO3_DB_UNIX_SOCKET');
    }

    $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] = $databaseConnection;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['folderCreateMask'] = '0775';

    $projectPath = \TYPO3\CMS\Core\Core\Environment::getProjectPath();
    $deploymentRootPath = dirname(dirname($projectPath));
    if (basename(dirname($projectPath)) === 'releases' && is_dir($deploymentRootPath . '/shared')) {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'] ??= [];
        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'][] = $deploymentRootPath . '/shared/';
    }
})();
