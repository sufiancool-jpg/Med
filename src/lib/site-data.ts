import { getCollection, type CollectionEntry } from "astro:content";
import { projectArticles as localProjectArticles, projectPages as localProjectPages, projectStages } from "../data/projects";
import { localPeople } from "../data/people";
import { siteConfig } from "../config/site";

const wordpressApiUrl = (import.meta.env.WORDPRESS_API_URL ?? process.env.WORDPRESS_API_URL ?? "").replace(/\/$/, "");
const cacheWordPressResults = import.meta.env.PROD;
export const DEFAULT_PROFILE_IMAGE = "/logo_Transparent.png";
export const isDefaultProfileImage = (value?: string) => !value || value === DEFAULT_PROFILE_IMAGE;

export interface PublicationTag {
  slug: string;
  name: string;
  label: string;
}

export interface PublicationReference {
  name: string;
  url: string;
}

export interface ProjectLink {
  id: number | string;
  slug: string;
  href: string;
  title: string;
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
  previewText: string;
  pubDate: Date;
  author: string;
  authorRole?: string;
  authorImage?: string;
  authorPerson?: PublicationPerson;
  authorPeople: PublicationPerson[];
  image?: string;
  audioHref?: string;
  outputType: string;
  outputTypeSlug: string;
  topics: string[];
  hashtags: PublicationTag[];
  contributors: PublicationPerson[];
  references: PublicationReference[];
  relatedProjectIds: number[];
  relatedProjectSlugs: string[];
  relatedProjectTitles: string[];
  relatedProjects: Array<{
    slug: string;
    title: string;
    href: string;
    color: string;
  }>;
  downloadTrackedHref?: string;
  downloadCountApiHref?: string;
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
  cardIcon?: string;
  parentProject?: ProjectLink;
  alignedProject?: ProjectLink;
  color: string;
  progressColor: string;
  currentStage: string;
  stagePoints: string[];
  hideProjectBar: boolean;
  leads: Array<{
    name: string;
    role: string;
    image: string;
    href?: string;
    linked: boolean;
  }>;
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
  projectCount: number;
  projectIds: number[];
}

export interface AnnouncementBarSettings {
  text: string;
  linkLabel: string;
  linkHref: string;
}

export interface SiteSettings {
  socialLinks: {
    linkedin?: string;
    youtube?: string;
    instagram?: string;
    contact?: string;
  };
  showPublicDownloadCounts: boolean;
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

const PUBLICATION_PREVIEW_WORD_LIMIT = 12;
const PROJECT_DESCRIPTION_WORD_LIMIT = 25;

const normalizeComparableText = (value: string) =>
  stripHtml(value)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, " ")
    .trim();

const takeWords = (value: string, limit = PUBLICATION_PREVIEW_WORD_LIMIT) =>
  stripHtml(value).split(/\s+/).filter(Boolean).slice(0, limit).join(" ");

const getWordCount = (value: string) => stripHtml(value).split(/\s+/).filter(Boolean).length;

const clampPreviewText = (value: string, limit = PUBLICATION_PREVIEW_WORD_LIMIT) => {
  const words = stripHtml(value).split(/\s+/).filter(Boolean);

  if (words.length <= limit) {
    return words.join(" ");
  }

  return `${words.slice(0, limit).join(" ")}...`;
};

const clampWordCount = (value: string, limit: number) => {
  const words = stripHtml(value).split(/\s+/).filter(Boolean);

  if (words.length <= limit) {
    return words.join(" ");
  }

  return words.slice(0, limit).join(" ");
};

const clampProjectDescription = (value: string) =>
  clampWordCount(value, PROJECT_DESCRIPTION_WORD_LIMIT);

const buildPublicationPreviewText = (...candidates: Array<string | undefined>) => {
  const preferredSource =
    candidates.find((value) => value && getWordCount(value) >= PUBLICATION_PREVIEW_WORD_LIMIT) ??
    candidates.find((value) => value && stripHtml(value).length > 0) ??
    "";

  return clampPreviewText(preferredSource);
};

