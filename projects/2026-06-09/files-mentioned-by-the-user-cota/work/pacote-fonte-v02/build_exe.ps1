$ErrorActionPreference = "Stop"
$Project = Split-Path -Parent $MyInvocation.MyCommand.Path
$Venv = Join-Path $Project ".venv"

if (-not (Test-Path $Venv)) {
    py -3 -m venv $Venv
}

$Python = Join-Path $Venv "Scripts\python.exe"
& $Python -m pip install --upgrade pip
& $Python -m pip install -r (Join-Path $Project "requirements.txt")

Push-Location $Project
try {
    & $Python -m PyInstaller `
        --noconfirm `
        --clean `
        --onefile `
        --windowed `
        --name "DelfosPropostas" `
        --add-data "assets;assets" `
        "app.py"
} finally {
    Pop-Location
}

Write-Host ""
Write-Host "Executável criado em: $Project\dist\DelfosPropostas.exe"
