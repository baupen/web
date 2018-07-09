<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

require 'vendor/deployer/deployer/recipe/symfony4.php';

set('bin_dir', 'bin');
set('var_dir', 'var');

// Configuration
set('repository', 'git@github.com:mangelio/app.git');
set('shared_files', array_merge(get('shared_files'), ['var/data.sqlite']));
set('shared_dirs', array_merge(get('shared_dirs'), ['public/upload', 'public/generated']));
set('symfony_env_file', '.env');
set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts');
set('env_file_path', '.env');

// import servers
inventory('servers.yml');

//stages: dev, testing, production
set('default_stage', 'dev');
//only keep two releases
set('keep_releases', 2);

//use php 7.2
set(
    'bin/php',
    '/usr/local/php72/bin/php'
);

desc('Installing vendors');
task('deploy:vendors', function () {
    run('cd {{release_path}} && {{bin/composer}} {{composer_options}}', ['timeout' => 400]);
});

//build yarn stuff & upload
desc('Bundling locally css/js and then uploading it');
task('frontend:build', function () {
    runLocally('yarn install');
    runLocally('yarn upgrade');
    runLocally('yarn run encore production');
    runLocally('rsync -azP public/dist {{user}}@{{hostname}}:{{release_path}}/public');
})->desc('Build frontend assets');

// kill php processes to ensure symlinks are refreshed
desc('Refreshing symlink by terminating any running php processes');
task('deploy:refresh_symlink', function () {
    try {
        run('killall -9 php-cgi'); //kill all php processes so symlink is refreshed
    } catch (\Exception $e) {
        //fails if no active processes; therefore no problem
    }
})->desc('Refreshing symlink');

desc('Loading fixtures');
task('database:fixtures', function () {
    if ('dev' === get('branch')) {
        run('cd {{release_path}} && {{bin/composer}} install --no-scripts --optimize-autoloader');
        run('{{bin/console}} doctrine:fixtures:load -q --env=dev');
        writeln('fixtures executed');
    }
});

desc('print any warnings');
task('deploy:configure', function () {
    //fixtures deploy if on dev branch
    if ('dev' === get('branch')) {
        writeln('[WARNING] deploying dev branch; executing fixtures. STOP DEPLOYING IMMEDIATELY IF YOU DO NOT EXPECT / UNDERSTAND THIS MESSAGE.');
    }
});


//automatic till vendors comand
desc('Deploy project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors'
]);

//shows infos to the user
after('deploy:info', 'deploy:configure');

//add the other tasks
after('deploy:vendors', 'frontend:build');
after('database:migrate', 'database:fixtures');
after('deploy:vendors', 'database:migrate');
after('deploy:vendors', 'deploy:cache:clear');
after('deploy:cache:clear', 'deploy:cache:warmup');
after('deploy:cache:warmup', 'deploy:symlink');
after('deploy:symlink', 'deploy:refresh_symlink');
after('deploy:refresh_symlink', 'deploy:unlock');
after('deploy:unlock', 'cleanup');