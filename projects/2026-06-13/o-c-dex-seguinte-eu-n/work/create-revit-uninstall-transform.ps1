$ErrorActionPreference = 'Stop'

$sourceMsi = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\RVT.msi'
$modifiedMsi = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\RVT-uninstall-fixed.msi'
$transform = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\skip-broken-uninstall-action.mst'
$action = '_560A25DD.D5955B9C_A4DD_4C11_97BD_AB88FAFFCD9E'

Copy-Item -LiteralPath $sourceMsi -Destination $modifiedMsi -Force
Remove-Item -LiteralPath $transform -Force -ErrorAction SilentlyContinue

$installer = New-Object -ComObject WindowsInstaller.Installer
$database = $installer.GetType().InvokeMember(
    'OpenDatabase',
    'InvokeMethod',
    $null,
    $installer,
    @($modifiedMsi, 1)
)
$sql = "UPDATE ``InstallExecuteSequence`` SET ``Condition``='0' WHERE ``Action``='$action'"
$view = $database.GetType().InvokeMember('OpenView', 'InvokeMethod', $null, $database, @($sql))
$view.GetType().InvokeMember('Execute', 'InvokeMethod', $null, $view, $null)
$database.GetType().InvokeMember('Commit', 'InvokeMethod', $null, $database, $null)
$view = $null
$database = $null
$installer = $null
[GC]::Collect()
[GC]::WaitForPendingFinalizers()

Add-Type @'
using System;
using System.Runtime.InteropServices;

public static class NativeMsi {
    [DllImport("msi.dll", CharSet = CharSet.Unicode)]
    public static extern uint MsiOpenDatabase(
        string databasePath,
        IntPtr persist,
        out IntPtr databaseHandle);

    [DllImport("msi.dll", CharSet = CharSet.Unicode)]
    public static extern uint MsiDatabaseGenerateTransform(
        IntPtr database,
        IntPtr referenceDatabase,
        string transformFile,
        int reserved1,
        int reserved2);

    [DllImport("msi.dll", CharSet = CharSet.Unicode)]
    public static extern uint MsiCreateTransformSummaryInfo(
        IntPtr database,
        IntPtr referenceDatabase,
        string transformFile,
        int errorConditions,
        int validation);

    [DllImport("msi.dll")]
    public static extern uint MsiCloseHandle(IntPtr handle);
}
'@

$originalHandle = [IntPtr]::Zero
$changedHandle = [IntPtr]::Zero
try {
    $result = [NativeMsi]::MsiOpenDatabase($sourceMsi, [IntPtr]::Zero, [ref]$originalHandle)
    if ($result -ne 0) {
        throw "MsiOpenDatabase(original) failed with code $result"
    }

    $result = [NativeMsi]::MsiOpenDatabase($modifiedMsi, [IntPtr]::Zero, [ref]$changedHandle)
    if ($result -ne 0) {
        throw "MsiOpenDatabase(modified) failed with code $result"
    }

    $result = [NativeMsi]::MsiDatabaseGenerateTransform(
        $changedHandle,
        $originalHandle,
        $transform,
        0,
        0)
    if ($result -ne 0) {
        throw "MsiDatabaseGenerateTransform failed with code $result"
    }

    $result = [NativeMsi]::MsiCreateTransformSummaryInfo(
        $changedHandle,
        $originalHandle,
        $transform,
        0,
        0)
    if ($result -ne 0) {
        throw "MsiCreateTransformSummaryInfo failed with code $result"
    }
}
finally {
    if ($changedHandle -ne [IntPtr]::Zero) {
        [void][NativeMsi]::MsiCloseHandle($changedHandle)
    }
    if ($originalHandle -ne [IntPtr]::Zero) {
        [void][NativeMsi]::MsiCloseHandle($originalHandle)
    }
}

Get-Item -LiteralPath $transform | Select-Object FullName, Length, LastWriteTime
