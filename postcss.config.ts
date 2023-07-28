interface PostCSSConfig {
  plugins: {
    [key: string]: object;
  };
}

const postcssConfig: PostCSSConfig = {
  plugins: {
    'postcss-import': {},
    'tailwindcss/nesting': {},
    tailwindcss: {},
    autoprefixer: {},
  },
};

module.exports = postcssConfig;
