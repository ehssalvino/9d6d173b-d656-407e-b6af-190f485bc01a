$ErrorActionPreference = 'Stop'

$logDir = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-uninstall-logs'
$customTransform = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\skip-broken-uninstall-action.mst'
$registeredTransform = 'C:\WINDOWS\Installer\{7346B4A0-2400-0510-0000-705C0D862004}\RVT.mst'
$transforms = ":pt-BR.mst;:all.mst;$registeredTransform;$customTransform"
$arguments = '/x {7346B4A0-2400-0510-0000-705C0D862004} ' +
    "TRANSFORMS=`"$transforms`" " +
    'ADSK_ODIS_SETUP=1 INSTALLDIR="D:\Program Files\Autodesk" ADSK_INSTALL_PATH="Revit 2024" ' +
    "/qn /norestart /L*v `"$logDir\Revit_2024_transform_remove.log`""

$process = Start-Process -FilePath "$env:SystemRoot\System32\msiexec.exe" `
    -ArgumentList $arguments -Wait -PassThru

[PSCustomObject]@{
    Action = 'Remove Revit 2024 with targeted uninstall transform'
    ExitCode = $process.ExitCode
    Success = $process.ExitCode -in 0, 1605, 1614, 3010
} | Export-Csv -LiteralPath "$logDir\transform-remove-result.csv" -NoTypeInformation -Encoding UTF8
