$ErrorActionPreference = 'Stop'

$log = 'C:\Users\Eduardo\Documents\Codex\2026-06-13\codas-o-seguinte-eu-precisei-mudar\work\move-data-to-d.log'
Start-Transcript -Path $log -Force

$items = @(
    @{
        Source = 'C:\Users\Eduardo\AppData\Local\Navegador C6 Bank'
        Target = 'D:\Dados-Eduardo\AppData\Local\Navegador C6 Bank'
    }
)

Get-Process |
    Where-Object { $_.ProcessName -match 'C6' } |
    Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

foreach ($item in $items) {
    $source = $item.Source
    $target = $item.Target
    $holding = "$source.codex-moving"

    if (Test-Path -LiteralPath $holding) {
        throw "Pasta temporaria ja existe: $holding"
    }

    New-Item -ItemType Directory -Path $target -Force | Out-Null
    & robocopy.exe $source $target /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /XJ /NP
    $robocopyExit = $LASTEXITCODE
    if ($robocopyExit -ge 8) {
        throw "Robocopy falhou em $source com codigo $robocopyExit"
    }

    $sourceFiles = Get-ChildItem -LiteralPath $source -Force -File -Recurse
    $targetFiles = Get-ChildItem -LiteralPath $target -Force -File -Recurse
    $sourceBytes = ($sourceFiles | Measure-Object Length -Sum).Sum
    $targetBytes = ($targetFiles | Measure-Object Length -Sum).Sum

    if ($sourceFiles.Count -ne $targetFiles.Count -or $sourceBytes -ne $targetBytes) {
        throw "Validacao falhou em $source"
    }

    Rename-Item -LiteralPath $source -NewName ([IO.Path]::GetFileName($holding))
    New-Item -ItemType Junction -Path $source -Target $target | Out-Null

    $junctionFiles = Get-ChildItem -LiteralPath $source -Force -File -Recurse
    $junctionBytes = ($junctionFiles | Measure-Object Length -Sum).Sum
    if ($junctionFiles.Count -ne $targetFiles.Count -or $junctionBytes -ne $targetBytes) {
        throw "Validacao da juncao falhou em $source"
    }

    Remove-Item -LiteralPath $holding -Recurse -Force
    Write-Output "TRANSFERIDO: $source -> $target | $([math]::Round($targetBytes / 1GB, 3)) GB | $($targetFiles.Count) arquivos"
}

Stop-Transcript
