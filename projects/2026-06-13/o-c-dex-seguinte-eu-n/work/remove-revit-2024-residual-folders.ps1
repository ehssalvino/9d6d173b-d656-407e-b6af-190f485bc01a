$ErrorActionPreference = 'Stop'

$targets = @(
    'D:\Program Files\Autodesk\Revit 2024',
    'C:\ProgramData\Autodesk\RVT 2024',
    "$env:LOCALAPPDATA\Autodesk\Revit\Autodesk Revit 2024",
    "$env:APPDATA\Autodesk\Revit\Autodesk Revit 2024"
)
$allowed = $targets | ForEach-Object {
    [IO.Path]::GetFullPath($_).TrimEnd('\')
}
$results = @()

foreach ($target in $targets) {
    $resolved = [IO.Path]::GetFullPath($target).TrimEnd('\')
    if ($resolved -notin $allowed) {
        throw "Caminho fora da lista permitida: $resolved"
    }

    if (Test-Path -LiteralPath $resolved) {
        Remove-Item -LiteralPath $resolved -Recurse -Force -ErrorAction Stop
    }

    $results += [PSCustomObject]@{
        Path = $resolved
        Removed = -not (Test-Path -LiteralPath $resolved)
    }
}

$results | Export-Csv `
    -LiteralPath 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-residual-cleanup.csv' `
    -NoTypeInformation `
    -Encoding UTF8
