import { getCollection, type CollectionEntry } from "astro:content";
import { projectArticles as localProjectArticles, projectPages as localProjectPages, projectStages } from "../data/projects";
import { localPeople } from "../data/people";

const wordpressApiUrl = (import.meta.env.WORDPRESS_API_URL ?? process.env.WORDPRESS_API_URL ?? "").replace(/\/$/, "");
const cacheWordPressResults = import.meta.env.PROD;
export const DEFAULT_PROFILE_IMAGE = "/logo_Transparent.png";
export const isDefaultProfileImage = (value?: string) => !value || value === DEFAULT_PROFILE_IMAGE;

export interface PublicationTag {
  slug: string;
  name: string;
  label: string;
}

export interface PersonSummary {
  id: number | string;
  slug: string;
  href: string;
  name: string;
  role: string;
  email?: string;
  linkedinUrl?: string;
  websiteUrl?: string;
  image?: string;
  shortBio: string;
  contentHtml: string;
  showOnTeamPage: boolean;
  source: "wordpress" | "local";
}

export interface PublicationPerson {
  id?: number | string;
  slug?: string;
  name: string;
  role?: string;
  image?: string;
  href?: string;
  linked: boolean;
}

export interface PublicationSummary {
  id: number | string;
  slug: string;
  href: string;
  title: string;
  description: string;
  excerpt: string;
  pubDate: Date;
  author: string;
  authorRole?: string;
  authorImage?: string;
  authorPerson?: PublicationPerson;
  image?: string;
  audioHref?: string;
  outputType: string;
  outputTypeSlug: string;
  topics: string[];
  hashtags: PublicationTag[];
  contributors: PublicationPerson[];
  relatedProjectIds: number[];
  relatedProjectSlugs: string[];
  relatedProjectTitles: string[];
  downloadHref?: string;
  downloadLabel?: string;
  source: "wordpress" | "local";
}

export type PublicationDetail =
  | (PublicationSummary & {
      source: "wordpress";
      contentHtml: string;
      localEntry?: never;
      minutesRead?: string;
    })
  | (PublicationSummary & {
      source: "local";
      contentHtml?: never;
      localEntry: CollectionEntry<"blog">;
      minutesRead?: string;
    });

export interface ProjectSummary {
  id: number | string;
  slug: string;
  href: string;
  title: string;
  description: string;
  color: string;
  currentStage: string;
  lead: {
    name: string;
    role: string;
    image: string;
    href?: string;
    linked: boolean;
  };
  team: Array<{
    name: string;
    href?: string;
    linked: boolean;
  }>;
  donors: Array<{
    name: string;
    logo: string;
  }>;
  updates: string[];
  focusAreas: Array<{
    title: string;
    description: string;
  }>;
  contentHtml: string;
  source: "wordpress" | "local";
}

export interface HomepageSelections {
  featuredPodcast?: PublicationSummary;
  featuredArticle?: PublicationDetail;
  sliderPublications: PublicationSummary[];
  latestPublications: PublicationSummary[];
}

export interface AnnouncementBarSettings {
  text: string;
  linkLabel: string;
  linkHref: string;
}

interface WordPressTerm {
  id: number;
  name: string;
  slug: string;
  taxonomy: string;
}

interface WordPressRecord {
  id: number;
  slug: string;
  date: string;
  menu_order?: number;
  title: { rendered: string };
  excerpt: { rendered: string };
  content: { rendered: string };
  meta?: Record<string, unknown>;
  _embedded?: {
    ["wp:term"]?: WordPressTerm[][];
  };
}

const entityMap: Record<string, string> = {
  amp: "&",
  apos: "'",
  lt: "<",
  gt: ">",
  nbsp: " ",
  quot: '"',
};

const toRouteSlug = (value: string) =>
  value
    .toLowerCase()
    .replace(/&/g, " and ")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");

const decodeHtml = (value: string) =>
  value.replace(/&(#x?[0-9a-f]+|[a-z]+);/gi, (_match, entity: string) => {
    const normalized = entity.toLowerCase();

    if (normalized.startsWith("#x")) {
      return String.fromCodePoint(parseInt(normalized.slice(2), 16));
    }

    if (normalized.startsWith("#")) {
      return String.fromCodePoint(parseInt(normalized.slice(1), 10));
    }

    return entityMap[normalized] ?? _match;
  });

