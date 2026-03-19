export interface LocalPerson {
  slug: string;
  name: string;
  role: string;
  image: string;
  email: string;
  linkedinUrl: string;
  websiteUrl?: string;
  shortBio: string;
  contentHtml: string;
  showOnTeamPage: boolean;
  menuOrder: number;
}

const defaultLinkedInUrl = "https://www.linkedin.com/";

export const localPeople: LocalPerson[] = [
  {
    slug: "luigi-narbone",
    name: "Luigi Narbone",
    role: "Director",
    image: "/Team/luigi-narbone.jpg",
    email: "luigi.narbone@mediterranean-platform.org",
    linkedinUrl: defaultLinkedInUrl,
    websiteUrl: "",
    shortBio:
      "Director with a background in EU and UN diplomacy, including roles as EU Ambassador in the Gulf and positions across multiple international contexts. His work focuses on MENA geopolitics, security, and political economy, alongside conflict dynamics and peacebuilding, bridging policy, diplomacy, and academic research.",
    contentHtml:
      "<p>Director with a background in EU and UN diplomacy, including roles as EU Ambassador in the Gulf and positions across multiple international contexts.</p><p>His work focuses on MENA geopolitics, security, and political economy, alongside conflict dynamics and peacebuilding, bridging policy, diplomacy, and academic research.</p>",
    showOnTeamPage: true,
    menuOrder: 1,
  },
  {
    slug: "virginie-collombier",
    name: "Virginie Collombier",
    role: "Scientific Coordinator",
    image: "/Team/virginie-collombier.jpg",
    email: "virginie.collombier@mediterranean-platform.org",
    linkedinUrl: defaultLinkedInUrl,
    websiteUrl: "",
    shortBio:
      "Scientific Coordinator working on socio-political transformations and geopolitical dynamics in the MENA region, with a strong focus on Libya. Her work examines state-society relations, power structures, and political dialogue, combining research with field-based initiatives and mentoring emerging researchers.",
    contentHtml:
      "<p>Scientific Coordinator working on socio-political transformations and geopolitical dynamics in the MENA region, with a strong focus on Libya.</p><p>Her work examines state-society relations, power structures, and political dialogue, combining research with field-based initiatives and mentoring emerging researchers.</p>",
    showOnTeamPage: true,
    menuOrder: 2,
  },
  {
    slug: "ilaria-bertocchini",
    name: "Ilaria Bertocchini",
    role: "Project Coordinator",
    image: "/Team/ilaria-bertocchini.jpg",
    email: "ilaria.bertocchini@mediterranean-platform.org",
    linkedinUrl: defaultLinkedInUrl,
    websiteUrl: "",
    shortBio:
      "Project Coordinator specializing in project management, monitoring and evaluation (M&E) across Europe and the MENA region. She brings experience in development cooperation, institutional processes, and coordination between stakeholders in complex project environments.",
    contentHtml:
      "<p>Project Coordinator specializing in project management, monitoring and evaluation (M&E) across Europe and the MENA region.</p><p>She brings experience in development cooperation, institutional processes, and coordination between stakeholders in complex project environments.</p>",
    showOnTeamPage: true,
    menuOrder: 3,
  },
  {
    slug: "meryem-akabouch",
    name: "Meryem Akabouch",
    role: "Research Associate",
    image: "/Team/meryem-akabouch.jpeg",
    email: "meryem.akabouch@mediterranean-platform.org",
    linkedinUrl: defaultLinkedInUrl,
    websiteUrl: "",
    shortBio:
      "Research Associate with a background in political theory and translation, focusing on political Islam, Europe-Africa relations, and social and political dynamics in the Mediterranean and Middle East, with an interest in intellectual and policy-oriented research.",
    contentHtml:
      "<p>Research Associate with a background in political theory and translation, focusing on political Islam, Europe-Africa relations, and social and political dynamics in the Mediterranean and Middle East.</p><p>Her work combines intellectual inquiry with policy-oriented research and regional analysis.</p>",
    showOnTeamPage: true,
    menuOrder: 4,
  },
  {
    slug: "carlo-palleschi",
    name: "Carlo Palleschi",
    role: "Associate Researcher",
    image: "/Team/carlo-palleschi.jpg",
    email: "carlo.palleschi@mediterranean-platform.org",
    linkedinUrl: defaultLinkedInUrl,
    websiteUrl: "",
    shortBio:
      "Associate Researcher and World Bank consultant working on African development and environmental issues, with experience across international organizations, research centres, and academic institutions, contributing to policy-oriented analysis and publications.",
    contentHtml:
      "<p>Associate Researcher and World Bank consultant working on African development and environmental issues, with experience across international organizations, research centres, and academic institutions.</p><p>He contributes to policy-oriented analysis, publications, and cross-regional project development.</p>",
    showOnTeamPage: true,
    menuOrder: 5,
  },
  {
    slug: "meraf-villani",
    name: "Meraf Villani",
    role: "Associate Researcher",
    image: "/Team/meraf-villani.jpg",
    email: "meraf.villani@mediterranean-platform.org",
    linkedinUrl: defaultLinkedInUrl,
    websiteUrl: "",
    shortBio:
      "Associate Researcher focusing on Africa-Europe relations, dialogue initiatives, and cultural exchange. She is actively engaged in projects that strengthen connections between regions, while contributing to discussions on African geopolitics and moderating international platforms.",
    contentHtml:
      "<p>Associate Researcher focusing on Africa-Europe relations, dialogue initiatives, and cultural exchange.</p><p>She is actively engaged in projects that strengthen connections between regions, while contributing to discussions on African geopolitics and moderating international platforms.</p>",
    showOnTeamPage: true,
    menuOrder: 6,
  },
  {
    slug: "leon-stille",
    name: "Leon Stille",
    role: "Energy Expert",
    image: "/Team/leon-stille.jpg",
    email: "leon.stille@mediterranean-platform.org",
    linkedinUrl: defaultLinkedInUrl,
    websiteUrl: "",
    shortBio:
      "Energy expert specializing in energy transition, hydrogen, and renewable technologies, with experience across industry, research, and education. His work combines technical expertise with project development, innovation, and policy engagement in the energy sector.",
    contentHtml:
      "<p>Energy expert specializing in energy transition, hydrogen, and renewable technologies, with experience across industry, research, and education.</p><p>His work combines technical expertise with project development, innovation, and policy engagement in the energy sector.</p>",
    showOnTeamPage: true,
    menuOrder: 7,
  },
];
