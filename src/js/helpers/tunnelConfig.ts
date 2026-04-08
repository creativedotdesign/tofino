type TunnelEnv = {
  VITE_TUNNEL_URL?: string;
  VITE_TUNNEL_HOSTNAME?: string;
};

type TunnelConfig = {
  publicUrl: string | undefined;
  hostname: string | undefined;
};

/**
 * Resolves the tunnel public URL and hostname from environment variables.
 *
 * Priority: VITE_TUNNEL_URL (explicit full URL) > VITE_TUNNEL_HOSTNAME (bare hostname → https).
 */
export function resolveTunnelConfig(env: TunnelEnv): TunnelConfig {
  const publicUrl = (() => {
    const explicitUrl = env.VITE_TUNNEL_URL?.trim();
    if (explicitUrl) {
      return explicitUrl.replace(/\/+$/, '');
    }

    const hostname = env.VITE_TUNNEL_HOSTNAME?.trim();
    if (hostname) {
      return `https://${hostname}`;
    }

    return undefined;
  })();

  const hostname = (() => {
    if (!publicUrl) return undefined;
    try {
      return new URL(publicUrl).hostname;
    } catch {
      return undefined;
    }
  })();

  return { publicUrl, hostname };
}
