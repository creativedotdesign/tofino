module.exports = {
  mode: 'jit',
  purge: {
    content: [
      './*.php',
      './inc/**/*.php',
      './src/svgs/**/*.svg',
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
  plugins: [
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/forms'),
    require('tailwindcss-autofill'),
    require('tailwindcss-text-fill'),
    require('tailwindcss-shadow-fill'),
    require('@tailwindcss/typography'),
  ],
};
