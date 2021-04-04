module.exports = {
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
    function ({ addUtilities }) {
      addUtilities(
        {
          '.empty-content': {
            content: "''",
          },
          '.content-none': {
            content: 'none',
          },
        },
        ['before', 'after']
      )
    },
  ],
}
