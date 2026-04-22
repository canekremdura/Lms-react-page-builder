# LMS React Page Builder

Dynamic page builder for LMS (Learning Management System) platforms. Create custom pages with drag-and-drop functionality.

## Features

- ✅ Drag-and-drop page builder
- ✅ Pre-built content blocks
- ✅ Custom component support
- ✅ Responsive design
- ✅ Real-time preview
- ✅ Save/Load templates
- ✅ LMS integration ready

## Tech Stack

- **Frontend:** React.js
- **Backend:** PHP
- **Styling:** CSS3
- **State Management:** React Context/Redux

## Installation

```bash
# Clone the repository
git clone https://github.com/canekremdura/LMS-REACT-PAGE-BUILDER.git

# Navigate to project directory
cd LMS-REACT-PAGE-BUILDER

# Install dependencies
npm install
composer install
```

## Usage

```bash
# Start development server
npm start

# Build for production
npm run build
```

## Available Components

- Text Block
- Image Gallery
- Video Player
- Quiz Component
- Course List
- Progress Bar
- Custom HTML

## Customization

You can create custom components by extending the base component class:

```jsx
import { BaseComponent } from './components/BaseComponent';

class CustomComponent extends BaseComponent {
  render() {
    // Your custom component logic
  }
}
```

## License

MIT License

## Author

**Can Ekrem Dura**

[GitHub Profile](https://github.com/canekremdura)

---

*Check out my other projects:*
- [csv-to-json-cli](https://github.com/canekremdura/csv-to-json-cli) - CSV to JSON converter
- [Mackolik-Bot](https://github.com/canekremdura/Mackolik-Bot) - Football match data scraper
- [wp-custom-variation-swatches](https://github.com/canekremdura/wp-custom-variation-swatches) - WooCommerce swatches
