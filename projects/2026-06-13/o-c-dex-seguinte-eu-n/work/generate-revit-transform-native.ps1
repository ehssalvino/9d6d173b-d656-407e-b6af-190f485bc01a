$ErrorActionPreference = 'Stop'

$sourceMsi = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\RVT.msi'
$modifiedMsi = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\RVT-uninstall-fixed.msi'
$transform = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-source\skip-broken-uninstall-action.mst'

Remove-Item -LiteralPath $transform -Force -ErrorAction SilentlyContinue

Add-Type @'
using System;
using System.Runtime.InteropServices;

public static class NativeMsiTransform {
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
    $result = [NativeMsiTransform]::MsiOpenDatabase($sourceMsi, [IntPtr]::Zero, [ref]$originalHandle)
    if ($result -ne 0) {
        throw "MsiOpenDatabase(original) failed with code $result"
    }

    $result = [NativeMsiTransform]::MsiOpenDatabase($modifiedMsi, [IntPtr]::Zero, [ref]$changedHandle)
    if ($result -ne 0) {
        throw "MsiOpenDatabase(modified) failed with code $result"
    }

    $result = [NativeMsiTransform]::MsiDatabaseGenerateTransform(
        $changedHandle,
        $originalHandle,
        $transform,
        0,
        0)
    if ($result -ne 0) {
        throw "MsiDatabaseGenerateTransform failed with code $result"
    }

    $result = [NativeMsiTransform]::MsiCreateTransformSummaryInfo(
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
        [void][NativeMsiTransform]::MsiCloseHandle($changedHandle)
    }
    if ($originalHandle -ne [IntPtr]::Zero) {
        [void][NativeMsiTransform]::MsiCloseHandle($originalHandle)
    }
}

Get-Item -LiteralPath $transform | Select-Object FullName, Length, LastWriteTime
