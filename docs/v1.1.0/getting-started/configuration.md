# Configuration
Your documentation configuration can be found at the root `config.php` file. These options are shared between all versions of your docs website.

The options within this file are:

`application`
Your application title, basically the name that will be displayed in the documentation titles.

`generate_menu`
Enables the automatic sidenav menu generation, if the custom menu file for the current version is not provided.

`git_edit`
Enables the "Edit this page on GitHub" button.
When enabled, this button will take the user directly to edit the current page in your application repository on GitHub.

`git_url`
Your application GitHub edit URL, including the branch.
Example: https://github.com/eugabrielsilva/butterdocs/edit/master

`md_breaks`
Enables the support for single line breaks in your Markdown files.

`md_urls`
Enables the automatic URL link generation in your Markdown files.

`search`
Enables search feature to search in docs content.