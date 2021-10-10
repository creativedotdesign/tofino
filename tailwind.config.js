module.exports = {
  mode: 'jit',
  purge: {
    content: [
      './*.php',
      './inc/**/*.php',
      './templates/**/*.php',
      './src/vue/*.vue',
      './src/styles/safelist.txt',
    ],
  },
  theme: {
    container: {
      center: true,
    },
  },
  plugins: [require('@tailwindcss/aspect-ratio'), require('@tailwindcss/forms')],
};
