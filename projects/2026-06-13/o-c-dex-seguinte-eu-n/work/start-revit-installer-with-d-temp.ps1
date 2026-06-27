$ErrorActionPreference = 'Stop'

$tempPath = 'D:\AutodeskTemp'
$installer = 'D:\Usuarios\Eduardo\Downloads\Autodesk_Revit_2024_3_5_ML_setup_webinstall.exe'

New-Item -ItemType Directory -Path $tempPath -Force | Out-Null
$env:TEMP = $tempPath
$env:TMP = $tempPath

Start-Process -FilePath $installer
