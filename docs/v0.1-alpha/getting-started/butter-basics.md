# Butter basics
- ButterDocs uses a flavored Markdown parser to format your documentation files in real time.

- Your docs files are stored into `docs` folder.

- Inside the first level of the `docs` folder, you must create a folder for **each version of your application**.

- In the version folder, place your `.md` files. You can also split the files into folders, as you wish.

- ButterDocs will use the folder and file names to create the corresponding URL routes, so remember to use valid URL characters.
We recommend using only letters and dashes on the filenames.

> **Important:** In case-sensitive hosts you may run into problems when using multiple cases in your filenames. To avoid this, use only lowercased names.

### Special files
There are two files inside each version folder that will be treated differently:

- The `home.md` file will be the entry point of your documentation. This page will be displayed as the index/welcome page of the current version.
- The `_menu.md` file will hold the sidenav menu content. This is were you can list your documentation topics.

### Example of folder structure
```plaintext
└── docs
    └── v0.5
        ├── _menu.md
        ├── home.md
        └── introduction
            ├── getting-started.md
            ├── configuration.md
            ├── ...
        └── the-basics
            ├── ...
    └── v1.0
    └── ...
```