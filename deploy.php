<?php

namespace Deployer;

use Deployer\Host\Host;
use Symfony\Component\Dotenv\Dotenv;
use function Deployer\host;

require 'recipe/common.php';

// Project configuration
set('application', 'fluidtypo3.org');
set('repository', 'ssh://git@github.com/FluidTYPO3/fluidtypo3-site.git');
set('git_tty', false);

// Use git submodules
set('git_recursive', false);

// Shared directories (linked across releases)
set('shared_dirs', [
    'public/fileadmin',
    'var',
]);

// Shared files (linked across releases)
set('shared_files', [
    '.env',
]);

// Writable directories
set('writable_dirs', [
    'public/fileadmin',
    'var',
]);
// Force use of sudo when setting permissions
set('writable_use_sudo', true);

// Keep last 5 releases
set('keep_releases', 5);

// Composer options
set('composer_options', '--no-dev --prefer-dist --no-interaction --optimize-autoloader');

set('bin/php', function () {
    return '/usr/bin/php';
});

set('bin/composer', function () {
    return '/usr/local/bin/composer';
});

// Hosts
host('production')
    ->setHostname('10.0.0.1')
    ->setRemoteUser('claus')
    ->setDeployPath('/var/www/fluidtypo3.org')
    ->set('branch', 'main')
    ->set('labels', ['stage' => 'production']);

host('legacy')
    ->setHostname('10.0.0.1')
    ->setRemoteUser('claus')
    ->setDeployPath('/var/www/legacy.fluidtypo3.org')
    ->set('branch', 'main')
    ->set('labels', ['stage' => 'development']);

host('local')->setLabels(['stage' => 'local'])->setDeployPath(trim(shell_exec('pwd')));

// Sync tasks. Transfers DB and files from production server to the specified environment.
function buildRemoteSshCommand(Host $host, string $command) {
    return parse(
        implode(
            ' ',
            [
                'ssh',
                $host->getSshArguments(),
                $host->getRemoteUser() . '@' . $host->getHostname(),
                escapeshellarg($command)
            ]
        )
    );
};

function buildRemotePathToSharedFiles(Host $host): string
{
    $syncUser = $host->getRemoteUser();
    if ($configuredUser = $host->get('syncUser')) {
        $syncUser = $configuredUser;
    }
    return implode(
        '',
        [
            $syncUser,
            '@',
            $host->getHostname(),
            ':',
            $host->getDeployPath(),
            '/shared/'
        ]
    );
};

task('sync:database', function () {
    $productionHost = select('stage=production')[0] ?? null;
    if (!$productionHost) {
        throw error('Production host is not defined');
    }

    $targetHost = currentHost();

    if ($targetHost->getHostname() === $productionHost->getHostname()) {
        throw error('Please select a target host other the production host');
    }

    $typo3Cli = 'vendor/bin/typo3';
    if ($productionHost->get('legacy_console')) {
        $typo3Cli .= 'cms';
    }

    $dumpCommand = implode(
        ' ',
        [
            '{{bin/php}}',
            $productionHost->getDeployPath() . '/current/' . $typo3Cli,
            'database:export',
            '-e "cache_*"',
            '-e sys_log',
            #'|',
            #'gzip',
        ]
    );

    info('Copying database from ' . $productionHost->getHostname() . ' to ' . $targetHost->getHostname());

    on($productionHost, function() use ($productionHost, $targetHost, $dumpCommand, $typo3Cli) {
        within('{{current_path}}', function() use ($productionHost, $targetHost, $dumpCommand, $typo3Cli) {
            if ($targetHost->getHostname() === 'local') {
                // Run locally by ssh executing a dump command that is directly piped into the DDEV TYPO3 DB import.
                $command = buildRemoteSshCommand($productionHost, $dumpCommand);
                //writeln($command . ' | gunzip | ddev typo3 database:import');
                //writeln('ddev mysql < ~/temp.sql && rm ~/temp.sql');
                runLocally($command . ' | cat - > ~/temp.sql', [], 900);
                runLocally('ddev mysql < ~/temp.sql && rm ~/temp.sql', [], 900);
            } else {
                $typo3CliTarget = 'vendor/bin/typo3';
                if ($targetHost->get('legacy_console')) {
                    $typo3CliTarget .= 'cms';
                }
                $targetPath = $targetHost->getDeployPath();

                // Dump on production, pipe through gzip to file and transfer to target host.
                info('Dump and transfer to target host ' . $targetHost->getHostname());
                $targetSshCommand = buildRemoteSshCommand($targetHost, 'gunzip | cat - > temp.sql');
                run($dumpCommand . ' | ' . $targetSshCommand, [], 300);

                info('Import and cleanup on target host ' . $targetHost->getHostname());
                // Then import from that file and clean up after.
                $targetImportCommand = buildRemoteSshCommand(
                    $targetHost,
                    $targetPath . '/current/' . $typo3CliTarget . ' database:import < ~/temp.sql && rm ~/temp.sql'
                );
                //writeln($dumpCommand . ' | ' . $targetSshCommand);
                run($targetImportCommand, [], 900);
            }
        });
    });
});

