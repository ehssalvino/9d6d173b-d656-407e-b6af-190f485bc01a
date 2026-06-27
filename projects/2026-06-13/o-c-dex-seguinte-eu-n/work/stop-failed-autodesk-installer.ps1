$ErrorActionPreference = 'SilentlyContinue'

$processNames = @(
    'Setup',
    'Installer',
    'install_manager',
    'ProcessManager',
    'DownloadManager',
    'AdskAccessUIHost',
    'Autodesk Access UI Host',
    'ui-launcher'
)

Get-Process |
    Where-Object { $processNames -contains $_.ProcessName } |
    Stop-Process -Force
