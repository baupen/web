# Introduction
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE) 
[![Build Status](https://travis-ci.com/mangelio/web.svg?branch=master)](https://travis-ci.com/mangelio/web)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mangelio/web/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mangelio/web/?branch=master)
[![Scrutinizer Coverage](https://scrutinizer-ci.com/g/mangelio/web/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mangelio/web/?branch=master)
[![translated using lokalise](https://img.shields.io/badge/translations-lokalise.co-%23249BEE.svg)](https://lokalise.co) 

## About
Mangel.io makes the issue management on a construction site more pleasing and straight-forward. 
Developed to fulfil both the requirements of the construction site manager and the craftsman to work together easily and efficiently.

It suggests the following workflow:
 - the manager inspects the construction site, and notices incomplete or broken things, and creates an issue for each 
 - the manager returns to his office and registers the newly created issues (adds supplemental information & adds them to the issue register)
 - the manager sends an email to all craftsman which have new issues or have not responded yet to issues from the register assigned to them
 - the craftsman receives the email with a special link which displays all currently open issues to which he is assigned to
 - on the construction site, using the link, he can resolve issues, or he had printed the issues in his office and contacts the manager after completion personally
 - the manager inspects the issues which the craftsman has resolved, and closes those which are completed to satisfaction

## Notice

_This is the next generation, currently unstable, version of mangel.io.
For the stable version, look at the branch `1.0`_

The target of the release is to make the project ready for big construction sites (<50'000 issues). In `1.0`, only up to 5'000 issues are well supported (some projects already have over 10'000 though).
As this requires a reimplementation of the API, various architectural challenges are tackled at the same time to make it worth while.  

The next version will feature:
- [x] considerably less cache usage (around 70% less than before)
- [x] more stable API scaling to many more issues (10x more than before estimated)
- [x] removed `Notes` (no one used it)
- [x] reimplementation of the login flow to ease boarding users
- [ ] ease authentication in App with a QR code
- [ ] dashboard features graph with issue progress
- [ ] foyer & register table of issues supports more issues & allows edit of more properties
- [ ] edit view simplified

Besides, some bigger maintenance tasks are completed:
- [x] migrate to symfony 5 (from 4)
- [x] migrate to VueJS 3 (from 2)
- [x] migrate to `symfony/mailer` (from swiftmailer)

Additionally, the environment changes:
- [x] update to php 7,4 (from 7.2)
- [x] use mysql (from sqlite)

For the user, the following changes:
- [x] fewer bugs / enhanced stability through improved code quality & less complexity
- [x] improved email templates reduce time needed to understand content
- [x] a new registration flow makes it easier for new users to get started

Milestone 1 (Refactor services):
- [x] analyse architectural issues with current implementation (too large cache, nonstandard API)
- [x] remove unused functionality (Notes)
- [x] remove `SyncService` to reduce complexity & much faster testing
- [x] remove next gen pdf generation as it will not be ready for some time
- [x] refactor services for clean code (easier to use abstractions, clear code flow). 
- [x] reimplement the login flow to ease boarding users

Milestone 2 (API):
- [x] setup `ApiPlatform`
- [x] simplify entities & improve naming
- [x] add nodes for construction sites
- [x] add nodes for craftsmen
- [x] add nodes for maps
- [x] add nodes for issues
- [x] add report
- [x] allow access to the API with tokens for constructionManager (iOS), craftsman & filter with `X-AUTHENTICATE`

Milestone 3 (Operations):
- [x] new vueJS setup
- [ ] use `famoser/agnes` for deployments
- [ ] add login QR token for app

Milestone 4 (UI)
- [ ] implement switch UI
- [ ] implement dashboard UI
- [ ] implement craftsman UI 
- [ ] implement foyer UI with scalable table
- [ ] implement register UI with same table as foyer
- [ ] implement edit UI with easier map edit
- [ ] finalize filter in API
- [ ] implement craftsman view
- [ ] implement filter view
- [ ] add graph of recent issues to dashboard

Milestone 5 (Migration & performance)
- [ ] write migration from sqlite to mysql
- [ ] test `app:cache:initialize` command
