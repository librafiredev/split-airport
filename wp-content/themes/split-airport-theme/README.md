# LibraFire WordPress Split Theme

## Overview

LibraFire Split Theme is designed to simplify project development. Efficient solutions are centralized, featuring conditional resource loading. It dynamically connects .scss and .js files for each page, optimizing resources and enhancing website performance. Compatible with performance analysis tools like PageSpeed Insights.

## Installation

1. Download WordPress from [here](https://wordpress.org/download/).
2. Install WordPress.
3. Add `.gitignore` to the project root.
4. Clone the Split theme into the themes folder:
   ```bash
   git clone http://git.librafire.com:2851/librafire/split-theme.git -b staging
   ```
5. Delete the `.git` folder from the Split theme folder.
6. Rename the theme folder from `split-theme` to `your-project-name-theme`.
7. Perform a case-sensitive search and replace in theme files:
   - Replace `Split` with `YourProjectName`.
   - Replace `split` with `yourprojectname`.
8. Update the `screenshot.png` in the theme to match your design.

## Required Plugins

- Advanced Custom Fields Pro

## Theme Folders and Files

- `__snippets`: PHP and JS scripts for optional inclusion.
- `assets`: Compiled SCSS and JS files.
- `blocks`: Folder containing blocks.
- `core`: Core theme functionalities.
- `dist`: Compiled CSS and JS files (do not manually edit).
- `includes`: PHP files with custom functions in `theme_functions.php`.
- `node_modules`: Node components.
- `template-parts`: Default WordPress templates.
- `templates`: Custom templates.

## Theme Configuration (`theme-config.php`)

- `WEBSITE_TYPE`: Choose between flexible content (0) and Gutenberg blocks (1).
- `ASSETS_VERSION`: Manage the version of CSS and JS assets.
- `CRITICAL_CSS_THRESHOLD`: Set the number of top-page blocks for critical CSS.
- `IN_DEVELOPMENT`: Toggle development mode. Disable before going live.
- `ACF_GOOGLE_API_KEY`: Enter your Google Maps API key.

## Compiling Assets

- Run `npm install` in the theme root.
- Start the compiler with `npm run dev`.

## Asset Modifications

Modify assets in the `assets` folder. Use Sass preprocessor and write critical CSS within

`/* critical:start */` 

and 

`/* critical:end */` tags. 

Import JS modules from `assets/components`.

All .scss and .js are written inside the appropriate blocks/sections. Also, all single/archive files will be automatically created within /assets/scss folder.

## Fonts

Fonts are located in `assets/fonts`. Use formats like WOFF, WOFF2, or others as needed. Load fonts through `includes/fonts.php`.

## Adding Blocks

1. Install the `lf-wp-cli` package with `npm install lf-wp-cli -g`.
2. To add a new block, run `lfwp bc --name "Block Name"` in the theme root.
3. Confirm with `y`.

## Deleting Blocks

To delete a block, execute `lfwp bd --name "Block Name"` in the theme root and confirm with `y`.

## Adding Custom Post Types/Taxonomies

Copy `register-custom-post-type.php` or `register-custom-taxonomy.php` from `__snippets/wp` to `includes/cpt`. Rename and adjust the names in the files.

## Site Deployment

- Connect the server to GIT for deployment.
- Dist folder, containing all assets, is in `.gitignore` to avoid conflicts.
- Set `IN_DEVELOPMENT` in `theme-config.php` to `false` for live servers.
- Regenerate asset files for live servers via `tools/regenerate assets` in the admin panel.
- Compile the dist folder on the server with `NPM INSTALL` and `NPM RUN PRODUCTION`.
- Ensure Node version is at least 18.0.0.

---

# Front End Best Practices

- Install node_modules using command: npm install

- Add fonts
    1. download font families you need
    2. go to website https://transfonter.org/, upload your font and select WOOF, WOOF2, TTF and EOT, also check "Fix vertical metrics"), after that convert fonts and download it
    3. copy all downloaded font files to folder /assets/fonts
    4. copy styles to file /assets/scss/global/_fonts.scss and make sure you hit relative path to font files within folder /assets/fonts

- Define all variables that meet the needs of the project (file: /assets/scss/global/_variables.scss)
    1. $container-width variable (this is your .container width based on provided design)
    2. colors
    3. $primary-font and $secondary-font
    4. font sizes

- Define typography (file: /assets/scss/global/_typography.scss)
    1. font sizes for p tag, headings (h1, h2, h3...)
    2. if every section have the same heading font size and line heaight, good practice is to define class, for exapmle .section-title within _typography.scss
