# 📄 LMS React Page Builder

![React](https://img.shields.io/badge/React-18+-61DAFB.svg?logo=react)
![TypeScript](https://img.shields.io/badge/TypeScript-Ready-3178C6.svg?logo=typescript)
![LMS](https://img.shields.io/badge/Category-E--Learning-green.svg)

A dynamic, component-based frontend Page Builder built with React, specifically tailored for Learning Management Systems (LMS). It empowers administrators and instructors to visually construct course pages, landing pages, and educational content.

## 🌟 Features

*   **Component-Based Architecture:** Easily add, remove, and reorder page elements (Text blocks, Video players, Quizzes, Image galleries).
*   **Dynamic Rendering:** Parses stored JSON/configuration structures into fully functional React components.
*   **LMS Integration Ready:** Built to communicate seamlessly with LMS backends (PHP/Node) via REST/GraphQL APIs using token-based authentication.
*   **Responsive UI:** Ensures that created pages look great on desktop, tablet, and mobile devices.

## ⚙️ Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/canekremdura/Lms-react-page-builder.git
    cd Lms-react-page-builder
    ```

2.  **Install dependencies:**
    Using npm or yarn:
    ```bash
    npm install
    # or
    yarn install
    ```

3.  **Environment Variables:**
    Copy the sample environment file and adjust your API endpoints if necessary.

## 🚀 Development

Start the local development server:

```bash
npm run dev
# or
yarn dev
```
Navigate to `http://localhost:3000` (or the specified port) in your browser.

## 📦 Build for Production

To create an optimized production build to integrate into your LMS dashboard:

```bash
npm run build
```

## 🤝 Contributing
Contributions are welcome! Please open an issue to discuss proposed changes before submitting a Pull Request.
