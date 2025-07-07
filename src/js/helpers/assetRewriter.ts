const devAssetRewriter = () => {
  const isDev = process.env.NODE_ENV === 'development';
  const devServerUrl = 'http://localhost:3000';

  return {
    name: 'vite-plugin-dev-asset-rewriter',
    enforce: 'post' as const, // Ensure this runs after other transformations
    transform(code: string, id: string) {
      // Exit early if not a CSS file or not in development mode
      if (!isDev || !id.endsWith('.css')) return null;

      // Use a more efficient regex to match and rewrite asset URLs
      const updatedCode = code.replace(
        /url\((['"]?)(\/[^'")]+)\1\)/g,
        (_, quote, assetPath) => `url(${quote}${devServerUrl}${assetPath}${quote})`
      );

      return updatedCode === code ? null : { code: updatedCode, map: null };
    },
  };
};

export default devAssetRewriter;
