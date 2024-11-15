export default [
  {
    ignores: ['dist/**'], // Example: ignore dist folder
  },
  {
    extends: [
      'eslint:recommended',
      'plugin:@typescript-eslint/eslint-recommended',
      'plugin:@typescript-eslint/recommended',
      'plugin:vue/vue3-recommended',
      'plugin:prettier/recommended',
    ],
    globals: {
      browser: 'readonly',
      tofinoJS: 'readonly',
    },
    plugins: ['@typescript-eslint'],
    languageOptions: {
      ecmaVersion: '2021',
      parser: '@typescript-eslint/parser',
      sourceType: 'module',
    },
    env: {
      'vue/setup-compiler-macros': true,
      node: true,
      es2021: true,
      es6: true,
    },
  },
];
