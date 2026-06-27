$ErrorActionPreference = 'SilentlyContinue'

Get-Process -Name 'msedge', 'ms-teams', 'MSTeams' | Stop-Process -Force
Start-Sleep -Seconds 2

$exactTargets = @(
    'C:\Users\Eduardo\AppData\Local\Microsoft\Edge\User Data\Default\Cache',
    'C:\Users\Eduardo\AppData\Local\Microsoft\Edge\User Data\Default\Code Cache',
    'C:\Users\Eduardo\AppData\Local\Microsoft\Edge\User Data\Default\GPUCache',
    'C:\Users\Eduardo\AppData\Local\Microsoft\OneDrive\logs'
)

foreach ($target in $exactTargets) {
    if (Test-Path -LiteralPath $target) {
        Remove-Item -LiteralPath $target -Recurse -Force
    }
}

$teamsRoot = 'C:\Users\Eduardo\AppData\Local\Packages\MSTeams_8wekyb3d8bbwe'
if (Test-Path -LiteralPath $teamsRoot) {
    Get-ChildItem -LiteralPath $teamsRoot -Recurse -Directory -Force |
        Where-Object { $_.Name -in @('Cache', 'Code Cache', 'GPUCache') } |
        Sort-Object FullName -Descending |
        Remove-Item -Recurse -Force
}

$cutoff = [datetime]'2026-06-13T20:00:00'
$tempRoot = 'C:\Users\Eduardo\AppData\Local\Temp'
Get-ChildItem -LiteralPath $tempRoot -Force |
    Where-Object { $_.LastWriteTime -lt $cutoff } |
    Remove-Item -Recurse -Force

Clear-RecycleBin -DriveLetter C -Force