const isPreviewDuplicatedInContent = (preview: string, contentHtml: string) => {
  const previewLead = takeWords(preview);
  const contentLead = takeWords(contentHtml);

  return Boolean(previewLead && contentLead && normalizeComparableText(previewLead) === normalizeComparableText(contentLead));
};

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

const defaultSiteSettings: SiteSettings = {
  socialLinks: {
    linkedin: siteConfig.socialLinks.linkedin,
    youtube: siteConfig.socialLinks.youtube,
    instagram: siteConfig.socialLinks.instagram,
    contact: siteConfig.socialLinks.contact,
  },
  showPublicDownloadCounts: false,
};

let wordpressProjectCache: Promise<ProjectSummary[] | null> | undefined;
let wordpressPeopleCache: Promise<PersonSummary[] | null> | undefined;
let wordpressPublicationCache: Promise<PublicationSummary[] | null> | undefined;
let wordpressHomepageRecordCache: Promise<WordPressRecord | null> | undefined;
let wordpressHomepageCache: Promise<HomepageSelections | null> | undefined;
let wordpressSiteSettingsCache: Promise<SiteSettings | null> | undefined;
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
const localVisibleProjectPages = localProjectPages.filter((project) => !project.hideFromProjectScreens);
const localVisibleProjectsBySlug = new Map(localVisibleProjectPages.map((project) => [project.slug, project]));

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

const getWordPressSiteBaseUrl = () => {
  if (!wordpressApiUrl) {
    return undefined;
  }

  try {
    const url = new URL(wordpressApiUrl);
    url.pathname = url.pathname.replace(/\/wp-json\/?$/, "/");
    url.search = "";
    url.hash = "";
    return url.toString();
  } catch {
    return undefined;
  }
};

const buildWordPressAdminActionUrl = (params: Record<string, string | number>) => {
  const baseUrl = getWordPressSiteBaseUrl();

  if (!baseUrl) {
    return undefined;
  }

  try {
    const url = new URL("wp-admin/admin-post.php", baseUrl);

    Object.entries(params).forEach(([key, value]) => {
      url.searchParams.set(key, String(value));
    });

    return url.toString();
  } catch {
    return undefined;
  }
};

