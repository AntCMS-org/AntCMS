import "./TinyZoom";

import "bootstrap/js/dist/menu.js";
import "bootstrap/js/dist/drawer.js";

import "prismjs";
import 'prismjs/components/prism-markup-templating';
import "prismjs/components/prism-yaml";
import "prismjs/components/prism-php";
import "prismjs/components/prism-markup";
import "prismjs/components/prism-markdown";
import "prismjs/components/prism-bash";

let themeToggle = null;
const colorScheme = window.matchMedia('(prefers-color-scheme: dark)');

const storedTheme = () => {
  try {
    return window.localStorage.getItem('antcms-theme');
  } catch {
    return null;
  }
};

const currentTheme = () => document.documentElement.getAttribute('data-bs-theme')
  || (colorScheme.matches ? 'dark' : 'light');

const setTheme = (theme, persist) => {
  document.documentElement.setAttribute('data-bs-theme', theme);
  document.documentElement.style.colorScheme = theme;

  if (themeToggle) {
    themeToggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
  }

  if (persist) {
    try {
      window.localStorage.setItem('antcms-theme', theme);
    } catch {
    }
  }
};

// The bundle may be included without defer; bind after the DOM exists either way.
const initThemeToggle = () => {
  themeToggle = document.getElementById('theme-toggle');

  if (!themeToggle) {
    return;
  }

  themeToggle.setAttribute('aria-pressed', currentTheme() === 'dark' ? 'true' : 'false');
  themeToggle.addEventListener('click', () => {
    setTheme(currentTheme() === 'dark' ? 'light' : 'dark', true);
  });
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initThemeToggle, { once: true });
} else {
  initThemeToggle();
}

colorScheme.addEventListener('change', (event) => {
  if (!storedTheme()) {
    setTheme(event.matches ? 'dark' : 'light', false);
  }
});
