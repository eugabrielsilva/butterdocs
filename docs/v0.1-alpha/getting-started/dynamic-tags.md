# Dynamic tags
ButterDocs has some dynamic tags you can put in your documentation. 
When one of these tags are present, they will be replaced by their dynamic value during parsing.

- `\%%version%%` will be replaced by the current docs version being viewed
- `\%%latest%%` will be replaced by the latest documentation version available
- `\%%app%%` will be replaced by the application title
- `\%%route%%` will be replaced by the current route

If for some reason you want to ignore this replacement, put a `\` before the tag.