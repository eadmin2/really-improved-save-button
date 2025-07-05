# Really Improved Save Button

A modern WordPress plugin that adds a powerful "Save" button to the Post Edit screen, allowing you to save and immediately perform your next action: return to the previous page, go to the next/previous post, view the post, or return to the posts list. Built for efficiency and a better editorial workflow.

## Features
- Adds a customizable "Save and Then..." button to the post editor
- Choose your next action: previous page, next/previous post, posts list, view post, and more
- Fully compatible with Gutenberg and Classic Editor
- Modern, user-friendly settings page
- Secure: follows WordPress best practices for sanitization, escaping, and permissions

## Installation
1. Download or clone this repository.
2. Copy the `really-improved-save-button` folder to your WordPress `wp-content/plugins/` directory.
3. In your WordPress admin, go to **Plugins** and activate **Really Improved Save Button**.

## Usage
- Edit any post or page. You will see the new "Save and Then..." button in the editor.
- Configure available actions and defaults in **Settings > Improved Save Button**.

## Development
This plugin uses Node.js for build tooling (e.g., JavaScript and SCSS compilation).

### Install dependencies
```
npm install
```

### Build assets
```
npm run build
```

### Development build (watch mode)
```
npm run dev
```

### Linting
```
npm run lint
```

## Contributing
Pull requests and issues are welcome! Please follow WordPress coding standards and ensure all code is secure and properly escaped/sanitized.

## License
This project is licensed under the GNU General Public License v3.0. See [COPYING.txt](COPYING.txt) for details. 