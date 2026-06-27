$ErrorActionPreference = 'Stop'

powercfg.exe /hibernate off
if ($LASTEXITCODE -ne 0) {
    throw "Falha ao desativar a hibernacao: $LASTEXITCODE"
}

$targets = @(
    'C:\Users\Eduardo\AppData\Local\CapCut\User Data\Download',
    'C:\Users\Eduardo\AppData\Local\CapCut\User Data\Cache'
)

foreach ($target in $targets) {
    if (-not (Test-Path -LiteralPath $target)) {
        continue
    }

    $resolved = [IO.Path]::GetFullPath($target)
    $allowedRoot = [IO.Path]::GetFullPath('C:\Users\Eduardo\AppData\Local\CapCut\User Data')
    if (-not $resolved.StartsWith($allowedRoot, [StringComparison]::OrdinalIgnoreCase)) {
        throw "Destino invalido: $resolved"
    }

    Remove-Item -LiteralPath $resolved -Recurse -Force
}
