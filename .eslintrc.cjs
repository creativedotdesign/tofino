module.exports = {
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/eslint-recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:vue/vue3-recommended',
    'plugin:prettier/recommended',
    'plugin:cypress/recommended',
  ],
  globals: {
    browser: true,
    tofinoJS: true,
  },
  parserOptions: {
    parser: '@typescript-eslint/parser',
  },
  plugins: ['@typescript-eslint'],
  env: {
    'vue/setup-compiler-macros': true,
    node: true,
    es2021: true,
    es6: true,
  },
};