task('sync:files', function () {
    $productionHost = select('stage=production')[0] ?? null;
    if (!$productionHost) {
        throw error('Production host is not defined');
    }

    $targetHost = currentHost();
    $toPath = $targetHost->getDeployPath();

    if ($targetHost->getHostname() === $productionHost->getHostname()) {
        throw error('Please select a target host other the production host');
    }

    info('Copying files from ' . $productionHost->getHostname() . ' to ' . $targetHost->getHostname());

    on($productionHost, function() use ($productionHost, $targetHost) {
        within('{{current_path}}', function () use ($productionHost, $targetHost) {
            $rsync = Deployer::get()->rsync;
            if ($targetHost->getHostname() === 'local') {
                // Simple rsync executed on local to copy files from remote shared dir to local fileadmin dir.
                $dirs = get('shared_dirs');
                $command = implode(
                    ' ',
                    [
                        'rsync -rcW --ignore-existing --no-compress ',
                        escapeshellarg(buildRemotePathToSharedFiles($productionHost) . 'public/fileadmin'),
                        './public/',
                    ]
                );
                //writeln($command);
                runLocally($command, [], 900);
            } else {
                // Run on remote host to rsync contents of the "shared" folder instead of copying from fileadmin.
                $command = implode(
                    ' ',
                    [
                        'rsync -rcW',
                        escapeshellarg('{{deploy_path}}/shared/public'),
                        escapeshellarg(buildRemotePathToSharedFiles($targetHost)),
                    ]
                );
                //writeln($command);
                run($command, [], 1800);
            }
        });
    });
});

task('sync:postsync', function() {
    on(currentHost(), function() {
        within('{{current_path}}', function () {
            if (currentHost()->getHostname() === 'local') {
                runLocally('ddev composer run-script typo3-postdeploy');
            } else {
                run('{{bin/composer}} run-script typo3-postdeploy');
            }
        });
    });
});

desc('Run safe TYPO3 database schema updates');
task('typo3:database_updateschema', function () {
    within('{{release_path}}', function () {
        run('{{bin/php}} vendor/bin/typo3 database:updateschema safe --no-interaction');
    });
});

desc('Flush TYPO3 caches');
task('typo3:cache_flush', function () {
    within('{{release_path}}', function () {
        run('{{bin/php}} vendor/bin/typo3 cache:flush --no-interaction');
    });
});

// Fix permissions after deployment
desc('Fix directory and file permissions');
task('typo3:fix_permissions', function () {
    #run('sudo chmod -R 777 {{deploy_path}}/shared/var/cache');
    run('chmod a+w {{current_path}}/config/system/settings.php');
});

// Main deploy task
desc('Deploy the project');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'typo3:database_updateschema',
    'typo3:database_normalize_collations',
    'deploy:publish',
    'typo3:fix_permissions',
]);

// Rollback message
after('deploy:symlink', 'typo3:cache_flush');
after('deploy:failed', 'deploy:unlock');


task('sync', [
    'sync:database',
    'sync:files',
])
    ->once()
    ->desc('Sync DB and files from production');