const buildWordPressApiEndpoint = (endpoint: string) => (wordpressApiUrl ? `${wordpressApiUrl}${endpoint}` : undefined);

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
      const peopleById = new Map(
        (people ?? localPeopleSummaries).map((person) => [String(person.id), person]),
      );

      const projectRows = records
        .map((record) => {
          const leadPersonIds = Array.isArray(record.meta?.mp_lead_person_ids)
            ? record.meta?.mp_lead_person_ids.map((item) => Number(item)).filter((item) => item > 0)
            : [];
          const leadPersonId = Number(record.meta?.mp_lead_person_id ?? 0);
          const resolvedLeadPersonIds =
            leadPersonIds.length > 0
              ? leadPersonIds
              : leadPersonId > 0
                ? [leadPersonId]
                : [];
          const leadPersonRecords = resolvedLeadPersonIds
            .map((id) => peopleById.get(String(id)))
            .filter(Boolean) as PersonSummary[];
          const primaryLeadPersonRecord = leadPersonRecords[0];
          const leadName = String(record.meta?.mp_lead_name ?? primaryLeadPersonRecord?.name ?? "").trim();
          const leadRole = String(record.meta?.mp_lead_role ?? primaryLeadPersonRecord?.role ?? "");
          const leadImage = String(record.meta?.mp_lead_image ?? primaryLeadPersonRecord?.image ?? "");
          const leads = leadPersonRecords.map((person) =>
            buildProjectPerson(person.name, peopleByName, {
              role: leadRole || person.role,
              image: person.image,
            }),
          );
          const fallbackLead = buildProjectPerson(leadName, peopleByName, {
            role: leadRole,
            image: leadImage,
          });
          const stagePoints = Array.isArray(record.meta?.mp_stage_points)
            ? record.meta?.mp_stage_points.map((item) => String(item)).filter(Boolean)
            : [];
          const teamMemberIds = Array.isArray(record.meta?.mp_team_member_ids)
            ? record.meta?.mp_team_member_ids.map((item) => Number(item)).filter((item) => item > 0)
            : [];
          const legacyTeamMembers = Array.isArray(record.meta?.mp_team_members)
            ? record.meta?.mp_team_members.map((item) => String(item))
            : [];
          const teamMembers =
            teamMemberIds.length > 0
              ? teamMemberIds
                  .map((id) => peopleById.get(String(id)))
                  .filter(Boolean)
                  .map((person) => buildProjectPerson((person as PersonSummary).name, peopleByName))
              : legacyTeamMembers.map((item) => buildProjectPerson(item, peopleByName));

          return {
            parentProjectId: Number(record.meta?.mp_parent_project_id ?? 0),
            alignedProjectId: Number(record.meta?.mp_aligned_project_id ?? 0),
            hiddenFromProjectScreens: Boolean(record.meta?.mp_hide_project_currently),
            id: record.id,
            slug: record.slug,
            href: `/services/${record.slug}`,
            title: decodeHtml(record.title.rendered),
            description: clampProjectDescription(record.excerpt.rendered),
            cardIcon: String(record.meta?.mp_card_icon ?? ""),
            color: String(record.meta?.mp_color ?? "#15243a"),
            progressColor: String(record.meta?.mp_progress_color ?? record.meta?.mp_color ?? "#15243a"),
            currentStage: String(record.meta?.mp_current_stage ?? projectStages[0]),
            stagePoints: stagePoints.length > 0 ? stagePoints : projectStages,
            hideProjectBar: Boolean(record.meta?.mp_hide_project_bar),
            leads: leads.length > 0 ? leads : fallbackLead.name ? [fallbackLead] : [],
            lead: fallbackLead,
            team: teamMembers,
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
        });

      const visibleProjectRows = projectRows.filter((project) => !project.hiddenFromProjectScreens);
      const projectLookup = new Map(visibleProjectRows.map((project) => [Number(project.id), project]));

      return visibleProjectRows
        .map(({ parentProjectId, alignedProjectId, hiddenFromProjectScreens: _hidden, ...project }) => ({
          ...project,
          parentProject:
            parentProjectId > 0 && projectLookup.has(parentProjectId)
              ? {
                  id: projectLookup.get(parentProjectId)!.id,
                  slug: projectLookup.get(parentProjectId)!.slug,
                  href: projectLookup.get(parentProjectId)!.href,
                  title: projectLookup.get(parentProjectId)!.title,
                }
              : undefined,
          alignedProject:
            alignedProjectId > 0 && projectLookup.has(alignedProjectId)
              ? {
                  id: projectLookup.get(alignedProjectId)!.id,
                  slug: projectLookup.get(alignedProjectId)!.slug,
                  href: projectLookup.get(alignedProjectId)!.href,
                  title: projectLookup.get(alignedProjectId)!.title,
                }
              : undefined,
        }))
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
  const authorPersonIds = Array.isArray(record.meta?.mp_author_person_ids)
    ? record.meta?.mp_author_person_ids.map((item) => Number(item)).filter((item) => item > 0)
    : [];
  const authorPersonId = Number(record.meta?.mp_author_person_id ?? 0);
  const resolvedAuthorPersonIds =
    authorPersonIds.length > 0
      ? authorPersonIds
      : authorPersonId > 0
        ? [authorPersonId]
        : [];
  const authorPersonRecords = resolvedAuthorPersonIds
    .map((id) => peopleLookup.get(id))
    .filter(Boolean) as PersonSummary[];
  const primaryAuthorPersonRecord = authorPersonRecords[0];
  const authorPeople = authorPersonRecords.map((person) => toPublicationPerson(person));
  const contributorPersonIds = Array.isArray(record.meta?.mp_contributor_person_ids)
    ? record.meta?.mp_contributor_person_ids.map((item) => Number(item))
    : [];
  const contributorNames = Array.isArray(record.meta?.mp_contributor_names)
    ? record.meta?.mp_contributor_names.map((item) => String(item))
    : [];
  const relatedProjects = relatedProjectIds
    .map((id) => projectLookup.get(id))
    .filter(Boolean) as ProjectSummary[];
  const references = Array.isArray(record.meta?.mp_references)
    ? record.meta?.mp_references
        .map((item) => ({
          name: String((item as { name?: string }).name ?? "").trim(),
          url: String((item as { url?: string }).url ?? "").trim(),
        }))
        .filter((item) => item.name || item.url)
    : [];
  const isPodcastOutput = outputTypeTerm?.slug === "pod-cast";
  const contributors = [
    ...contributorPersonIds
      .filter((id) => !resolvedAuthorPersonIds.includes(id))
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
    previewText: buildPublicationPreviewText(
      record.excerpt.rendered,
      record.content.rendered,
      record.title.rendered,
    ),
    pubDate: new Date(record.date),
    author:
      authorPeople.length > 0
        ? authorPeople.map((person) => person.name).join(", ")
        : String(record.meta?.mp_author_name ?? "Mediterranean Platform"),
    authorRole: String(record.meta?.mp_author_role ?? primaryAuthorPersonRecord?.role ?? ""),
    authorImage: String(primaryAuthorPersonRecord?.image ?? record.meta?.mp_author_image ?? "") || DEFAULT_PROFILE_IMAGE,
    authorPerson: primaryAuthorPersonRecord ? toPublicationPerson(primaryAuthorPersonRecord) : undefined,
    authorPeople,
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
    references,
    relatedProjectIds,
    relatedProjectSlugs: relatedProjects.map((project) => project.slug),
    relatedProjectTitles: relatedProjects.map((project) => project.title),
    relatedProjects: relatedProjects.map((project) => ({
      slug: project.slug,
      title: project.title,
      href: project.href,
      color: project.color,
    })),
    downloadTrackedHref: isPodcastOutput
      ? undefined
      : buildWordPressAdminActionUrl({
          action: "mp_headless_track_publication_download",
          publication_id: record.id,
        }),
    downloadCountApiHref: isPodcastOutput
      ? undefined
      : buildWordPressApiEndpoint(`/mp-headless/v1/publications/${record.id}/download-stats`),
    downloadHref: isPodcastOutput ? "" : String(record.meta?.mp_download_url ?? ""),
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

const loadWordPressSiteSettings = async (): Promise<SiteSettings | null> => {
  if (!cacheWordPressResults) {
    wordpressSiteSettingsCache = undefined;
  }

  if (!wordpressSiteSettingsCache) {
    wordpressSiteSettingsCache = (async () => {
      const settings = await fetchWordPressJson<{
        socialLinks?: {
          linkedin?: string;
          youtube?: string;
          instagram?: string;
        };
        showPublicDownloadCounts?: boolean;
      }>("/mp-headless/v1/site-settings");

      if (!settings) {
        return null;
      }

      return {
        socialLinks: {
          linkedin: String(settings.socialLinks?.linkedin ?? "").trim(),
          youtube: String(settings.socialLinks?.youtube ?? "").trim(),
          instagram: String(settings.socialLinks?.instagram ?? "").trim(),
          contact: siteConfig.socialLinks.contact,
        },
        showPublicDownloadCounts: Boolean(settings.showPublicDownloadCounts),
      };
    })();
  }

  return wordpressSiteSettingsCache;
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
            .slice(0, 5)
        : publications.slice(0, 5);

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
        projectCount: Math.max(0, Number(homepage.meta?.mp_homepage_project_count ?? 0)),
        projectIds: Array.isArray(homepage.meta?.mp_homepage_project_ids)
          ? homepage.meta?.mp_homepage_project_ids
              .map((id) => Number(id))
              .filter((id) => Number.isFinite(id) && id > 0)
          : [],
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
          const authorPersonSlugs = Array.isArray(entry.data.authorPersonSlugs)
            ? entry.data.authorPersonSlugs
            : entry.data.authorPersonSlug
              ? [entry.data.authorPersonSlug]
              : [];
          const authorPeople = authorPersonSlugs
            .map((slug) => localPeopleBySlug.get(slug))
            .filter(Boolean)
            .map((person) => toPublicationPerson(person as PersonSummary));
          const authorPerson =
            authorPeople[0] ??
            ((entry.data.authorPersonSlug && localPeopleBySlug.get(entry.data.authorPersonSlug))
              ? toPublicationPerson(localPeopleBySlug.get(entry.data.authorPersonSlug) as PersonSummary)
              : localPeopleByName.get(entry.data.author.toLowerCase())
                ? toPublicationPerson(localPeopleByName.get(entry.data.author.toLowerCase()) as PersonSummary)
                : undefined);
          const authorPersonSlugsSet = new Set(authorPeople.map((person) => person.slug).filter(Boolean));
          const contributors = [
            ...((entry.data.contributorPersonSlugs ?? [])
              .filter((slug) => !authorPersonSlugsSet.has(slug))
              .map((slug) => localPeopleBySlug.get(slug))
              .filter(Boolean)
              .map((person) => toPublicationPerson(person as PersonSummary))),
            ...((entry.data.contributorNames ?? []).map((name) => ({
              name,
              linked: false as const,
              image: "",
            }))),
          ];
          const relatedProjects = (entry.data.relatedProjectSlugs ?? [])
            .map((projectSlug) => localVisibleProjectsBySlug.get(projectSlug))
            .filter(Boolean)
            .map((project) => ({
              slug: project!.slug,
              title: project!.title,
              href: project!.href,
              color: project!.color,
            }));

          return {
            id: entry.id,
            slug: entry.id,
            href: `/blog/${entry.id}`,
            title: entry.data.title,
            description: entry.data.description,
            excerpt: entry.data.description,
            previewText: buildPublicationPreviewText(entry.data.description, entry.body, entry.data.title),
            pubDate: entry.data.pubDate,
            author:
              authorPeople.length > 0
                ? authorPeople.map((person) => person.name).join(", ")
                : entry.data.author,
            authorRole: entry.data.authorRole ?? authorPerson?.role,
            authorImage: entry.data.authorImage ?? authorPerson?.image ?? DEFAULT_PROFILE_IMAGE,
            authorPerson,
            authorPeople,
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
            references: (entry.data.references ?? []).map((reference) => ({
              name: reference.name,
              url: reference.url,
            })),
            relatedProjectIds: [],
            relatedProjectSlugs: relatedProjects.map((project) => project.slug),
            relatedProjectTitles: relatedProjects.map((project) => project.title),
            relatedProjects,
            downloadTrackedHref: entry.data.downloadHref,
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
  if (wordpressProjects && wordpressProjects.length > 0) {
    return wordpressProjects;
  }

  const peopleByName = new Map(localPeopleSummaries.map((person) => [person.name.toLowerCase(), person]));
  const localProjectsBySlug = new Map(localVisibleProjectPages.map((project) => [project.slug, project]));

  return localVisibleProjectPages.map((project) => ({
    id: project.slug,
    slug: project.slug,
    href: project.href,
    title: project.title,
    description: clampProjectDescription(project.description),
    cardIcon: project.cardIcon,
    parentProject: project.parentProjectSlug
      ? (() => {
          const parentProject = localProjectsBySlug.get(project.parentProjectSlug);

          return parentProject
            ? {
                id: parentProject.slug,
                slug: parentProject.slug,
                href: parentProject.href,
                title: parentProject.title,
              }
            : undefined;
        })()
      : undefined,
    alignedProject: project.alignedProjectSlug
      ? (() => {
          const alignedProject = localProjectsBySlug.get(project.alignedProjectSlug);

          return alignedProject
            ? {
                id: alignedProject.slug,
                slug: alignedProject.slug,
                href: alignedProject.href,
                title: alignedProject.title,
              }
            : undefined;
        })()
      : undefined,
    color: project.color,
    progressColor: project.color,
    currentStage: project.currentStage,
    stagePoints: project.stagePoints,
    hideProjectBar: project.hideProjectBar,
    leads: ((project.leads?.length ? project.leads : [project.lead]) ?? []).map((lead) =>
      buildProjectPerson(lead.name, peopleByName, {
        role: lead.role,
        image: lead.image,
      }),
    ),
    lead: buildProjectPerson(
      (project.leads?.[0] ?? project.lead).name,
      peopleByName,
      {
        role: (project.leads?.[0] ?? project.lead).role,
        image: (project.leads?.[0] ?? project.lead).image,
      },
    ),
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
  if (wordpressPeople && wordpressPeople.length > 0) {
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
  if (wordpressPublications && wordpressPublications.length > 0) {
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

  const localProject = localVisibleProjectPages.find((project) => project.slug === projectSlug);
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
      previewText: buildPublicationPreviewText(article.summary, article.title),
      pubDate: new Date(),
      author: article.author,
      authorPeople: [],
      contributors: [],
      references: [],
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
      relatedProjects: [
        {
          slug: localProject.slug,
          title: localProject.title,
          href: localProject.href,
          color: localProject.color,
        },
      ],
      source: "local" as const,
    }));
};

export const getPublicationsForPerson = async (personSlug: string) => {
  const publications = await getPublications();

  return publications.filter(
    (publication) =>
      publication.authorPeople.some((author) => author.slug === personSlug) ||
      publication.authorPerson?.slug === personSlug ||
      publication.contributors.some((contributor) => contributor.slug === personSlug),
  );
};

export const getHomepageSelections = async (): Promise<HomepageSelections> => {
  const wordpressSelections = await loadWordPressHomepageSelections();
  if (
    wordpressSelections &&
    (
      wordpressSelections.sliderPublications.length > 0 ||
      wordpressSelections.latestPublications.length > 0 ||
      Boolean(wordpressSelections.featuredArticle) ||
      Boolean(wordpressSelections.featuredPodcast)
    )
  ) {
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
    latestPublications: publications.slice(0, 5),
    projectCount: 0,
    projectIds: [],
  };
};

export const getSiteSettings = async (): Promise<SiteSettings> => {
  const wordpressSettings = await loadWordPressSiteSettings();

  if (wordpressSettings) {
    return wordpressSettings;
  }

  return defaultSiteSettings;
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

export const shouldShowPublicationLead = (publication: PublicationDetail) => {
  if (publication.source !== "wordpress") {
    return true;
  }

  return !isPreviewDuplicatedInContent(publication.description, publication.contentHtml);
};

export const getSimilarPublications = async (
  publication: PublicationSummary,
  limit = 5,
) => {
  const publications = await getPublications();
  const currentHashtagSlugs = new Set(publication.hashtags.map((tag) => tag.slug));

  return publications
    .filter((candidate) => candidate.slug !== publication.slug)
    .map((candidate) => ({
      publication: candidate,
      overlap: candidate.hashtags.filter((tag) => currentHashtagSlugs.has(tag.slug)).length,
    }))
    .filter((candidate) => candidate.overlap > 0)
    .sort((a, b) => {
      if (b.overlap !== a.overlap) {
        return b.overlap - a.overlap;
      }

      return b.publication.pubDate.valueOf() - a.publication.pubDate.valueOf();
    })
    .slice(0, limit)
    .map((candidate) => candidate.publication);
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
