Introduction
======
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

deploy

using the following libraries:
 - `friendsofphp/php-cs-fixer` to fix code styling issues
  
frontend building tools:
 - `@symfony/webpack-encore` for the encore provided by symfony
 - `jquery` to simplify DOM access
 - `bootstrap-sass` bootstrap for basic css styling
 - `font-awesome` font with icons
 - `sass-loader node-sass` to enable the sass precompiler
 
after first pull, execute from project root:
 - `yarn install` installs npm dependencies 
 - `composer install` installs php dependencies
 - `yarn encore dev` builds css / js files
 - `php bin/console doctrine:fixtures:load` loads sample data & user
 
if you're developing in the backend:
 - `php bin/console server:run` #starts the symfony server
 
if you're developing the frontend (css/js), execute afterwards:
 - `yarn encore dev-server` #serves as a proxy between the symfony server & the webpage displayed in the browser
 - edit files in web/assets/sass or web/assets/js, save them to see the change instantly in the browser
 - test error templates inside TwigBundle/views by accessing `/_error/404` and `/_error/500`
 
if you want to login as an admin
 - go to /login
 - use the user `info@mangel.io` with pass `asdf1234`
 
symfony-cmd:
 - `doctrine:migrations:diff` to generate the migration class
 - `doctrine:migrations:migrate` to execute all migrations
 - `doctrine:fixtures:load` to load fixtures

cmd:
- `phpunit` to execute the unit tests
- `vendor/bin/php-cs-fixer fix` to fix code style issues
 
deploy:
deployment can be done with composer

server requirements are ghostscript (`gs`) and any other dependencies composer.json requires

 - rename `servers_template.yml` to `servers.yml` & fill out server infos
 - execute `dep deploy [ENVIRONMENT]`, replacing `[ENVIRONMENT]` by ether `dev`, `testing` or `production` (defaults to `dev`) 
 - if you deploy the fist time to production, while `deploy:composer` is running, set the `.env` file in `/shared/.env`
    
if you're setting up deployment on a new server
 - `cat ~/.ssh/id_rsa.pub` to ensure you already have created an ssh key for yourself, if none:
    - `ssh-keygen -t rsa -b 4096 -C "info@famoser.ch"` generate a new key
    - `eval $(ssh-agent -s)` start the ssh agent
    - `ssh-add ~/.ssh/id_rsa` add the new key
 - add own ssh key to the server with `ssh-copy-id -i ~/.ssh/id_rsa.pub username@server.domain` 
 - connect to server with `ssh username@server.domain`
 - `cat ~/.ssh/id_rsa.pub` to display the sever ssh key, if none see above on how to create one
 - go to https://github.com/famoser/nodika/deploy_keys and add the server ssh key
 - point the web directory to `~/myurl.ch/ENV/current/web`
 - deploy!
 - you may want to check with `php bin/symfony_requirements` if your server fully supports symfony
