$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path -Parent $PSScriptRoot
$releaseDir = Join-Path $repoRoot 'releases'
New-Item -ItemType Directory -Path $releaseDir -Force | Out-Null
Compress-Archive -LiteralPath (Join-Path $repoRoot 'wordpress/puratek-flow-importer') -DestinationPath (Join-Path $releaseDir 'puratek-flow-importer-1.9.0-LOCAL-PROTOTYPE.zip') -Force
Compress-Archive -Path (Join-Path $repoRoot 'automation-packages/puratek-local/*') -DestinationPath (Join-Path $releaseDir 'puratek-builder-LOCAL-ONLY.zip') -Force
Write-Output "Created plugin and local automation ZIPs in $releaseDir"
