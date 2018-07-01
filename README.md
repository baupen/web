# Introduction
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE) 
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fmangelio%2Fapp.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fmangelio%2Fapp?ref=badge_shield)
[![Build Status](https://travis-ci.org/mangelio/app.svg?branch=master)](https://travis-ci.org/mangelio/app)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mangelio/app/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mangelio/app/?branch=master)
[![codecov](https://codecov.io/gh/mangelio/app/branch/master/graph/badge.svg)](https://codecov.io/gh/mangelio/app) 



mangel.io aims to make the issue management on a construction site more pleasing and straight-forward.


## Useful commands

##### symfony-cmd
`php bin/console server:run` to start the symfony server  
`doctrine:migrations:diff` to generate the migration class  
`doctrine:migrations:migrate` to execute all migrations  
`doctrine:fixtures:load` to load fixtures

##### cmd
`composer install` to install backend dependencies  
`yarn install && yarn encore dev` to install & build frontend dependencies  
`phpunit` to execute the unit tests  
`vendor/bin/php-cs-fixer fix` to fix code style issues  
`dep deploy` to deploy  

##### develop
login with `info@mangel.io`, `asdf`  
`yarn encode dev-server` starts the frontend dev server  
test error templates inside TwigBundle/views by accessing `/_error/404` and `/_error/500`

##### deploy
server must fulfil requirements of `composer.json` & include ghostscript (`gs`)  
if you deploy the fist time, while `deploy:composer` is running, set the `.env` file in `/shared/.env`  
 
##### ssh
`ssh-copy-id -i ~/.ssh/id_rsa.pub username@domain` to add ssh key  
`cat ~/.ssh/id_rsa.pub` to query the active ssh key  
`ssh-keygen -t rsa -b 4096 -C "username@domain" && eval $(ssh-agent -s) && ssh-add ~/.ssh/id_rsa` generate a new key & add it to ssh  

## git hooks
##### pre-commit
```
#!/bin/sh
./vendor/bin/php-cs-fixer fix --dry-run -v > /dev/null 2>&1
status=$?

if [ "$status" = 0 ] ; then
    exit 0
else
    ./vendor/bin/php-cs-fixer fix > /dev/null 2>&1
    git add *
    echo 1>&2 "Found not properly formatted files. php-cs-fixer
was run."
    exit 0
fi

```


## License
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fmangelio%2Fapp.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2Fmangelio%2Fapp?ref=badge_large)