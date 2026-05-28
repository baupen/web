# Introduction
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE) 
[![PHP Composer](https://github.com/baupen/web/actions/workflows/php.yml/badge.svg)](https://github.com/baupen/web/actions/workflows/php.yml)
[![Node.js Encore](https://github.com/baupen/web/actions/workflows/node.js.yml/badge.svg)](https://github.com/baupen/web/actions/workflows/node.js.yml)
[![translated using lokalise](https://img.shields.io/badge/translations-lokalise.co-%23249BEE.svg)](https://lokalise.co) 

## About

Baupen makes issue management on construction sites more pleasing and straight-forward. It most notably improves defect management, acceptance checks and documentation. Both the requirements of the construction site manager and the craftsmen are fulfilled, to enable them work together transparently and efficiently.

It suggests the following workflow:
 - the construction manager creates issues for incomplete or wrongly executed tasks 
 - the construction manager supplements new issues (e.g. adds a deadline) and adds them to the issue register
 - the construction manager notifies craftsmen with new issues
 - the craftsman receives a personalised link with all currently open issues assigned to them
 - the craftsman executes the resulting tasks, and then marks the issues as resolved
 - the construction manager inspects the issues the craftsman resolved, and closes those which are completed to satisfaction

The first and the last step are best executed using the [iOS](https://github.com/baupen/iOS) or [Android](https://github.com/baupen/Android) apps.


Backend:
- Upgrade to symfony 7, api platform 4
- Rebuild security voters
- Database: Improve data types (use STRING instead of TEXT, use immutable datetime structures)
- Add phpstan static analysis & fix all issues
- Remove fixtures if possible (includes liip bundles)
- Simplify access control
- Refactor tests?

Frontend:
- Replace noty (noty longer maintained)
- Replace vue-i18n (high maintenance cost due to ever-evolving api/compilation/owner, overly complicated implementation)
- Data: Preload construction site data with dedicated js file, replace data injection by store for this general data.
- UI framework: Improve form fields abstraction, clearer loading indication (see meet-mvp)
- Refactor table implementation

UX:
- Introduce profile / password change page, where also weekly can be configured
- First-time flow: Create example construction site on user request. Add context to registration emails (e.g. when inviting external construction manager)
- Construction managers: Allow safe removal of construction manager, even if with some changes on construction site.

