import path from "node:path";
import { execFileSync } from "node:child_process";
import fs from "node:fs/promises";

const rootDir = process.cwd();
const wpDir = path.join(rootDir, ".wp-local");
const pluginSource = path.join(rootDir, "wordpress-plugin", "medplatform-headless");
const pluginTarget = path.join(wpDir, "wp-content", "plugins", "medplatform-headless");
const dbName = "medplatform_wp";
const dbUser = "sufianararah";
const dbHost = "localhost";
const siteUrl = "http://127.0.0.1:8081";
const adminUser = "admin";
const adminPassword = "admin12345!";
const adminEmail = "admin@medplatform.local";
const wpCliBinary = execFileSync("which", ["wp"], { cwd: rootDir, encoding: "utf8" }).trim();

const run = (command, args, options = {}) => {
  console.log(`> ${command} ${args.join(" ")}`);
  execFileSync(command, args, {
    cwd: rootDir,
    stdio: "inherit",
    ...options,
  });
};

const wp = (...args) => run("php", ["-d", "memory_limit=512M", wpCliBinary, ...args]);

await fs.mkdir(wpDir, { recursive: true });

run("mysql", [
  `-u${dbUser}`,
  "-e",
  `CREATE DATABASE IF NOT EXISTS \`${dbName}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`,
]);

try {
  await fs.access(path.join(wpDir, "wp-load.php"));
} catch {
  wp("core", "download", `--path=${wpDir}`, "--force");
}

try {
  await fs.access(path.join(wpDir, "wp-config.php"));
} catch {
  wp(
    "config",
    "create",
    `--path=${wpDir}`,
    `--dbname=${dbName}`,
    `--dbuser=${dbUser}`,
    "--dbpass=",
    `--dbhost=${dbHost}`,
    "--skip-check",
    "--force",
  );
}

let isInstalled = true;
try {
  wp("core", "is-installed", `--path=${wpDir}`);
} catch {
  isInstalled = false;
}

if (!isInstalled) {
  wp(
    "core",
    "install",
    `--path=${wpDir}`,
    `--url=${siteUrl}`,
    "--title=Mediterranean Platform CMS",
    `--admin_user=${adminUser}`,
    `--admin_password=${adminPassword}`,
    `--admin_email=${adminEmail}`,
    "--skip-email",
  );
}

await fs.rm(pluginTarget, { recursive: true, force: true });
await fs.cp(pluginSource, pluginTarget, { recursive: true });

wp("plugin", "activate", "medplatform-headless", `--path=${wpDir}`);
wp("rewrite", "structure", "/%postname%/", `--path=${wpDir}`);
wp("option", "update", "blog_public", "0", `--path=${wpDir}`);

run("node", ["scripts/generate-wordpress-seed.mjs"]);
wp("eval-file", "scripts/seed-wordpress.php", `--path=${wpDir}`);

console.log("");
console.log("Local WordPress is ready.");
console.log(`WordPress admin: ${siteUrl}/wp-admin`);
console.log(`Username: ${adminUser}`);
console.log(`Password: ${adminPassword}`);
console.log("Run `npm run wp:serve` to start local WordPress.");
console.log("Run `npm run dev:local-cms` to preview Astro against local WordPress.");
console.log("Run `npm run dev` or `npm run dev:live-cms` to keep Astro pointed at the live CMS.");
