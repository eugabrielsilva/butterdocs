# Welcome
_Welcome to ButterDocs!_

> You are now ready to create tasty documentations.

### Getting started
Setup your documentation by creating Markdown files inside the `docs` folder. Each root folder represents a version of your application.

By default, the `home.md` file will be the main entrance of your documentation.

Use the `menu.md` file to customize your sidenav menu and links.

That's all. ButterDocs will do the rest for you.

### Dynamic tags
In your Markdown files you can use a few tags that will be replaced by dynamic values. These tags are:

- `\%%version%%` - Replaces the current docs version being viewed
- `\%%latest%%` - Replaces the latest docs version
- `\%%app%%` - Replaces the application title
- `\%%route%%` - Replaces the current route address

### Customization
In the `src/config.php` file you can edit your documentation settings.