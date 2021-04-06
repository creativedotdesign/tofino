module.exports = {
  mode: 'jit',
  purge: {
    content: [
      './*.php',
      './inc/**/*.php',
      './templates/**/*.php',
      './assets/vue/*.vue',
      './assets/styles/safelist.txt',
    ],
  },
  theme: {
    container: {
      center: true,
    },
  },
  plugins: [
    require('tailwindcss-pseudo-elements'),
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/forms'),
  ],
};
