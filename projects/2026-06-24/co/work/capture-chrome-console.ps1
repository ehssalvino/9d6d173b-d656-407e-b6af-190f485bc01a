param(
    [string]$ChromePath = 'C:\Program Files\Google\Chrome\Application\chrome.exe',
    [string]$Url,
    [int]$Port = 9223,
    [int]$Seconds = 35
)

$profile = Join-Path $PSScriptRoot 'chrome-cdp-profile'
New-Item -ItemType Directory -Force -Path $profile | Out-Null

$args = @(
    '--headless=new',
    '--disable-gpu',
    '--disable-extensions',
    '--no-first-run',
    "--user-data-dir=$profile",
    "--remote-debugging-port=$Port",
    'about:blank'
)

$proc = Start-Process -FilePath $ChromePath -ArgumentList $args -PassThru -WindowStyle Hidden
Start-Sleep -Seconds 2

try {
    $tabs = Invoke-RestMethod -Uri "http://127.0.0.1:$Port/json" -TimeoutSec 5
    $wsUrl = $tabs[0].webSocketDebuggerUrl
    $ws = [System.Net.WebSockets.ClientWebSocket]::new()
    $ws.ConnectAsync([Uri]$wsUrl, [Threading.CancellationToken]::None).Wait()

    $id = 0
    function Send-Cdp($method, $params = $null) {
        $script:id++
        $obj = @{ id = $script:id; method = $method }
        if ($null -ne $params) { $obj.params = $params }
        $json = ($obj | ConvertTo-Json -Depth 20 -Compress)
        $bytes = [Text.Encoding]::UTF8.GetBytes($json)
        $seg = [ArraySegment[byte]]::new($bytes)
        $script:ws.SendAsync($seg, [System.Net.WebSockets.WebSocketMessageType]::Text, $true, [Threading.CancellationToken]::None).Wait()
    }

    Send-Cdp 'Runtime.enable'
    Send-Cdp 'Log.enable'
    Send-Cdp 'Page.enable'
    Send-Cdp 'Page.navigate' @{ url = $Url }

    $deadline = (Get-Date).AddSeconds($Seconds)
    $buffer = New-Object byte[] 1048576
    $events = New-Object System.Collections.Generic.List[string]

    while ((Get-Date) -lt $deadline) {
        $seg = [ArraySegment[byte]]::new($buffer)
        $task = $ws.ReceiveAsync($seg, [Threading.CancellationToken]::None)
        if (-not $task.Wait(1000)) { continue }
        $result = $task.Result
        if ($result.Count -le 0) { continue }
        $text = [Text.Encoding]::UTF8.GetString($buffer, 0, $result.Count)
        try { $msg = $text | ConvertFrom-Json } catch { continue }
        switch ($msg.method) {
            'Runtime.consoleAPICalled' {
                $vals = @()
                foreach ($arg in $msg.params.args) {
                    if ($arg.value) { $vals += [string]$arg.value }
                    elseif ($arg.description) { $vals += [string]$arg.description }
                }
                $events.Add(('CONSOLE {0}: {1}' -f $msg.params.type, ($vals -join ' | ')))
            }
            'Runtime.exceptionThrown' {
                $events.Add(('EXCEPTION: {0}' -f $msg.params.exceptionDetails.text))
                if ($msg.params.exceptionDetails.exception.description) {
                    $events.Add(('DETAIL: {0}' -f $msg.params.exceptionDetails.exception.description))
                }
            }
            'Log.entryAdded' {
                $events.Add(('LOG {0}: {1}' -f $msg.params.entry.level, $msg.params.entry.text))
            }
        }
    }

    $events | Select-Object -First 200
}
finally {
    if ($ws) { $ws.Dispose() }
    if ($proc -and -not $proc.HasExited) { Stop-Process -Id $proc.Id -Force }
}
