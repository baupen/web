# Roadmap

Features which fit the vision of the product, and have been requested by customers.

## Currently being worked on

### Protocol

Allow to track context of issues, craftsman and construction sites.

Implementation is finished somewhat, with some open issues remaning. However, it is ready for testing to get customer feedback. Before full release, issues need to be resolved or conciously be set back.

Usability:
- [ ] The empty protocol looks weird, both on craftsman / construction site. Add a help alert there?
- [ ] Customer feedback indicates craftsman protocol is useful to see within the issues protocol. However, holistic solution to integrate next higher context into the protocol (issues integrates CS and craftsman, craftsman integrates CS) seems to not work well. How to consiliate?
- [ ] Customer usage implies that last protocol entry useful to see in overviews. However, difficult to implement / visualize. Is this really necessary?
- [ ] The "Last activity" checkbox in the issues table excludes protocol activity, which might be surprising.

Technical:
- [ ] The UI architecture was compromised; in the 'View' folder, now also API requests are done.
- [ ] Translations are not yet done


### Other upcoming features

wait for protocol:
- let craftsman add their own entries

others:
- Add issue online, incl. placing of issues on map
- Store e-mail template, but without sending email
- Allow to add / manage reminders per construction site, and provide access in overview


## Future improvements

Dashboard:
- The feed on the dashboard is probably not useful in the state it is now.
- Refactor when example construction site is created: Only upon user request.

Register:
- Preview results in filter (preview expected number of results when applying new filter criteria)

Report:
- Add issue number coloring also to the table / to the photos

Edit:
- When adding an external construction manager, add context to the registration email about who did it, and for what construction site.

Framework:
- Frontend often cannot recover from failed backend calls (e.g. button remains disabled)