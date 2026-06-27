$ErrorActionPreference = 'Stop'

$productCode = '{7346B4A0-2400-0510-0000-705C0D862004}'
$productRegistry = 'HKLM:\SOFTWARE\Classes\Installer\Products\0A4B643700420150000007C5D0680240'
$productRegistryNative = 'HKLM\SOFTWARE\Classes\Installer\Products\0A4B643700420150000007C5D0680240'
$productTransformDir = "C:\WINDOWS\Installer\$productCode"
$sourceTransform = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\skip-broken-uninstall-action.mst'
$registeredTransform = Join-Path $productTransformDir 'CodexSkipBrokenUninstallAction.mst'
$backup = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-backup\Installer-Product-Revit2024.reg'
$log = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-uninstall-logs\Revit_2024_registered_transform_remove.log'
$resultFile = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-uninstall-logs\registered-transform-result.csv'

& reg.exe export $productRegistryNative $backup /y | Out-Null
if ($LASTEXITCODE -ne 0) {
    throw "Falha ao exportar backup da chave do Windows Installer: $LASTEXITCODE"
}

$originalTransforms = (Get-ItemProperty -LiteralPath $productRegistry -Name Transforms).Transforms
Copy-Item -LiteralPath $sourceTransform -Destination $registeredTransform -Force
$temporaryTransforms = "$originalTransforms;$registeredTransform"
Set-ItemProperty -LiteralPath $productRegistry -Name Transforms -Value $temporaryTransforms

$arguments = "/x $productCode ADSK_ODIS_SETUP=1 " +
    'INSTALLDIR="D:\Program Files\Autodesk" ADSK_INSTALL_PATH="Revit 2024" ' +
    "/qn /norestart /L*v `"$log`""
$process = Start-Process -FilePath "$env:SystemRoot\System32\msiexec.exe" `
    -ArgumentList $arguments -Wait -PassThru
$success = $process.ExitCode -in 0, 1605, 1614, 3010

if (-not $success -and (Test-Path -LiteralPath $productRegistry)) {
    Set-ItemProperty -LiteralPath $productRegistry -Name Transforms -Value $originalTransforms
}
if (Test-Path -LiteralPath $registeredTransform) {
    Remove-Item -LiteralPath $registeredTransform -Force
}

[PSCustomObject]@{
    Action = 'Remove Revit 2024 with temporarily registered transform'
    ExitCode = $process.ExitCode
    Success = $success
    RegistryRestoredAfterFailure = -not $success
} | Export-Csv -LiteralPath $resultFile -NoTypeInformation -Encoding UTF8
