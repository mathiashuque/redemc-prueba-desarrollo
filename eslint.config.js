import js from '@eslint/js';
import globals from 'globals';

export default [
  {
    ignores: ['vendor/**', 'node_modules/**', '.claude/**'],
  },
  js.configs.recommended,
  {
    files: ['public/js/**/*.js'],
    languageOptions: {
      // ES2020 sin módulos: compatible con los navegadores actuales sin build.
      ecmaVersion: 2020,
      sourceType: 'script',
      globals: {
        ...globals.browser,
        ...globals.jquery,
      },
    },
    rules: {
      eqeqeq: 'error',
      'no-var': 'error',
      'prefer-const': 'error',
    },
  },
];
