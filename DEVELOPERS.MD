# Developers

## About the setup

The **Backend** is in PHP, using the symfony framework 5 with ApiPlatform 2 on top. It follows conventions of both frameworks as close as possible.

Backend technical dept:
- [ ] PHP 8.0 is out
- [ ] symfony will replace its authentication system, see here: https://symfony.com/doc/current/security/experimental_authenticators.html

The **Frontend** isin js, using Vue.JS 3. As there are fewer conventions out there, we need to follow our own ones.

| `assets/js/vue` | Content | Purpose |
| --- | --- | --- | --- |
| `.` | vue entry points | abstract navigation from layout |
| `./components` | purpose-specific layouts | abstract layout from content |
| `./components/Action` | user-initiated interactions | abstract interactions from input |
| `./components/Filter` | issue filters | abstract filter result from filtering |
| `./components/Form` | entity specific forms | abstract entity edit from interactions |
| `./components/Library` | project-independent resources | abstract clean encapsulation of patterns & layout from domain |
| `./components/View` | view structures (tables, grids) | abstract data display / filtering |
| `./services` | non-VueJS specific code | abstract validation, api & more |

As we do not use a store at the moment, all data must be stored within components. To avoid huge parent components, (a) push data to the smallest child (but keep it at the highest parent where multiple children use it) and (b) execute requests as low as possible.

Frontend technical dept:
- [ ] Translations need a clean concept. Its difficult to find a good compromise between purpose / domain ordering
- [ ] IssueTable & its filters do too much. Less than optimal UX & hard to maintain.
- [ ] Actions should execute the requests themselves

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

## Prepare local machine

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

### Testing with PHPStorm

You need to configure PHPStorm to use the `symfony/phpunit-bridge` as phpunit implementation. See here: https://stackoverrun.com/de/q/13027505
