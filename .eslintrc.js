module.exports = {
  extends: ['eslint:recommended', 'plugin:vue/vue3-recommended', 'prettier'],
  globals: {
    browser: true,
    tofinoJS: true,
  },
  env: {
    node: true,
    es2021: true,
  },
};
