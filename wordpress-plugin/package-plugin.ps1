param(
	[string] $LiveFolder = "medplatform-headless-clean-install"
)

$ErrorActionPreference = "Stop"

$pluginRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$sourceDir = Join-Path $pluginRoot "medplatform-headless"
$mainFile = Join-Path $sourceDir "medplatform-headless.php"

if (-not (Test-Path -LiteralPath $mainFile)) {
	throw "Plugin main file not found: $mainFile"
}

$versionLine = Select-String -LiteralPath $mainFile -Pattern '^\s*\*\s*Version:\s*(.+?)\s*$' | Select-Object -First 1

if (-not $versionLine) {
	throw "Could not find plugin Version header in $mainFile"
}

$version = $versionLine.Matches[0].Groups[1].Value.Trim()
$zipVersion = $version -replace '[^0-9A-Za-z]+', ''
$destination = Join-Path $pluginRoot "medplatform-headless-clean-install-v$zipVersion.zip"
$stagingRoot = Join-Path $pluginRoot ".plugin-package-staging"
$stagingPluginDir = Join-Path $stagingRoot $LiveFolder

if (Test-Path -LiteralPath $stagingRoot) {
	Remove-Item -LiteralPath $stagingRoot -Recurse -Force
}

try {
	New-Item -ItemType Directory -Force -Path $stagingPluginDir | Out-Null
	Copy-Item -Path (Join-Path $sourceDir "*") -Destination $stagingPluginDir -Recurse -Force
	Compress-Archive -Path $stagingPluginDir -DestinationPath $destination -Force
	Write-Output $destination
} finally {
	if (Test-Path -LiteralPath $stagingRoot) {
		Remove-Item -LiteralPath $stagingRoot -Recurse -Force
	}
}
