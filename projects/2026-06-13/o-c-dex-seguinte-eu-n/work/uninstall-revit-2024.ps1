$ErrorActionPreference = 'Continue'

$logDir = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\o-c-dex-seguinte-eu-n\work\revit-uninstall-logs'
New-Item -ItemType Directory -Path $logDir -Force | Out-Null

$products = @(
    @{ Name = 'Batch Print for Autodesk Revit 2024'; Guid = '{82AF00E4-2401-0010-0000-FCE0F8702400}' },
    @{ Name = 'Worksharing Monitor for Autodesk Revit 2024'; Guid = '{5063E738-2401-0010-0000-7B7B9AB02400}' },
    @{ Name = 'eTransmit for Autodesk Revit 2024'; Guid = '{4477F08B-2401-0010-0000-9A09D8342400}' },
    @{ Name = 'Autodesk Cloud Models for Revit 2024'; Guid = '{AA384BE4-2401-0010-0000-97E7D7D02400}' },
    @{ Name = 'Autodesk Steel Connections Core Content for Revit 2024'; Guid = '{C430585C-2024-4514-A253-D0C70D33ADD5}' },
    @{ Name = 'FormIt Converter for Revit 2024'; Guid = '{A4D93D5A-1942-4AB1-828E-C58A8DDB4377}' },
    @{ Name = 'Generative Design For Revit'; Guid = '{FD0F3B78-9A88-4A56-AFE4-28B5D8F4A30A}' },
    @{ Name = 'REX Revit'; Guid = '{11AFDE30-6E36-412B-8220-A78311625B91}' },
    @{ Name = 'Revit 2024'; Guid = '{7346B4A0-2400-0510-0000-705C0D862004}' },
    @{ Name = 'Autodesk Revit Content Core-RVT 2024'; Guid = '{CC7D1ED0-2024-0410-0000-1CC925969102}' },
    @{ Name = 'Autodesk Revit Content Core 2024'; Guid = '{AA384BE4-2024-0410-0000-9241AD002DA5}' },
    @{ Name = 'Autodesk Revit Product Feedback 2024'; Guid = '{D0AA00F5-2024-4900-BB7C-21929DC2B241}' },
    @{ Name = 'Autodesk Revit Unit Schemas 2024'; Guid = '{CDCC6F31-2024-4902-8E9B-D562B70697B6}' }
)

$results = foreach ($product in $products) {
    $safeName = $product.Name -replace '[^A-Za-z0-9.-]', '_'
    $logPath = Join-Path $logDir "$safeName.log"
    $arguments = @(
        '/x',
        $product.Guid,
        '/qn',
        '/norestart',
        '/L*v',
        $logPath
    )

    $process = Start-Process -FilePath "$env:SystemRoot\System32\msiexec.exe" `
        -ArgumentList $arguments -Wait -PassThru

    [PSCustomObject]@{
        Product = $product.Name
        Guid = $product.Guid
        ExitCode = $process.ExitCode
        Success = $process.ExitCode -in 0, 1605, 1614, 3010
        Log = $logPath
    }
}

$resultPath = Join-Path $logDir 'uninstall-results.csv'
$results | Export-Csv -LiteralPath $resultPath -NoTypeInformation -Encoding UTF8
$results | Format-Table -AutoSize

if ($results.ExitCode -contains 3010) {
    Write-Host 'Uma reinicializacao sera necessaria.'
}
