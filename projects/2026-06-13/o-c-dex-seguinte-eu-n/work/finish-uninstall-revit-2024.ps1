$ErrorActionPreference = 'Stop'

$logDir = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-uninstall-logs'
$revitMsi = 'G:\Meu Drive\Softwares\Vietcons_RVT 2024\x64\RVT\RVT.msi'

if (-not (Test-Path -LiteralPath $revitMsi)) {
    throw "Pacote original nao encontrado: $revitMsi"
}

$results = @()

$revitArgs = @(
    '/x',
    "`"$revitMsi`"",
    '/qn',
    '/norestart',
    '/L*v',
    "`"$logDir\Revit_2024_from_original_media.log`""
)
$revit = Start-Process -FilePath "$env:SystemRoot\System32\msiexec.exe" `
    -ArgumentList $revitArgs -Wait -PassThru
$results += [PSCustomObject]@{
    Product = 'Revit 2024 from original media'
    ExitCode = $revit.ExitCode
    Success = $revit.ExitCode -in 0, 1605, 1614, 3010
}

$steelArgs = @(
    '/x',
    '{C430585C-2024-4514-A253-D0C70D33ADD5}',
    'INSTALLDIR="D:\Program Files\Autodesk\Revit 2024\"',
    'ARPINSTALLLOCATION="D:\Program Files\Autodesk\Revit 2024\"',
    '/qn',
    '/norestart',
    '/L*v',
    "`"$logDir\Steel_Connections_retry.log`""
)
$steel = Start-Process -FilePath "$env:SystemRoot\System32\msiexec.exe" `
    -ArgumentList $steelArgs -Wait -PassThru
$results += [PSCustomObject]@{
    Product = 'Autodesk Steel Connections Core Content for Revit 2024'
    ExitCode = $steel.ExitCode
    Success = $steel.ExitCode -in 0, 1605, 1614, 3010
}

$results | Export-Csv -LiteralPath "$logDir\finish-results.csv" -NoTypeInformation -Encoding UTF8
$results | Format-Table -AutoSize
