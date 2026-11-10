import eslint from '@eslint/js';
import { defineConfig } from 'eslint/config';
import tseslint from 'typescript-eslint';
import vue from 'eslint-plugin-vue';
import vueA11y from 'eslint-plugin-vuejs-accessibility';
import jsdoc from 'eslint-plugin-jsdoc';
import stylistic from '@stylistic/eslint-plugin';
import globals from 'globals';
import prettier from 'eslint-config-prettier';

/**
 * Read-only quality checks for production TypeScript and Vue source.
 * vue-tsc owns the compiler check; async lint rules also use TypeScript's type information.
 */
const sourceFiles = ['*.ts', 'src/**/*.ts', 'modules/**/*.{ts,vue}', 'features/**/*.{ts,vue}'];

// Apply TypeScript's core-rule replacements to TypeScript inside Vue scripts too.
const typescriptConfigs = tseslint.configs.recommended.map((config) => ({
  ...config,
  files: sourceFiles,
}));

export default defineConfig([
  {
    ignores: [
      'node_modules/**',
      'vendor/**',
      'dist/**',
      'dist-release/**',
      'build/**',
      '.cache/**',
      '.playwright-cli/**',
    ],
  },
  {
    files: sourceFiles,
    extends: [
      eslint.configs.recommended,
      ...typescriptConfigs,
      ...vue.configs['flat/recommended'],
      ...vueA11y.configs['flat/recommended'],
    ],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: { ...globals.browser, tofinoJS: 'readonly', acf: 'readonly' },
      parserOptions: {
        parser: tseslint.parser,
        projectService: true,
        tsconfigRootDir: import.meta.dirname,
        extraFileExtensions: ['.vue'],
      },
    },
    plugins: {
      '@stylistic': stylistic,
      jsdoc,
    },
    settings: {
      jsdoc: { mode: 'typescript' },
    },
    rules: {
      // Check async correctness without enabling the full type-aware preset at once.
      '@typescript-eslint/no-floating-promises': 'error',
      '@typescript-eslint/no-misused-promises': 'error',

      // Require purposeful documentation on named implementations, including object handlers.
      // Inline map/filter/lifecycle callbacks stay outside this requirement.
      'jsdoc/require-jsdoc': [
        'error',
        {
          require: {
            FunctionDeclaration: true,
            MethodDefinition: true,
            ClassDeclaration: true,
            ArrowFunctionExpression: false,
            FunctionExpression: false,
          },
          contexts: [
            'VariableDeclarator > ArrowFunctionExpression',
            'VariableDeclarator > FunctionExpression',
            'Property > ArrowFunctionExpression',
            'Property > FunctionExpression',
          ],
          checkGetters: true,
          checkSetters: true,
          exemptEmptyFunctions: false,
          // Missing descriptions should be written by a person, not filled with empty blocks.
          enableFixer: false,
        },
      ],
      'jsdoc/require-description': 'error',

      // Keep decisions and assignments readable even when their bodies are short.
      'no-nested-ternary': 'error',
      'no-return-assign': ['error', 'always'],
      // Cally uses native Web Component slots, not Vue's legacy slot API.
      'vue/no-deprecated-slot-attribute': ['error', { ignoreParents: ['calendar-range'] }],

      // Separate declaration groups, completed blocks and return statements.
      '@stylistic/padding-line-between-statements': [
        'error',
        { blankLine: 'always', prev: ['const', 'let'], next: '*' },
        { blankLine: 'any', prev: ['const', 'let'], next: ['const', 'let'] },
        { blankLine: 'always', prev: 'block-like', next: '*' },
        { blankLine: 'always', prev: '*', next: 'return' },
      ],
    },
  },
  {
    files: ['*.ts', 'src/js/build/**/*.ts'],
    languageOptions: { globals: globals.node },
  },
  {
    files: ['tests/**/*.ts'],
    extends: [eslint.configs.recommended, ...tseslint.configs.recommended],
    languageOptions: { globals: { ...globals.node, ...globals.browser } },
  },
  {
    files: ['eslint.config.js', 'scripts/**/*.mjs'],
    extends: [eslint.configs.recommended],
    languageOptions: { globals: globals.node },
  },
  prettier,
  {
    files: sourceFiles,
    rules: {
      // Retain readable blocks after disabling formatting rules owned by Prettier.
      curly: ['error', 'all'],
      '@stylistic/brace-style': ['error', '1tbs', { allowSingleLine: false }],
      '@stylistic/max-statements-per-line': ['error', { max: 1 }],
    },
  },
]);
