import fs from "node:fs/promises";
import path from "node:path";
import matter from "gray-matter";
import { marked } from "marked";

const rootDir = process.cwd();
const blogDir = path.join(rootDir, "src", "content", "blog");
const extraPublicationsPath = path.join(rootDir, "wordpress", "seed", "extra-publications.json");
const outputPath = path.join(rootDir, "wordpress", "seed", "generated-publications.json");

const projectSlugByPublicationSlug = {
  "red-sea-corridors-audio-brief": ["gulf-platform"],
};

const toTopicLabel = (value) =>
  value
    .split("-")
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");

const normalizeOutputType = (value) => {
  if (value === "Series") {
    return "Insights";
  }

  if (value === "Podcast") {
    return "Pod-Cast";
  }

  return value || "Insights";
};

const markdownFiles = (await fs.readdir(blogDir))
  .filter((name) => name.endsWith(".md") || name.endsWith(".mdx"))
  .sort();

const parsedPublications = [];

for (const fileName of markdownFiles) {
  const filePath = path.join(blogDir, fileName);
  const raw = await fs.readFile(filePath, "utf8");
  const { data, content } = matter(raw);
  const slug = fileName.replace(/\.(md|mdx)$/u, "");
  const tags = Array.isArray(data.tags) ? data.tags.map(String) : [];

  parsedPublications.push({
    slug,
    title: String(data.title),
    excerpt: String(data.description ?? ""),
    date: String(data.pubDate),
    authorName: String(data.author ?? "Mediterranean Platform"),
    authorPersonSlug: data.authorPersonSlug ? String(data.authorPersonSlug) : "",
    authorRole: data.authorRole ? String(data.authorRole) : "",
    category: normalizeOutputType(String(data.category ?? "")),
    topics: tags.length > 0 ? [toTopicLabel(tags[0])] : ["General"],
    hashtags: tags,
    projectSlugs: projectSlugByPublicationSlug[slug] ?? [],
    imageUrl: data.image ? String(data.image) : "",
    downloadUrl: data.downloadHref ? String(data.downloadHref) : "",
    downloadLabel: data.downloadLabel ? String(data.downloadLabel) : "",
    contributorPersonSlugs: Array.isArray(data.contributorPersonSlugs)
      ? data.contributorPersonSlugs.map(String)
      : [],
    contributorNames: Array.isArray(data.contributorNames) ? data.contributorNames.map(String) : [],
    contentHtml: await marked.parse(content),
  });
}

const extraPublications = JSON.parse(await fs.readFile(extraPublicationsPath, "utf8"));
const publicationMap = new Map();

for (const publication of [...parsedPublications, ...extraPublications]) {
  publicationMap.set(publication.slug, publication);
}

await fs.writeFile(
  outputPath,
  JSON.stringify([...publicationMap.values()], null, 2) + "\n",
  "utf8",
);

console.log(`Generated ${publicationMap.size} publication seed records at ${path.relative(rootDir, outputPath)}.`);
