import type { APIRoute } from "astro";
import { readFile } from "node:fs/promises";
import { resolve } from "node:path";
import sharp from "sharp";
import { siteConfig } from "../../config/site";
import { getSiteSettings } from "../../lib/site-data";

const SOCIAL_IMAGE_WIDTH = 1200;
const SOCIAL_IMAGE_HEIGHT = 630;
const FALLBACK_IMAGE_PATH = resolve(process.cwd(), "src/assets/og-image.png");

const readFallbackImage = () => readFile(FALLBACK_IMAGE_PATH);

const readSourceImage = async (source: string) => {
  try {
    if (/^https?:\/\//i.test(source)) {
      const response = await fetch(source);

      if (!response.ok) {
        throw new Error(`Image request failed with ${response.status}`);
      }

      return Buffer.from(await response.arrayBuffer());
    }

    const publicPath = source.replace(/^\/+/, "");
    if (publicPath !== "" && !publicPath.split("/").includes("..")) {
      return await readFile(resolve(process.cwd(), "public", publicPath));
    }
  } catch (error) {
    console.warn(
      `[social-image] Could not load ${source}; using the bundled fallback image.`,
      error,
    );
  }

  return readFallbackImage();
};

export const GET: APIRoute = async () => {
  const siteSettings = await getSiteSettings();
  const configuredImage = siteSettings.seoDefaults.ogImage;
  const fallbackImage =
    typeof siteConfig.ogImage === "string"
      ? siteConfig.ogImage
      : siteConfig.ogImage.src;
  const sourceImage = await readSourceImage(configuredImage || fallbackImage);
  const socialImage = await sharp(sourceImage)
    .rotate()
    .flatten({ background: { r: 255, g: 255, b: 255 } })
    .resize({
      width: SOCIAL_IMAGE_WIDTH,
      height: SOCIAL_IMAGE_HEIGHT,
      fit: "contain",
      position: "center",
      background: { r: 255, g: 255, b: 255, alpha: 1 },
    })
    .png({ compressionLevel: 9 })
    .toBuffer();
  const responseBody = Uint8Array.from(socialImage);

  return new Response(responseBody, {
    headers: {
      "Content-Type": "image/png",
      "Cache-Control": "public, max-age=31536000, immutable",
    },
  });
};
