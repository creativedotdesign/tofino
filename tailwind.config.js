module.exports = {
  content: [
    './header.php',
    './footer.php',
    './404.php',
    './functions.php',
    './inc/**/*.php',
    './templates/**/*.php',
    './src/public/svgs/**/*.svg',
    './src/**/*.vue',
  ],
  theme: {
    container: {
      center: true,
    },
    fontFamily: {
      roboto: ['Roboto'],
    },
  },
  plugins: [
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
