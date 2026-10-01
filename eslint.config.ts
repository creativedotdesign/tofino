import { defineConfig, globalIgnores } from 'eslint/config';
import js from '@eslint/js';
import tseslint from 'typescript-eslint';
import pluginVue from 'eslint-plugin-vue';
import pluginVueA11y from 'eslint-plugin-vuejs-accessibility';
import eslintPluginPrettierRecommended from 'eslint-plugin-prettier/recommended';
import globals from 'globals';

export default defineConfig([
  // Ignore patterns
  globalIgnores(['dist/**', 'node_modules/**', 'vendor/**']),

  // Base JavaScript config
  js.configs.recommended,

  // TypeScript configs
  ...tseslint.configs.recommended,

  // Vue configs
  ...pluginVue.configs['flat/recommended'],

  // Vue accessibility: alt text, labels, click handlers on non-interactive
  // elements, ARIA roles/props. Catches modal and form defects in templates
  // before they reach the browser audits.
  ...pluginVueA11y.configs['flat/recommended'],

  // Custom rules
  {
    files: ['**/*.{js,ts,vue}'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
        ...globals.es2021,
        tofinoJS: 'readonly',
        acf: 'readonly',
      },
      parserOptions: {
        parser: tseslint.parser,
      },
    },
    rules: {
      // Add your custom rules here
    },
  },

  // Vue-specific overrides — required so vue-eslint-parser delegates
  // <script lang="ts"> blocks to the TypeScript parser
  {
    files: ['**/*.vue'],
    languageOptions: {
      parserOptions: {
        parser: tseslint.parser,
      },
    },
  },

  // Prettier config (must be last)
  eslintPluginPrettierRecommended,
]);
