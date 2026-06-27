$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
$Venv = Join-Path $Root ".venv"

$PythonCommand = Get-Command py -ErrorAction SilentlyContinue
if ($PythonCommand) {
    $BasePython = @("py", "-3")
} else {
    $PythonCommand = Get-Command python -ErrorAction SilentlyContinue
    if (-not $PythonCommand) {
        throw "Python 3 não encontrado. Instale o Python 3.11 ou superior."
    }
    $BasePython = @("python")
}

if (-not (Test-Path $Venv)) {
    if ($BasePython.Count -eq 2) {
        & $BasePython[0] $BasePython[1] -m venv $Venv
    } else {
        & $BasePython[0] -m venv $Venv
    }
}

$Python = Join-Path $Venv "Scripts\python.exe"
$PyInstaller = Join-Path $Venv "Scripts\pyinstaller.exe"
& $Python -m pip install --upgrade pip
& $Python -m pip install -r (Join-Path $Root "requirements.txt")
& $PyInstaller --noconfirm --clean --windowed --name "GeradorMemorialSolar" `
    --add-data "$Root\assets;assets" `
    --paths $Root `
    (Join-Path $Root "app.py")

Write-Host "Executável criado em: $Root\dist\GeradorMemorialSolar\GeradorMemorialSolar.exe"
