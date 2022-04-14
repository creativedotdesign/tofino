module.exports = {
  content: [
    './header.php',
    './footer.php',
    './404.php',
    './inc/**/*.php',
    './templates/**/*.php',
    './src/svgs/**/*.svg',
    './src/vue/*.vue',
  ],
  theme: {
    container: {
      center: true,
    },
  },
  plugins: [
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
