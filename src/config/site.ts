import ogImage from "../assets/og-image.png";

export const siteConfig = {
  name: "Mediterranean Platform",
  description:
    "Research, dialogue, and educational programme promoting informed policymaking and advocacy across the Mediterranean space.",
  url: "https://mediterranean-platform.org",
  lang: "en",
  locale: "en_US",
  author: "Mediterranean Platform",
  twitter: "@medplatform",
  ogImage: ogImage,
  socialLinks: {
    linkedin: "https://www.linkedin.com/",
    youtube: "https://www.youtube.com/",
    instagram: "",
    contact: "/contact",
  },
  navLinks: [
    { text: "Home", href: "/" },
    { text: "About", href: "/about" },
    { text: "Projects", href: "/services" },
    { text: "Media", href: "/widgets" },
    { text: "Publications", href: "/blog" },
    { text: "Contact", href: "/contact" },
  ],
};