const stripHtml = (value: string) =>
  decodeHtml(value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim());

const textToParagraphHtml = (value: string) =>
  value
    .split(/\n\s*\n/g)
    .map((paragraph) => paragraph.replace(/\s*\n\s*/g, " ").trim())
    .filter(Boolean)
    .map((paragraph) => `<p>${paragraph}</p>`)
    .join("");

const extractParagraphs = (html: string, limit = 3) => {
  const matches = [...html.matchAll(/<p>(.*?)<\/p>/gisu)];
  return matches
    .slice(0, limit)
    .map((match) => stripHtml(match[1] ?? ""))
    .filter(Boolean);
};

const localDateFormatter = (date: Date) =>
  new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  }).format(date);

const normalizeLocalPublicationType = (value?: string) => {
  if (value === "Series") {
    return "Insights";
  }

  if (value === "Podcast") {
    return "Pod-Cast";
  }

  return value ?? "Insights";
};

const formatLocalTagLabel = (value: string) =>
  value
    .split("-")
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");

const defaultAnnouncementBarSettings: AnnouncementBarSettings = {
  text: "Next talk: May 20 - African Talks: South African Foreign Policy",
  linkLabel: "Register",
  linkHref: "/widgets",
};

let wordpressProjectCache: Promise<ProjectSummary[] | null> | undefined;
let wordpressPeopleCache: Promise<PersonSummary[] | null> | undefined;
let wordpressPublicationCache: Promise<PublicationSummary[] | null> | undefined;
let wordpressHomepageRecordCache: Promise<WordPressRecord | null> | undefined;
let wordpressHomepageCache: Promise<HomepageSelections | null> | undefined;
let localPublicationCache: Promise<PublicationSummary[]> | undefined;
let localDetailCache: Promise<Map<string, PublicationDetail>> | undefined;

const isPublicationSummary = (
  value: PublicationSummary | undefined,
): value is PublicationSummary => Boolean(value);

const isPodcastPublication = (publication: PublicationSummary | undefined) =>
  Boolean(publication && publication.outputTypeSlug === "pod-cast");

const localPeopleSummaries: PersonSummary[] = localPeople.map((person) => ({
  id: person.slug,
  slug: person.slug,
  href: `/team/${person.slug}`,
  name: person.name,
  role: person.role,
  email: person.email,
  linkedinUrl: person.linkedinUrl,
  websiteUrl: person.websiteUrl,
  image: person.image || DEFAULT_PROFILE_IMAGE,
  shortBio: person.shortBio,
  contentHtml: person.contentHtml,
  showOnTeamPage: person.showOnTeamPage,
  source: "local",
}));

const localPeopleBySlug = new Map(localPeopleSummaries.map((person) => [person.slug, person]));
const localPeopleByName = new Map(
  localPeopleSummaries.map((person) => [person.name.toLowerCase(), person]),
);

const toPublicationPerson = (person: PersonSummary): PublicationPerson => ({
  id: person.id,
  slug: person.slug,
  name: person.name,
  role: person.role,
  image: person.image,
  href: person.href,
  linked: true,
});

const buildProjectPerson = (
  name: string,
  peopleByName: Map<string, PersonSummary>,
  options?: {
    role?: string;
    image?: string;
  },
) => {
  const normalizedName = name.trim();
  const matchedPerson = peopleByName.get(normalizedName.toLowerCase());

  return {
    name: matchedPerson?.name ?? normalizedName,
    role: options?.role ?? matchedPerson?.role ?? "",
    image: options?.image || matchedPerson?.image || DEFAULT_PROFILE_IMAGE,
    href: matchedPerson?.href,
    linked: Boolean(matchedPerson?.href),
  };
};

const fetchWordPressJson = async <T>(endpoint: string): Promise<T | null> => {
  if (!wordpressApiUrl) {
    return null;
  }

  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 4000);

  try {
    const response = await fetch(`${wordpressApiUrl}${endpoint}`, {
      cache: "no-store",
      signal: controller.signal,
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      return null;
    }

    return (await response.json()) as T;
  } catch {
    return null;
  } finally {
    clearTimeout(timeout);
  }
};

