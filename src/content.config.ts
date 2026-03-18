import { defineCollection, z } from "astro:content";
import { glob } from "astro/loaders";

const blog = defineCollection({
  loader: glob({ pattern: "**/*.{md,mdx}", base: "./src/content/blog" }),
  schema: z.object({
    title: z.string(),
    description: z.string(),
    pubDate: z.coerce.date(),
    author: z.string(),
    authorRole: z.string().optional(),
    authorImage: z.string().optional(),
    image: z.string().optional(),
    tags: z.array(z.string()).optional(),
    category: z.string().optional(),
    downloadHref: z.string().optional(),
    downloadLabel: z.string().optional(),
  }),
});

export const collections = { blog };
