# Developers

## About the setup

This project uses the symfony framework for its php backend, the VueJS framework for its JavaScript frontend, and ApiPlatform for its API.

backend:
- `symfony/sceleton`, `orm`, `translations`, `logging`, `dotenv`, `mailer`, `form`, `requirements-checker`, `templates` and `api`. For pdfs, `tecnickcom/tcpdf` is used, which will be replaced by `famoser/pdf-generator` in the future.
- dev dependencies include `encore`, `orm-fixtures`, `cs-fixer`, `security-checker`, `debug`, `simple-phpunit`, `brainmaestro/composer-git-hooks`.
- for deployment, `famoser/agnes` is used.

The previous setup also relied on:
- `fzaninotto/faker` for sample data
- `spatie/pdf-to-image` (unsure if ever used)
- `ramsey/uuid`(can probably be replaced by native php)
- `intervention/image` for resizing images

## Developing

setup:
- `composer install` to install backend dependencies  
- `yarn install` to install frontend dependencies  

developing:
- `symfony start` to start the symfony server  
- `yarn run dev-server` to start the frontend server
- visit `localhost:8000` and login with `f@mangel.io`, `asdf`  
- test error templates inside TwigBundle/views by accessing `/_error/404` and `/_error/500`

clean up:
- `vendor/bin/php-cs-fixer fix` to autoformat code
- `bin/phpunit` to execute tests

database commands:
- `doctrine:migrations:diff` to generate a new migration class  
- `doctrine:migrations:migrate` to execute all migrations  
- `doctrine:fixtures:load` to load fixtures

deployment:
- `vendor/bin/agnes release` to create a new release
- `vendor/bin/agnes deploy` to deploy a release or commitish  

## Troubleshooting

### fortawesome

to be able to execute `yarn install`, you need to download the fortawesome pro fonts. 
For this, you need to setup its registry. You can do so by executing  
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

### Lokalise

To be able to download translations, you need a lokalise API key.

create a read-only api token under `https://lokalise.co/profile/#apitokens`.
add to .bashrc
```
export LOKALISE_TOKEN=<token>
```

### Deployment

server must fulfil requirements of `composer.json` & include ghostscript (`gs`)  

a sample prod `.env.local` could look like this:

```dotenv
APP_ENV=prod
APP_SECRET=NEW_SECRET
MAILER_URL=smtp://info@example.com:NOT_REAL@smtp.mail.com:465/?encryption=ssl&auth_mode=login
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

### Setup ssh

`ssh-copy-id -i ~/.ssh/id_rsa.pub username@domain` to add ssh key  
`cat ~/.ssh/id_rsa.pub` to query the active ssh key  
`ssh-keygen -t rsa -b 4096 -C "username@domain" && eval $(ssh-agent -s) && ssh-add ~/.ssh/id_rsa` generate a new key & add it to ssh  
