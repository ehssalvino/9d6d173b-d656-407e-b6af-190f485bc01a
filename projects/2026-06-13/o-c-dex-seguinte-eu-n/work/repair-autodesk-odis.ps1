$ErrorActionPreference = 'Continue'

$resultFile = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\odis-repair-result.csv'
$installer = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\AdODIS-installer.exe'
$remover = 'C:\Program Files\Autodesk\AdODIS\V1\RemoveODIS.exe'
$results = @()

Get-Service -ErrorAction SilentlyContinue |
    Where-Object {
        $_.Name -in 'Autodesk Access Service Host', 'Autodesk Service'
    } |
    Stop-Service -Force -ErrorAction SilentlyContinue

Get-Process -ErrorAction SilentlyContinue |
    Where-Object {
        $_.ProcessName -in @(
            'AdskAccessService',
            'AdskAccessServiceHost',
            'AdskExecutorProxy',
            'AdskInstallerUpdateCheck',
            'AdskUpdateCheck',
            'Installer',
            'ProcessManager',
            'DownloadManager'
        )
    } |
    Stop-Process -Force -ErrorAction SilentlyContinue

if (Test-Path -LiteralPath $remover) {
    $removeProcess = Start-Process -FilePath $remover `
        -ArgumentList @('--mode', 'unattended', '--unattendedmodeui', 'none') `
        -WindowStyle Hidden `
        -Wait `
        -PassThru
    $results += [PSCustomObject]@{
        Action = 'Remove existing Autodesk ODIS'
        ExitCode = $removeProcess.ExitCode
        Success = $removeProcess.ExitCode -eq 0
    }
}
else {
    $results += [PSCustomObject]@{
        Action = 'Remove existing Autodesk ODIS'
        ExitCode = 0
        Success = $true
    }
}

Start-Sleep -Seconds 3

$installProcess = Start-Process -FilePath $installer `
    -ArgumentList @('--mode', 'unattended', '--unattendedmodeui', 'none') `
    -WindowStyle Hidden `
    -Wait `
    -PassThru
$results += [PSCustomObject]@{
    Action = 'Install Autodesk ODIS from signed media'
    ExitCode = $installProcess.ExitCode
    Success = $installProcess.ExitCode -eq 0
}

$results | Export-Csv -LiteralPath $resultFile -NoTypeInformation -Encoding UTF8
