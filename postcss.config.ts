import dotenvFlow from 'dotenv-flow';
import postcssImport from 'postcss-import';
import postcssUrl from 'postcss-url';
import tailwindcssNesting from 'tailwindcss/nesting';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';

interface PostCSSConfig {
  plugins: (string | { [key: string]: any })[];
}

interface Asset {
  url: string;
}

const getPostCSSConfig = (env: string): PostCSSConfig => {
  // Load environment variables
  dotenvFlow.config();

  // Define the PostCSS plugins as an array using ES imports
  const plugins = [
    postcssImport,
    postcssUrl({
      url: (asset: Asset) => {
        const fontPath =
          env === 'production'
            ? `${process.env.VITE_THEME_PATH}/dist/fonts`
            : `${process.env.VITE_THEME_PATH}/src/public/fonts`;
        return asset.url.replace('$fonts', fontPath);
      },
    }),
    tailwindcssNesting,
    tailwindcss,
    autoprefixer,
  ];

  return {
    plugins,
  };
};

export default getPostCSSConfig;
