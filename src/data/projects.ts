export interface ProjectPage {
  slug: string;
  href: string;
  title: string;
  description: string;
  color: string;
  currentStage: string;
  stagePoints: string[];
  hideProjectBar: boolean;
  overview: string[];
  updates: string[];
  lead: {
    name: string;
    role: string;
    image: string;
  };
  team: string[];
  donors: Array<{
    name: string;
    logo: string;
  }>;
  focusAreas: Array<{
    title: string;
    description: string;
  }>;
}

export const projectStages = [
  "Conception",
  "Research",
  "Dialogue",
  "Publication",
  "Implemented",
];

export const projectPages: ProjectPage[] = [
  {
    slug: "libya-platform",
    href: "/services/libya-platform",
    title: "Libya Platform",
    description:
      "Field-based research, capacity building, and dialogue support focused on governance processes, youth participation, and practical policy pathways.",
    color: "#15243a",
    currentStage: "Dialogue",
    stagePoints: [...projectStages],
    hideProjectBar: false,
    overview: [
      "The Libya Platform brings together research, policy dialogue, and operational support around governance transitions, local institutional design, and inclusive public debate.",
      "Its work is grounded in practical pathways for policymaking, with a focus on how local and national processes can be linked more effectively in conflict-affected and politically fragmented settings.",
      "Youth participation, dialogue support, and institution-focused analysis remain central to the platform’s work across publications, convenings, and project development.",
    ],
    updates: [
      "Current work focuses on governance design, local institutional balance, and dialogue-linked policy pathways.",
      "Recent outputs connect subnational governance debates with national dialogue and peace process questions.",
      "The programme is expanding its youth-facing and capacity-building stream through publications and targeted convenings.",
    ],
    lead: {
      name: "Virginie Collombier",
      role: "Program Lead",
      image: "/Team/virginie-collombier.jpg",
    },
    team: ["Ilaria Bertocchini", "Meryem Akabouch", "Meraf Villani"],
    donors: [
      { name: "European Commission", logo: "/Logos/European-Commission.png" },
      { name: "LDTF", logo: "/Logos/Logo-LDTF.png" },
      { name: "UNIMED", logo: "/Logos/Unimed.png" },
    ],
    focusAreas: [
      {
        title: "Governance Processes",
        description:
          "Research and dialogue on institutional design, local governance, and state-society relations.",
      },
      {
        title: "National Dialogue",
        description:
          "Support for inclusive dialogue formats, peace process reflection, and operational policy pathways.",
      },
      {
        title: "Youth Participation",
        description:
          "Work on participation, mentorship, and long-term civic inclusion in future-facing policy spaces.",
      },
    ],
  },
  {
    slug: "africa-nexus",
    href: "/services/africa-nexus",
    title: "Africa Nexus",
    description:
      "Policy analyses, crossroads exchanges, and knowledge ecosystems linking African and European experts, researchers, and decision-makers.",
    color: "#42b09a",
    currentStage: "Publication",
    stagePoints: [...projectStages],
    hideProjectBar: false,
    overview: [
      "Africa Nexus is designed as a space for African-European exchange, connecting policy research, dialogue initiatives, and expert communities across regions.",
      "It focuses on building durable knowledge ecosystems that can support stronger analysis, deeper collaboration, and more grounded conversations between institutions and practitioners.",
      "Themes such as sustainable development, energy transition, and cross-regional cooperation shape the platform’s editorial and convening agenda.",
    ],
    updates: [
      "The current cycle concentrates on Africa-Europe knowledge exchange and cross-regional policy analysis.",
      "Energy transition and sustainable development publications are being used as anchors for a wider dialogue series.",
      "The platform is moving from network-building into more visible editorial and partnership outputs.",
    ],
    lead: {
      name: "Carlo Palleschi",
      role: "Program Lead",
      image: "/Team/carlo-palleschi.jpg",
    },
    team: ["Meraf Villani", "Leon Stille", "Meryem Akabouch"],
    donors: [
      { name: "PM SOG", logo: "/Logos/PM-SOG-Logo.png.jpg" },
      { name: "European Commission", logo: "/Logos/European-Commission.png" },
      { name: "UNIMED", logo: "/Logos/Unimed.png" },
    ],
    focusAreas: [
      {
        title: "Knowledge Exchange",
        description:
          "Crossroads exchanges between experts, researchers, and policymakers working across African and European contexts.",
      },
      {
        title: "Africa-Europe Dialogue",
        description:
          "Policy-facing conversations on regional priorities, institutions, and transnational cooperation.",
      },
      {
        title: "Sustainable Development",
        description:
          "Research on climate, energy transition, and long-term development choices shaping regional futures.",
      },
    ],
  },
  {
    slug: "gulf-platform",
    href: "/services/gulf-platform",
    title: "Gulf Platform",
    description:
      "Regional research and dialogue on Europe-Gulf relations, geopolitical shifts, sectoral cooperation, and long-term strategic trends.",
    color: "#8c6335",
    currentStage: "Implemented",
    stagePoints: [...projectStages],
    hideProjectBar: false,
    overview: [
      "The Gulf Platform develops policy-relevant research and dialogue around Europe-Gulf relations, strategic realignment, and the broader political economy of regional change.",
      "Its work tracks geopolitical shifts, sectoral cooperation, and the evolving role of Gulf actors in diplomacy, security, and cross-regional engagement.",
      "The platform is intended to support a clearer understanding of long-term trends through publications, analytical briefs, and structured exchange.",
    ],
    updates: [
      "The platform is already operating as an active editorial and dialogue stream across geopolitical and security topics.",
      "Recent work connects Europe-Gulf analysis with maritime issues, economic shifts, and long-term strategic positioning.",
      "Implementation now focuses on consolidating outputs, partner engagement, and recurring publication formats.",
    ],
    lead: {
      name: "Luigi Narbone",
      role: "Program Lead",
      image: "/Team/luigi-narbone.jpg",
    },
    team: ["Leon Stille", "Ilaria Bertocchini", "Carlo Palleschi"],
    donors: [
      { name: "download-1", logo: "/Logos/download-1.png" },
      { name: "unnamed", logo: "/Logos/unnamed-e1709546741640.png" },
      { name: "European Commission", logo: "/Logos/European-Commission.png" },
    ],
    focusAreas: [
      {
        title: "Europe-Gulf Relations",
        description:
          "Analysis of diplomatic, economic, and strategic ties between Gulf actors and European institutions.",
      },
      {
        title: "Regional Security",
        description:
          "Policy-oriented work on geopolitical developments, maritime issues, and shifting security architectures.",
      },
      {
        title: "Strategic Trends",
        description:
          "Long-term research on sectoral cooperation, social change, and regional power dynamics.",
      },
    ],
  },
];

