$ErrorActionPreference = "Stop"
$Project = Split-Path -Parent $MyInvocation.MyCommand.Path
$CompilerCandidates = @(
    (Join-Path $env:LOCALAPPDATA "Programs\Inno Setup 6\ISCC.exe"),
    "C:\Program Files (x86)\Inno Setup 6\ISCC.exe",
    "C:\Program Files\Inno Setup 6\ISCC.exe"
)

$Compiler = $CompilerCandidates | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1
if (-not $Compiler) {
    throw "Inno Setup 6 não encontrado. Instale com: winget install JRSoftware.InnoSetup"
}

& (Join-Path $Project ".venv\Scripts\python.exe") (Join-Path $Project "create_icon.py")
& (Join-Path $Project "build_exe.ps1")
& $Compiler (Join-Path $Project "installer.iss")

Write-Host ""
Write-Host "Instalador criado em: $Project\installer-dist\Instalador_DelfosPropostas_0.3.0.exe"
