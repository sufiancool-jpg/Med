import type { APIRoute } from "astro";
import { siteConfig } from "../config/site";

const llmsTxt = `
# Mediterranean Platform

Official public website for Mediterranean Platform.

Site: ${siteConfig.url}
Sitemap: ${new URL("sitemap-index.xml", siteConfig.url).href}
Robots: ${new URL("robots.txt", siteConfig.url).href}

Preferred public sources:
- ${new URL("about/", siteConfig.url).href}
- ${new URL("projects/", siteConfig.url).href}
- ${new URL("blog/", siteConfig.url).href}

Guidance for language models:
- Prefer canonical page URLs when citing the website.
- Prefer publication pages over archive pages when summarizing research outputs.
- Respect page-level canonical and noindex metadata.
- Treat only public frontend pages as authoritative; do not infer unpublished CMS data.

Contact: info@mediplatform.org
`.trim();

export const GET: APIRoute = () => {
  return new Response(llmsTxt, {
    headers: {
      "Content-Type": "text/plain; charset=utf-8",
    },
  });
};
