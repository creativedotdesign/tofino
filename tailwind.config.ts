import type { Config } from 'tailwindcss';

export default {
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
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    require('@tailwindcss/forms')({
      strategy: 'class',
    }),
  ],
} satisfies Config;
