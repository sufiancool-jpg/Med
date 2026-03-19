import rss from "@astrojs/rss";
import { siteConfig } from "../config/site";
import { getPublicationBySlug, getPublications } from "../lib/site-data";

export async function GET(context) {
  const publications = await getPublications();

  return rss({
    title: siteConfig.name,
    description: siteConfig.description,
    site: context.site,
    items: await Promise.all(
      publications
        .sort((a, b) => b.pubDate.valueOf() - a.pubDate.valueOf())
        .map(async (publication) => {
          const detail = await getPublicationBySlug(publication.slug);
          const content =
            detail?.source === "wordpress"
              ? detail.contentHtml
              : publication.description;

          return {
            title: publication.title,
            pubDate: publication.pubDate,
            description: publication.description,
            link: `/blog/${publication.slug}/`,
            content,
            customData: publication.author
              ? `<author>${publication.author}</author>`
              : undefined,
          };
        }),
    ),
    customData: `<language>${siteConfig.locale}</language>`,
  });
}
