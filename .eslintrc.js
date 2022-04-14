module.exports = {
  extends: ['eslint:recommended', 'plugin:@typescript-eslint/eslint-recommended', 'plugin:@typescript-eslint/recommended', 'plugin:vue/vue3-recommended', 'prettier'],
  globals: {
    browser: true,
    tofinoJS: true,
  },
  parserOptions: {
    parser: '@typescript-eslint/parser'
  },
  plugins: ["@typescript-eslint"],
  env: {
    node: true,
    es2021: true,
    es6: true,
  },
};
