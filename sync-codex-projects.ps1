param(
    [string]$SourceRoot = 'C:\Users\Eduardo\Documents\Codex',
    [string]$TargetRoot = 'C:\Users\Eduardo\Documents\Codex\codex-projetos-git',
    [int64]$MaxFileMB = 50
)

$ErrorActionPreference = 'Stop'

$maxBytes = $MaxFileMB * 1MB
$excludedDirNames = @(
    '.git', '.agents', '.codex', 'node_modules', 'vendor', '__pycache__',
    '.next', 'dist', 'build', 'coverage', '.venv', 'venv', 'env', '_internal'
)

$excludedPathPatterns = @(
    '\\chrome[^\\]*profile',
    '\\edge[^\\]*profile',
    '\\[^\\]*debug-profile',
    '\\chrome-cdp-profile',
    '\\mysql-',
    '\\elementor-[^\\]*-extract',
    '\\wordpress-extract',
    '\\installed-[^\\]*',
    '\\Safe Browsing\\',
    '\\Service Worker\\',
    '\\CacheStorage\\',
    '\\GPUCache\\',
    '\\Cache\\',
    '\\Code Cache\\',
    '\\ShaderCache\\',
    '\\DawnWebGPUCache\\',
    '\\DawnGraphiteCache\\'
)

$excludedExtensions = @(
    '.zip', '.rar', '.7z', '.tmp', '.bak', '.old', '.log',
    '.ibd', '.frm', '.mad', '.mai', '.pma', '.opt', '.db', '.db-journal',
    '.store', '.dat', '.bin', '.dll', '.exe', '.msi'
)

$excludedFileNames = @(
    '.env', 'Thumbs.db', '.DS_Store',
    'codex-auto-login.php',
    'check-editor-assets.php',
    'check-elementor-editor.php',
    'fetch-elementor-editor.php',
    'fetch-elementor-preview.php'
)

function Test-IsExcludedPath {
    param([string]$Path)

    $relative = $Path.Substring($SourceRoot.Length).TrimStart('\')
    if ($relative.StartsWith('codex-projetos-git\', [StringComparison]::OrdinalIgnoreCase)) {
        return $true
    }

    foreach ($name in $excludedDirNames) {
        if ($Path -match ('\\' + [Regex]::Escape($name) + '(\\|$)')) {
            return $true
        }
    }

    foreach ($pattern in $excludedPathPatterns) {
        if ($Path -match $pattern) {
            return $true
        }
    }

    return $false
}

function Test-IsExcludedFile {
    param([System.IO.FileInfo]$File)

    if (Test-IsExcludedPath $File.FullName) {
        return $true
    }

    if ($excludedFileNames -contains $File.Name) {
        return $true
    }

    if ($File.Name -like '.env.*') {
        return $true
    }

    if ($excludedExtensions -contains $File.Extension.ToLowerInvariant()) {
        return $true
    }

    if ($File.Length -gt $maxBytes) {
        return $true
    }

    return $false
}

$projectsRoot = Join-Path $TargetRoot 'projects'
New-Item -ItemType Directory -Force -Path $projectsRoot | Out-Null

$sourceDirs = Get-ChildItem -LiteralPath $SourceRoot -Directory -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -match '^\d{4}-\d{2}-\d{2}$' }

$copied = 0
$skipped = 0

foreach ($dateDir in $sourceDirs) {
    $candidateDirs = Get-ChildItem -LiteralPath $dateDir.FullName -Directory -Recurse -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -in @('work', 'outputs') -and -not (Test-IsExcludedPath $_.FullName) }

    foreach ($dir in $candidateDirs) {
        $relative = $dir.FullName.Substring($SourceRoot.Length).TrimStart('\')
        $targetDir = Join-Path $projectsRoot $relative
        New-Item -ItemType Directory -Force -Path $targetDir | Out-Null

        $files = Get-ChildItem -LiteralPath $dir.FullName -File -Recurse -ErrorAction SilentlyContinue
        foreach ($file in $files) {
            if (Test-IsExcludedFile $file) {
                $skipped++
                continue
            }

            $fileRelative = $file.FullName.Substring($dir.FullName.Length).TrimStart('\')
            $targetFile = Join-Path $targetDir $fileRelative
            if ($targetFile.Length -gt 240) {
                $skipped++
                continue
            }

            $targetParent = Split-Path -Parent $targetFile
            New-Item -ItemType Directory -Force -Path $targetParent | Out-Null
            try {
                Copy-Item -LiteralPath $file.FullName -Destination $targetFile -Force
                $copied++
            } catch {
                $skipped++
            }
        }
    }
}

$summary = @"
# Codex Projetos

Repositorio central com copias filtradas dos projetos e entregaveis produzidos em chats do Codex.

Origem local: `$SourceRoot`
Atualizado em: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss zzz')

Arquivos copiados: $copied
Arquivos ignorados por filtro/tamanho: $skipped

## Estrutura

Os projetos ficam em `projects/`, organizados por data, nome do chat e pasta (`work` ou `outputs`).

## Filtros

Este repositorio ignora caches, perfis de navegador, bancos locais, pacotes baixados, logs, temporarios,
arquivos grandes e helpers locais que criam cookies ou logins temporarios.
"@

Set-Content -LiteralPath (Join-Path $TargetRoot 'README.md') -Value $summary -Encoding UTF8

Write-Output "copied=$copied"
Write-Output "skipped=$skipped"





