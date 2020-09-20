# Introduction
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE) 
[![Build Status](https://travis-ci.com/mangelio/web.svg?branch=master)](https://travis-ci.com/mangelio/web)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mangelio/web/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mangelio/web/?branch=master)
[![codecov](https://codecov.io/gh/mangelio/web/branch/master/graph/badge.svg)](https://codecov.io/gh/mangelio/web) 
[![translated using lokalise](https://img.shields.io/badge/translations-lokalise.co-%23249BEE.svg)](https://lokalise.co) 

## About
Mangel.io makes the issue management on a construction site more pleasing and straight-forward. 
Developed to fulfil both the requirements of the construction site manager and the craftsman to work together easily and efficiently.

It suggests the following workflow:
 - the manager inspects the construction site, and notices incomplete or broken things, and creates an issue for each 
 - the manager returns to his office, reviews the newly created issues, and adds them to the issue register
 - the manager sends an email to all craftsman which have new issues or have not responded yet to issues from the register assigned to them
 - the craftsman receives the email with a special link which displays all currently open issues to which he is assigned to
 - on the construction site, using the link, he can respond to issues, or he had printed the issues in his office and contacts the manager after completion personally
 - the manager inspects the issues for which the craftsman already has responded and marks issues as completed which have been resolved

## Notice

_This is the next generation, currently unstable, version of mangel.io.
For the stable version, look at the branch `1.0`_

The target of the release is to make the project ready for big construction sites (<50'000 issues). In `1.0`, only up to 5'000 issues are well supported (some projects already have over 10'000 though).
As this requires a reimplementation of the API, various architectural challenges are tackled at the same time to make it worth while.  

The next version will feature:
- [x] considerably less cache usage (around 70% less than before)
- [x] use of `ApiPlatform` (instead of manually written API nodes)
- [x] removed next gen pdf generation as it will not be ready for some time
- [x] removed `SyncService` to reduce complexity & much faster testing
- [x] removed `Notes` to reduce complexity (users did not use it)
- [x] refactored services for clean code (easier to use abstractions, clear code flow)
- [ ] use of `famoser/agnes` for deployment (faster, more scalable deployments)
- [ ] reimplementation of the login flow to ease boarding users
- [ ] reimplementation of the UI to use new API

Besides, some bigger maintenance tasks are completed:
- [x] migrate to symfony 5 (from 4)
- [x] migrate to `symfony/mailer` (from swiftmailer)

Additionally, the environment changes:
- [x] update to php 7,4 (from 7.2)
- [x] use mysql (from sqlite)

Migration guide 1.0 to 2.0:
- Migrate sqlite to mysql
- Remove cache folder
- Regenerate cache with `app:cache:initialize` command
