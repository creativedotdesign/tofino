interface PostCSSConfig {
  plugins: {
    [key: string]: object;
  };
}

interface Asset {
  url: string;
}

const getPostCSSConfig = (env: string): PostCSSConfig => {
  /* eslint-disable */
  require('dotenv').config();

  const postcssConfig: PostCSSConfig = {
    plugins: {
      'postcss-import': {},
      'postcss-url': {
        url: (asset: Asset) => {
          if (env === 'production') {
            return asset.url.replace('$fonts', `${process.env.VITE_THEME_PATH}/dist/fonts`);
          } else {
            return asset.url.replace('$fonts', `${process.env.VITE_THEME_PATH}/src/public/fonts`);
          }
        },
      },
      'tailwindcss/nesting': {},
      tailwindcss: {},
      autoprefixer: {},
    },
  };

  return postcssConfig;
};

module.exports = getPostCSSConfig;