const getTermsByTaxonomy = (record: WordPressRecord, taxonomy: string) =>
  (record._embedded?.["wp:term"] ?? [])
    .flat()
    .filter((term) => term.taxonomy === taxonomy);

const loadWordPressPeople = async (): Promise<PersonSummary[] | null> => {
  if (!cacheWordPressResults) {
    wordpressPeopleCache = undefined;
  }

  if (!wordpressPeopleCache) {
    wordpressPeopleCache = (async () => {
      const records = await fetchWordPressJson<WordPressRecord[]>(
        "/wp/v2/mp_person?per_page=100&status=publish&orderby=menu_order&order=asc",
      );

      if (!records) {
        return null;
      }

      return records
        .map((record) => {
          const shortBioMeta = String(record.meta?.mp_short_bio ?? "").trim();
          const profileBioMeta = String(record.meta?.mp_profile_bio ?? "").trim();

          return {
            id: record.id,
            slug: record.slug,
            href: `/team/${record.slug}`,
            name: decodeHtml(record.title.rendered),
            role: String(record.meta?.mp_role ?? ""),
            email: String(record.meta?.mp_email ?? ""),
            linkedinUrl: String(record.meta?.mp_linkedin_url ?? ""),
            websiteUrl: String(record.meta?.mp_website_url ?? ""),
            image: String(record.meta?.mp_photo ?? "") || DEFAULT_PROFILE_IMAGE,
            shortBio: shortBioMeta || stripHtml(record.excerpt.rendered),
            contentHtml:
              textToParagraphHtml(profileBioMeta) ||
              record.content.rendered ||
              `<p>${shortBioMeta || stripHtml(record.excerpt.rendered)}</p>`,
            showOnTeamPage: Boolean(record.meta?.mp_show_on_team_page),
            source: "wordpress" as const,
          };
        })
        .sort((a, b) => {
          const orderDelta = (records.find((record) => record.slug === a.slug)?.menu_order ?? 0)
            - (records.find((record) => record.slug === b.slug)?.menu_order ?? 0);
          if (orderDelta !== 0) {
            return orderDelta;
          }

          return a.name.localeCompare(b.name);
        });
    })();
  }

  return wordpressPeopleCache;
};

const loadWordPressProjects = async (): Promise<ProjectSummary[] | null> => {
  if (!cacheWordPressResults) {
    wordpressProjectCache = undefined;
  }

  if (!wordpressProjectCache) {
    wordpressProjectCache = (async () => {
      const [records, people] = await Promise.all([
        fetchWordPressJson<WordPressRecord[]>("/wp/v2/mp_project?per_page=100&status=publish"),
        loadWordPressPeople(),
      ]);

      if (!records) {
        return null;
      }

      const peopleByName = new Map(
        (people ?? localPeopleSummaries).map((person) => [person.name.toLowerCase(), person]),
      );

      return records
        .map((record) => {
          const leadName = String(record.meta?.mp_lead_name ?? "").trim();
          const leadRole = String(record.meta?.mp_lead_role ?? "");
          const leadImage = String(record.meta?.mp_lead_image ?? "");

          return {
            id: record.id,
            slug: record.slug,
            href: `/services/${record.slug}`,
            title: decodeHtml(record.title.rendered),
            description: stripHtml(record.excerpt.rendered),
            color: String(record.meta?.mp_color ?? "#15243a"),
            currentStage: String(record.meta?.mp_current_stage ?? projectStages[0]),
            lead: buildProjectPerson(leadName, peopleByName, {
              role: leadRole,
              image: leadImage,
            }),
            team: Array.isArray(record.meta?.mp_team_members)
              ? record.meta?.mp_team_members.map((item) =>
                  buildProjectPerson(String(item), peopleByName),
                )
              : [],
            donors: Array.isArray(record.meta?.mp_donors)
              ? record.meta?.mp_donors.map((item) => ({
                  name: String((item as { name?: string }).name ?? ""),
                  logo: String((item as { logo?: string }).logo ?? ""),
                }))
              : [],
            updates: Array.isArray(record.meta?.mp_updates)
              ? record.meta?.mp_updates.map((item) => String(item))
              : [],
            focusAreas: Array.isArray(record.meta?.mp_focus_areas)
              ? record.meta?.mp_focus_areas.map((item) => ({
                  title: String((item as { title?: string }).title ?? ""),
                  description: String((item as { description?: string }).description ?? ""),
                }))
              : [],
            contentHtml: record.content.rendered,
            source: "wordpress" as const,
          };
        })
        .sort((a, b) => a.title.localeCompare(b.title));
    })();
  }

  return wordpressProjectCache;
};

