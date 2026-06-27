$ErrorActionPreference = 'Stop'

$executables = @(
    'AcEventSync.exe',
    'AcQMod.exe',
    'ADPClientService.exe',
    'AdSSO.exe',
    'DownloadManager.exe',
    'GenuineService.exe',
    'install_helper_tool.exe',
    'install_manager.exe',
    'LogAnalyzer.exe',
    'ProcessManager.exe'
)
$nativeBase = 'HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Image File Execution Options'
$psBase = 'HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Image File Execution Options'
$backupDir = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-backup\ifeo'
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

$results = foreach ($executable in $executables) {
    $psPath = Join-Path $psBase $executable
    if (-not (Test-Path -LiteralPath $psPath)) {
        [PSCustomObject]@{ Executable = $executable; Removed = $false; Reason = 'Absent' }
        continue
    }

    $debugger = (Get-ItemProperty -LiteralPath $psPath -Name Debugger -ErrorAction SilentlyContinue).Debugger
    if ($debugger -ne 'Blocked') {
        [PSCustomObject]@{ Executable = $executable; Removed = $false; Reason = "Debugger=$debugger" }
        continue
    }

    $nativePath = "$nativeBase\$executable"
    $backupPath = Join-Path $backupDir "$executable.reg"
    & reg.exe export $nativePath $backupPath /y | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao exportar $nativePath"
    }

    Remove-Item -LiteralPath $psPath -Recurse -Force
    [PSCustomObject]@{ Executable = $executable; Removed = $true; Reason = 'Debugger=Blocked' }
}

$results | Export-Csv `
    -LiteralPath 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\ifeo-cleanup-result.csv' `
    -NoTypeInformation `
    -Encoding UTF8