export const projectArticles = [
  {
    title: "The Red Sea: Divided by Water, United by Opportunities",
    summary:
      "A multi-disciplinary brief on regional opportunity structures, cooperation pathways, and strategic dialogue across the Red Sea and Mediterranean space.",
    author: "Ghanem, Narbone, Palleschi, and Maru (ed.)",
    date: "Feb 25, 2026",
    topic: "Regional Cooperation",
    platform: "Gulf Platform",
    hashtags: ["RedSea", "RegionalCooperation", "StrategicDialogue"],
    image: "/articles/red-sea-ebook.pdf.png",
    imageAlt: "The Red Sea publication cover",
  },
  {
    title: "Distribution of Powers in Libya's Future Local Governance System",
    summary:
      "A policy-oriented assessment of local governance design and pathways for effective institutional balance.",
    author: "Intissar Kherigi",
    date: "Feb 5, 2026",
    topic: "Governance",
    platform: "Libya Platform",
    hashtags: ["Libya", "LocalGovernance", "InstitutionalDesign"],
    image: "/blog/blog_post_10_1768850582328.webp",
    imageAlt: "Publication thumbnail placeholder for local governance policy paper",
  },
  {
    title:
      "Subnational Governance in Divided Societies: Learning from Yemen to Inform Libya's Peace Process",
    summary:
      "Comparative insights connecting local governance, peace process design, and practical dialogue support.",
    author: "Mohamed Aziz Mrad",
    date: "Jan 8, 2026",
    topic: "Dialogue Support",
    platform: "Libya Platform",
    hashtags: ["Libya", "PeaceProcess", "DialogueSupport"],
    image: "/blog/blog_post_11_1768850597259.webp",
    imageAlt: "Publication thumbnail placeholder for peace process policy paper",
  },
  {
    title:
      "Youth as Catalysts for Shaping Libya's Future Pathways for Inclusion in National Dialogue and Vision-Making",
    summary:
      "A paper on youth participation, skills development, and practical inclusion in national dialogue design.",
    author: "Abdelkarim Skouri",
    date: "Dec 2, 2025",
    topic: "Youth Participation",
    platform: "Libya Platform",
    hashtags: ["Youth", "NationalDialogue", "CapacityBuilding"],
    image: "/blog/blog_post_12_1768850610966.webp",
    imageAlt: "Publication thumbnail placeholder for youth participation paper",
  },
  {
    title: "Africa Nexus: Building an African-European Knowledge Ecosystem",
    summary:
      "A platform briefing connecting experts, young scholars, and policymakers through policy analyses and knowledge exchange.",
    author: "Africa Nexus Team",
    date: "Latest update",
    topic: "Knowledge Exchange",
    platform: "Africa Nexus",
    hashtags: ["AfricaNexus", "CrossroadsExchanges", "KnowledgeExchange"],
    image: "/blog/welcome.webp",
    imageAlt: "Africa Nexus article thumbnail",
  },
  {
    title:
      "Hydrogen Valleys and Sustainable Development in Algeria: Pivoting from Hydrocarbons to an Inclusive Euro-Mediterranean Hydrogen Economy",
    summary:
      "Research on energy transition, industrial strategy, and climate-linked development in North Africa.",
    author: "Leon Stille",
    date: "Nov 21, 2025",
    topic: "Energy Transition",
    platform: "Africa Nexus",
    hashtags: ["EnergyTransition", "ClimatePolicy", "SustainableDevelopment"],
    image: "/blog/markdown.webp",
    imageAlt: "Energy transition article thumbnail",
  },
  {
    title:
      "Gulf Platform: Europe-Gulf Dialogue on Geopolitical, Economic, and Societal Trends",
    summary:
      "Policy-relevant research and dialogue support for decision-makers working across regional security and long-term cooperation files.",
    author: "Gulf Platform Team",
    date: "Latest update",
    topic: "Geopolitics",
    platform: "Gulf Platform",
    hashtags: ["GulfPlatform", "RegionalSecurity", "PolicyRelevant"],
    image: "/blog/blog_post_8_1768850554501.webp",
    imageAlt: "Gulf Platform article thumbnail",
  },
];

export const projectHrefByTitle = Object.fromEntries(
  projectPages.map((project) => [project.title, project.href]),
) as Record<string, string>;