const buildWordPressPublicationSummary = (
  record: WordPressRecord,
  projectLookup: Map<number, ProjectSummary>,
  peopleLookup: Map<number, PersonSummary>,
): PublicationSummary => {
  const topicTerms = getTermsByTaxonomy(record, "mp_topic");
  const hashtagTerms = getTermsByTaxonomy(record, "mp_hashtag");
  const outputTypeTerm = getTermsByTaxonomy(record, "mp_output_type")[0];
  const relatedProjectIds = Array.isArray(record.meta?.mp_related_project_ids)
    ? record.meta?.mp_related_project_ids.map((item) => Number(item))
    : [];
  const authorPersonId = Number(record.meta?.mp_author_person_id ?? 0);
  const authorPersonRecord = authorPersonId ? peopleLookup.get(authorPersonId) : undefined;
  const contributorPersonIds = Array.isArray(record.meta?.mp_contributor_person_ids)
    ? record.meta?.mp_contributor_person_ids.map((item) => Number(item))
    : [];
  const contributorNames = Array.isArray(record.meta?.mp_contributor_names)
    ? record.meta?.mp_contributor_names.map((item) => String(item))
    : [];
  const relatedProjects = relatedProjectIds
    .map((id) => projectLookup.get(id))
    .filter(Boolean) as ProjectSummary[];
  const contributors = [
    ...contributorPersonIds
      .filter((id) => id !== authorPersonId)
      .map((id) => peopleLookup.get(id))
      .filter(Boolean)
      .map((person) => toPublicationPerson(person as PersonSummary)),
    ...contributorNames
      .filter(Boolean)
      .map((name) => ({
        name,
        linked: false as const,
        image: "",
      })),
  ];

  return {
    id: record.id,
    slug: record.slug,
    href: `/blog/${record.slug}`,
    title: decodeHtml(record.title.rendered),
    description: stripHtml(record.excerpt.rendered),
    excerpt: stripHtml(record.excerpt.rendered),
    pubDate: new Date(record.date),
    author: String(record.meta?.mp_author_name ?? authorPersonRecord?.name ?? "Mediterranean Platform"),
    authorRole: String(record.meta?.mp_author_role ?? authorPersonRecord?.role ?? ""),
    authorImage: String(record.meta?.mp_author_image ?? authorPersonRecord?.image ?? "") || DEFAULT_PROFILE_IMAGE,
    authorPerson: authorPersonRecord ? toPublicationPerson(authorPersonRecord) : undefined,
    image: String(record.meta?.mp_cover_image ?? ""),
    audioHref: String(record.meta?.mp_audio_url ?? ""),
    outputType: outputTypeTerm?.name ?? "Insights",
    outputTypeSlug: outputTypeTerm?.slug ?? "insights",
    topics: topicTerms.map((term) => term.name),
    hashtags: hashtagTerms.map((term) => ({
      slug: term.slug,
      name: term.name,
      label: term.name,
    })),
    contributors,
    relatedProjectIds,
    relatedProjectSlugs: relatedProjects.map((project) => project.slug),
    relatedProjectTitles: relatedProjects.map((project) => project.title),
    downloadHref: String(record.meta?.mp_download_url ?? ""),
    downloadLabel: String(record.meta?.mp_download_label ?? ""),
    source: "wordpress",
  };
};

const loadWordPressPublications = async (): Promise<PublicationSummary[] | null> => {
  if (!cacheWordPressResults) {
    wordpressPublicationCache = undefined;
  }

  if (!wordpressPublicationCache) {
    wordpressPublicationCache = (async () => {
      const [records, projects, people] = await Promise.all([
        fetchWordPressJson<WordPressRecord[]>(
          "/wp/v2/mp_publication?per_page=100&status=publish&_embed=1",
        ),
        loadWordPressProjects(),
        loadWordPressPeople(),
      ]);

      if (!records || !projects || !people) {
        return null;
      }

      const projectLookup = new Map(projects.map((project) => [Number(project.id), project]));
      const peopleLookup = new Map(people.map((person) => [Number(person.id), person]));

      return records
        .map((record) => buildWordPressPublicationSummary(record, projectLookup, peopleLookup))
        .sort((a, b) => b.pubDate.valueOf() - a.pubDate.valueOf());
    })();
  }

  return wordpressPublicationCache;
};

