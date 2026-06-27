$ErrorActionPreference = 'Stop'

$logDir = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-uninstall-logs'
$revitMsi = 'G:\Meu Drive\Softwares\Vietcons_RVT 2024\x64\RVT\RVT.msi'
$msiexec = "$env:SystemRoot\System32\msiexec.exe"

$results = @()

$recacheArguments = "/fvomus `"$revitMsi`" /qn /norestart /L*v `"$logDir\Revit_2024_recache.log`""
$recache = Start-Process -FilePath $msiexec -ArgumentList $recacheArguments -Wait -PassThru
$results += [PSCustomObject]@{
    Action = 'Rebuild Revit 2024 Windows Installer cache'
    ExitCode = $recache.ExitCode
    Success = $recache.ExitCode -in 0, 3010
}

if ($recache.ExitCode -in 0, 3010) {
    $removeArguments = '/x {7346B4A0-2400-0510-0000-705C0D862004} /qn /norestart ' +
        "/L*v `"$logDir\Revit_2024_after_recache.log`""
    $remove = Start-Process -FilePath $msiexec -ArgumentList $removeArguments -Wait -PassThru
    $results += [PSCustomObject]@{
        Action = 'Remove Revit 2024 after cache rebuild'
        ExitCode = $remove.ExitCode
        Success = $remove.ExitCode -in 0, 1605, 1614, 3010
    }
}

$steelArguments = '/x {C430585C-2024-4514-A253-D0C70D33ADD5} ' +
    'REVITINSTALL_ROOT="D:\Program Files\Autodesk\" ADSK_INSTALL_PATH="Revit 2024" ' +
    "/qn /norestart /L*v `"$logDir\Steel_Connections_with_root.log`""
$steel = Start-Process -FilePath $msiexec -ArgumentList $steelArguments -Wait -PassThru
$results += [PSCustomObject]@{
    Action = 'Remove Steel Connections with corrected Revit root'
    ExitCode = $steel.ExitCode
    Success = $steel.ExitCode -in 0, 1605, 1614, 3010
}

$results | Export-Csv -LiteralPath "$logDir\recache-remove-results.csv" -NoTypeInformation -Encoding UTF8
$results | Format-Table -AutoSize
