#define MyAppName "Gerador de Memorial Solar"
#define MyAppVersion "1.0.0"
#define MyAppExeName "GeradorMemorialSolar.exe"

[Setup]
AppId={{8147AE2A-78D8-49AE-A807-04D928638F69}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
DefaultDirName={autopf}\GeradorMemorialSolar
DefaultGroupName={#MyAppName}
OutputDir=installer
OutputBaseFilename=Instalador_Gerador_Memorial_Solar
Compression=lzma
SolidCompression=yes
WizardStyle=modern

[Files]
Source: "dist\GeradorMemorialSolar\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{autoprograms}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"
Name: "{autodesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "Abrir o Gerador"; Flags: nowait postinstall skipifsilent