const loadWordPressHomepageRecord = async (): Promise<WordPressRecord | null> => {
  if (!cacheWordPressResults) {
    wordpressHomepageRecordCache = undefined;
  }

  if (!wordpressHomepageRecordCache) {
    wordpressHomepageRecordCache = (async () => {
      const homepageRecords = await fetchWordPressJson<WordPressRecord[]>(
        "/wp/v2/mp_homepage?slug=homepage-settings&status=publish",
      );

      if (!homepageRecords?.length) {
        return null;
      }

      return homepageRecords[0];
    })();
  }

  return wordpressHomepageRecordCache;
};

const loadWordPressHomepageSelections = async (): Promise<HomepageSelections | null> => {
  if (!cacheWordPressResults) {
    wordpressHomepageCache = undefined;
  }

  if (!wordpressHomepageCache) {
    wordpressHomepageCache = (async () => {
      const [homepage, publications] = await Promise.all([
        loadWordPressHomepageRecord(),
        loadWordPressPublications(),
      ]);

      if (!homepage || !publications) {
        return null;
      }
      const publicationById = new Map(
        publications.map((publication) => [Number(publication.id), publication]),
      );

      const sliderPublications = Array.isArray(homepage.meta?.mp_slider_publication_ids)
        ? homepage.meta?.mp_slider_publication_ids
            .map((id) => publicationById.get(Number(id)))
            .filter(isPublicationSummary)
        : publications.slice(0, 4);

      const latestPublications = Array.isArray(homepage.meta?.mp_latest_publication_ids)
        ? homepage.meta?.mp_latest_publication_ids
            .map((id) => publicationById.get(Number(id)))
            .filter(isPublicationSummary)
        : publications.slice(0, 4);

      const featuredPodcastCandidate = publicationById.get(
        Number(homepage.meta?.mp_featured_podcast_id),
      );
      const featuredPodcast =
        featuredPodcastCandidate && isPodcastPublication(featuredPodcastCandidate)
          ? featuredPodcastCandidate
          : undefined;
      const featuredArticleSummary = publicationById.get(
        Number(homepage.meta?.mp_featured_article_id),
      );
      const featuredArticle = featuredArticleSummary
        ? await getPublicationBySlug(featuredArticleSummary.slug)
        : undefined;

      return {
        featuredPodcast,
        featuredArticle,
        sliderPublications: sliderPublications.length > 0 ? sliderPublications : publications.slice(0, 4),
        latestPublications,
      };
    })();
  }

  return wordpressHomepageCache ?? null;
};

const loadLocalPublications = async (): Promise<PublicationSummary[]> => {
  if (!localPublicationCache) {
    localPublicationCache = (async () => {
      const entries = await getCollection("blog");

      return entries
        .map((entry) => {
          const outputType = normalizeLocalPublicationType(entry.data.category);
          const hashtags = (entry.data.tags ?? []).map((tag) => ({
            slug: tag,
            name: tag,
            label: formatLocalTagLabel(tag),
          }));
          const authorPerson =
            (entry.data.authorPersonSlug && localPeopleBySlug.get(entry.data.authorPersonSlug)) ||
            localPeopleByName.get(entry.data.author.toLowerCase());
          const contributors = [
            ...((entry.data.contributorPersonSlugs ?? [])
              .map((slug) => localPeopleBySlug.get(slug))
              .filter(Boolean)
              .map((person) => toPublicationPerson(person as PersonSummary))),
            ...((entry.data.contributorNames ?? []).map((name) => ({
              name,
              linked: false as const,
              image: "",
            }))),
          ];

          return {
            id: entry.id,
            slug: entry.id,
            href: `/blog/${entry.id}`,
            title: entry.data.title,
            description: entry.data.description,
            excerpt: entry.data.description,
            pubDate: entry.data.pubDate,
            author: entry.data.author,
            authorRole: entry.data.authorRole ?? authorPerson?.role,
            authorImage: entry.data.authorImage ?? authorPerson?.image ?? DEFAULT_PROFILE_IMAGE,
            authorPerson: authorPerson ? toPublicationPerson(authorPerson) : undefined,
            image: entry.data.image,
            audioHref: outputType === "Pod-Cast" ? entry.data.downloadHref : undefined,
            outputType,
            outputTypeSlug: toRouteSlug(outputType),
            topics:
              entry.data.tags && entry.data.tags.length > 0
                ? [formatLocalTagLabel(entry.data.tags[0])]
                : ["General"],
            hashtags,
            contributors,
            relatedProjectIds: [],
            relatedProjectSlugs: [],
            relatedProjectTitles: [],
            downloadHref: entry.data.downloadHref,
            downloadLabel: entry.data.downloadLabel,
            source: "local" as const,
          };
        })
        .sort((a, b) => b.pubDate.valueOf() - a.pubDate.valueOf());
    })();
  }

  return localPublicationCache;
};

