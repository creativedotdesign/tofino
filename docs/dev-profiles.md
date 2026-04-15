# Dev Profiles

Tofino uses Vite profiles for local and tunnel-based development.

## Profiles

- `local` (default): Standard local Vite HMR workflow.
- `tunnel`: Uses a Cloudflare tunnel for single-origin public HTTPS testing.

## Commands

```bash
npm run dev
npm run dev:tunnel
npm run build
npm run build:watch
npm run preview
```

## Environment Variables

Core:

- `VITE_ASSET_URL`: Your local site URL used for proxying (for example `https://tofino.test`).
- `VITE_THEME_PATH`: Theme path used for production asset base URL.
- `VITE_DEV_PROFILE`: `local` or `tunnel`.

Tunnel-specific (used with `VITE_DEV_PROFILE=tunnel`):

- `VITE_TUNNEL_HOSTNAME`: Public tunnel hostname, for example `dev.example.com`.
- `VITE_TUNNEL_URL`: Optional full public URL override, for example `https://dev.example.com`.
- `VITE_TUNNEL_NAME`: Cloudflare tunnel name. Default: `tofino-dev`.
- `VITE_TUNNEL_ID`: Cloudflare tunnel UUID.
- `VITE_TUNNEL_PORT`: Local Vite port exposed through Cloudflare. Default: `5173`.

## Cloudflare Tunnel Setup

1. `cloudflared tunnel create <your-tunnel-name>`
2. `cloudflared tunnel route dns <your-tunnel-name> <your-hostname>`
3. Copy the tunnel UUID from `cloudflared tunnel list` into `VITE_TUNNEL_ID`.
4. Set tunnel env vars in `.env`.
5. Run `npm run dev:tunnel`.

## Notes

- `dist/hot` is generated in dev and consumed by the theme asset loader.
- In production, assets are loaded from `dist/.vite/manifest.json`.
- Vite defaults to port `5173` unless configured otherwise.
