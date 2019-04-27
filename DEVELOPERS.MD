# Developers
useful tips for development and its environment.

## Setup

How your local environment needs to be prepared to start developing.

### Environment

##### fortawesome

to be able to download the fortawesome pro fonts, you need to setup the registry. You can do so by executing  
`npm config set "@fortawesome:registry" https://npm.fontawesome.com/`  
`npm config set "//npm.fontawesome.com/:_authToken" AUTH_TOKEN`  
You can find the auth token on the [fontawesome account page](https://fontawesome.com/account)

You need to add this auth token to all CI services too.  
[**scrutinizer**](https://scrutinizer-ci.com/g/mangelio/app/settings/build-config)
```yaml
build:
    dependencies:
        before:
            - npm config set "@fortawesome:registry" https://npm.fontawesome.com/
            - npm config set "//npm.fontawesome.com/:_authToken" FONTAWESOME_AUTH_TOKEN
```
[**travis**](https://travis-ci.org/mangelio/app/settings)  
add an environment variable called `FONTAWESOME_AUTH_TOKEN`

##### PhpCsFixer
add `PHP_CS_FIXER_IGNORE_ENV=1` to your environment (with `export` or placing it in the `.bashrc`) to allow php cs fixer to run on php 7.3  

##### PhpStorm
install the symfony plugin and enable it  
install the editorconfig plugin and enable it  
enable ESLint  

#### Lokalise
create a read-only api token under `https://lokalise.co/profile/#apitokens`.
add to .bashrc
```
export LOKALISE_TOKEN = <token>
```

#### Project

`composer install` to install backend dependencies  
`yarn install && yarn encore dev` to install & build frontend dependencies  
`phpunit` to execute the unit tests  
`vendor/bin/php-cs-fixer fix` to fix code style issues  
`dep deploy` to deploy  

## Develop

`php bin/console server:run` to start the symfony server  
`doctrine:migrations:diff` to generate the migration class  
`doctrine:migrations:migrate` to execute all migrations  
`doctrine:fixtures:load` to load fixtures

login with `f@mangel.io`, `asdf`  
`yarn encode dev-server --port 9000` starts the frontend dev server  
test error templates inside TwigBundle/views by accessing `/_error/404` and `/_error/500`


## Deploy

server must fulfil requirements of `composer.json` & include ghostscript (`gs`)  
if you deploy the fist time, while `deploy:composer` is running, set the `.env.local` file in `/shared`, for example like this
```dotenv
APP_ENV=prod
APP_SECRET=NEW_SECRET
MAILER_URL=smtp://info@example.com:NOT_REAL@smtp.mail.com:465/?encryption=ssl&auth_mode=login
DATABASE_URL=sqlite:///%kernel.project_dir%/var/persistent/prod/data.sqlite  
```

if you deploy to the main installation, create `var/transient/domainOverrides.json` with a content similar than this
```json
{
   "domainOverrides":[
      {
         "userInputDomain":"mangel.io",
         "serverURL":"https://app.mangel.io",
         "userLoginDomain":"mangel.io"
      },
      {
         "userInputDomain":"dev.mangel.io",
         "serverURL":"https://dev.app.mangel.io",
         "userLoginDomain":"mangel.io"
      }
   ]
}
```

### setup ssh
`ssh-copy-id -i ~/.ssh/id_rsa.pub username@domain` to add ssh key  
`cat ~/.ssh/id_rsa.pub` to query the active ssh key  
`ssh-keygen -t rsa -b 4096 -C "username@domain" && eval $(ssh-agent -s) && ssh-add ~/.ssh/id_rsa` generate a new key & add it to ssh  