const loadLocalPublicationDetails = async (): Promise<Map<string, PublicationDetail>> => {
  if (!localDetailCache) {
    localDetailCache = (async () => {
      const entries = await getCollection("blog");
      const summaries = await loadLocalPublications();
      const summaryBySlug = new Map(summaries.map((summary) => [summary.slug, summary]));

      return new Map(
        entries.map((entry) => [
          entry.id,
          {
            ...summaryBySlug.get(entry.id)!,
            source: "local" as const,
            localEntry: entry,
          },
        ]),
      );
    })();
  }

  return localDetailCache;
};

export const getProjects = async (): Promise<ProjectSummary[]> => {
  const wordpressProjects = await loadWordPressProjects();
  if (wordpressProjects) {
    return wordpressProjects;
  }

  const peopleByName = new Map(localPeopleSummaries.map((person) => [person.name.toLowerCase(), person]));

  return localProjectPages.map((project) => ({
    id: project.slug,
    slug: project.slug,
    href: project.href,
    title: project.title,
    description: project.description,
    color: project.color,
    currentStage: project.currentStage,
    lead: buildProjectPerson(project.lead.name, peopleByName, {
      role: project.lead.role,
      image: project.lead.image,
    }),
    team: project.team.map((member) => buildProjectPerson(member, peopleByName)),
    donors: project.donors,
    updates: project.updates,
    focusAreas: project.focusAreas,
    contentHtml: project.overview.map((paragraph) => `<p>${paragraph}</p>`).join(""),
    source: "local",
  }));
};

export const getPeople = async (): Promise<PersonSummary[]> => {
  const wordpressPeople = await loadWordPressPeople();
  if (wordpressPeople) {
    return wordpressPeople;
  }

  return localPeopleSummaries;
};

export const getTeamPeople = async (): Promise<PersonSummary[]> => {
  const people = await getPeople();
  return people.filter((person) => person.showOnTeamPage);
};

export const getPersonBySlug = async (slug: string) => {
  const people = await getPeople();
  return people.find((person) => person.slug === slug);
};

export const getProjectBySlug = async (slug: string) => {
  const projects = await getProjects();
  return projects.find((project) => project.slug === slug);
};

export const getPublications = async (): Promise<PublicationSummary[]> => {
  const wordpressPublications = await loadWordPressPublications();
  if (wordpressPublications) {
    return wordpressPublications;
  }

  return loadLocalPublications();
};

export const getPublicationBySlug = async (slug: string): Promise<PublicationDetail | undefined> => {
  if (wordpressApiUrl) {
    const [record, projects, people] = await Promise.all([
      fetchWordPressJson<WordPressRecord[]>(
        `/wp/v2/mp_publication?slug=${encodeURIComponent(slug)}&status=publish&_embed=1`,
      ),
      loadWordPressProjects(),
      loadWordPressPeople(),
    ]);

    if (record?.length && projects && people) {
      const projectLookup = new Map(projects.map((project) => [Number(project.id), project]));
      const peopleLookup = new Map(people.map((person) => [Number(person.id), person]));
      const summary = buildWordPressPublicationSummary(record[0], projectLookup, peopleLookup);
      return {
        ...summary,
        source: "wordpress",
        contentHtml: record[0].content.rendered,
      };
    }
  }

  const details = await loadLocalPublicationDetails();
  return details.get(slug);
};

