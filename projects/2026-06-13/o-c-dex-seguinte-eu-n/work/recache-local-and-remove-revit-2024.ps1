$ErrorActionPreference = 'Stop'

$logDir = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-uninstall-logs'
$revitMsi = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\RVT.msi'
$msiexec = "$env:SystemRoot\System32\msiexec.exe"

$results = @()

$recacheArguments = "/fv `"$revitMsi`" ADSK_ODIS_SETUP=1 " +
    'INSTALLDIR="D:\Program Files\Autodesk" ADSK_INSTALL_PATH="Revit 2024" ' +
    "/qn /norestart /L*v `"$logDir\Revit_2024_local_recache.log`""
$recache = Start-Process -FilePath $msiexec -ArgumentList $recacheArguments -Wait -PassThru
$results += [PSCustomObject]@{
    Action = 'Recache Revit 2024 from local signed MSI'
    ExitCode = $recache.ExitCode
    Success = $recache.ExitCode -in 0, 3010
}

if ($recache.ExitCode -in 0, 3010) {
    $removeArguments = '/x {7346B4A0-2400-0510-0000-705C0D862004} ' +
        'ADSK_ODIS_SETUP=1 INSTALLDIR="D:\Program Files\Autodesk" ADSK_INSTALL_PATH="Revit 2024" ' +
        '/qn /norestart ' +
        "/L*v `"$logDir\Revit_2024_final_remove.log`""
    $remove = Start-Process -FilePath $msiexec -ArgumentList $removeArguments -Wait -PassThru
    $results += [PSCustomObject]@{
        Action = 'Remove Revit 2024 after local recache'
        ExitCode = $remove.ExitCode
        Success = $remove.ExitCode -in 0, 1605, 1614, 3010
    }
}

$results | Export-Csv -LiteralPath "$logDir\local-recache-results.csv" -NoTypeInformation -Encoding UTF8
$results | Format-Table -AutoSize
