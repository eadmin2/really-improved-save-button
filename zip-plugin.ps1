# PowerShell script to zip WordPress plugin files for upload
# Follows WordPress recommendations: includes all plugin files/folders at root, excludes dev/system files

$pluginDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$pluginName = Split-Path $pluginDir -Leaf
$zipPath = Join-Path $pluginDir '..\really-improved-save-button.zip'

# List of top-level folders/files to exclude
$exclude = @('node_modules', '.git', 'scss', 'zip-plugin.ps1', 'really-improved-save-button.zip', 'package.json', 'package-lock.json', 'classic-editor', 'improved-save-button')

# Remove old zip if exists
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

# Build a list of full paths to include (files and folders), recursively filter out .json, .md, .map
$pathsToZip = @()
$items = Get-ChildItem -Path $pluginDir -Force | Where-Object { $exclude -notcontains $_.Name }
foreach ($item in $items) {
    if ($item.PSIsContainer) {
        $files = Get-ChildItem -Path $item.FullName -Recurse -File | Where-Object { $_.Extension -notin '.json', '.md', '.map' }
        foreach ($file in $files) {
            # Add files with relative path under plugin folder
            $relativePath = Join-Path $pluginName $file.FullName.Substring($pluginDir.Length + 1)
            $pathsToZip += @{ Path = $file.FullName; RelativePath = $relativePath }
        }
    } elseif ($item.Extension -notin '.json', '.md', '.map') {
        $relativePath = Join-Path $pluginName $item.Name
        $pathsToZip += @{ Path = $item.FullName; RelativePath = $relativePath }
    }
}

# Create a temporary staging folder to build the correct structure
$tempStaging = Join-Path $pluginDir '..\_zip_staging'
if (Test-Path $tempStaging) { Remove-Item $tempStaging -Recurse -Force }
New-Item -ItemType Directory -Path $tempStaging | Out-Null
$pluginStaging = Join-Path $tempStaging $pluginName
New-Item -ItemType Directory -Path $pluginStaging | Out-Null

# Copy files to the staging folder with correct structure
foreach ($entry in $pathsToZip) {
    $dest = Join-Path $tempStaging $entry.RelativePath
    $destDir = Split-Path $dest -Parent
    if (!(Test-Path $destDir)) { New-Item -ItemType Directory -Path $destDir -Force | Out-Null }
    Copy-Item -Path $entry.Path -Destination $dest -Force
}

# Create the zip archive from the staging folder
Compress-Archive -Path (Join-Path $tempStaging $pluginName) -DestinationPath $zipPath -Force

# Clean up staging
Remove-Item $tempStaging -Recurse -Force

Write-Host "Plugin zipped to $zipPath with correct folder structure." 