export const getPublicationsByOutputTypeSlug = async (slug: string) => {
  const publications = await getPublications();
  return publications.filter((publication) => publication.outputTypeSlug === slug);
};

export const getPublicationsByHashtagSlug = async (slug: string) => {
  const publications = await getPublications();
  return publications.filter((publication) =>
    publication.hashtags.some((tag) => tag.slug === slug),
  );
};

export const getOutputTypePaths = async () => {
  const publications = await getPublications();
  const seen = new Map<string, string>();

  for (const publication of publications) {
    if (!seen.has(publication.outputTypeSlug)) {
      seen.set(publication.outputTypeSlug, publication.outputType);
    }
  }

  return [...seen.entries()].map(([slug, label]) => ({ slug, label }));
};

export const getHashtagPaths = async () => {
  const publications = await getPublications();
  const seen = new Map<string, string>();

  for (const publication of publications) {
    for (const tag of publication.hashtags) {
      if (!seen.has(tag.slug)) {
        seen.set(tag.slug, tag.label);
      }
    }
  }

  return [...seen.entries()].map(([slug, label]) => ({ slug, label }));
};

export const getPublicationsForProject = async (projectSlug: string) => {
  const publications = await getPublications();

  const filtered = publications.filter((publication) =>
    publication.relatedProjectSlugs.includes(projectSlug),
  );

  if (filtered.length > 0) {
    return filtered;
  }

  const localProject = localProjectPages.find((project) => project.slug === projectSlug);
  if (!localProject) {
    return [];
  }

  return localProjectArticles
    .filter((article) => article.platform === localProject.title)
    .map((article, index) => ({
      id: `${projectSlug}-${index}`,
      slug: toRouteSlug(article.title),
      href: "/blog",
      title: article.title,
      description: article.summary,
      excerpt: article.summary,
      pubDate: new Date(),
      author: article.author,
      contributors: [],
      image: article.image,
      outputType: "Publication",
      outputTypeSlug: "publication",
      topics: [article.topic],
      hashtags: article.hashtags.map((tag) => ({
        slug: toRouteSlug(tag),
        name: tag,
        label: tag,
      })),
      relatedProjectIds: [],
      relatedProjectSlugs: [projectSlug],
      relatedProjectTitles: [localProject.title],
      source: "local" as const,
    }));
};

export const getPublicationsForPerson = async (personSlug: string) => {
  const publications = await getPublications();

  return publications.filter(
    (publication) =>
      publication.authorPerson?.slug === personSlug ||
      publication.contributors.some((contributor) => contributor.slug === personSlug),
  );
};

export const getHomepageSelections = async (): Promise<HomepageSelections> => {
  const wordpressSelections = await loadWordPressHomepageSelections();
  if (wordpressSelections) {
    return wordpressSelections;
  }

  const publications = await getPublications();
  const featuredPodcast =
    publications.find((publication) => isPodcastPublication(publication)) ?? publications[0];
  const featuredArticle =
    (await getPublicationBySlug("markdown-features")) ??
    (publications[1] ? await getPublicationBySlug(publications[1].slug) : undefined);

  return {
    featuredPodcast,
    featuredArticle,
    sliderPublications: publications.slice(0, 4),
    latestPublications: publications.slice(0, 4),
  };
};

export const getAnnouncementBarSettings = async (): Promise<AnnouncementBarSettings | undefined> => {
  const homepage = await loadWordPressHomepageRecord();

  if (homepage) {
    const text = String(homepage.meta?.mp_announcement_text ?? "").trim();
    const linkLabel = String(homepage.meta?.mp_announcement_link_label ?? "").trim();
    const linkHref = String(homepage.meta?.mp_announcement_link_url ?? "").trim();

    if (!text || !linkLabel || !linkHref) {
      return undefined;
    }

    return {
      text,
      linkLabel,
      linkHref,
    };
  }

  return defaultAnnouncementBarSettings;
};

export const getFeaturedArticleParagraphs = (publication?: PublicationDetail) => {
  if (!publication) {
    return [];
  }

  if (publication.source === "wordpress") {
    return extractParagraphs(publication.contentHtml, 3);
  }

  return [publication.description];
};

export const getPublicationCardDate = (publication: PublicationSummary) =>
  localDateFormatter(publication.pubDate);

export { projectStages };
