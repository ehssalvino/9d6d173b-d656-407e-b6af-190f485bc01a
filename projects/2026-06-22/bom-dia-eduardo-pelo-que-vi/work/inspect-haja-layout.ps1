$ErrorActionPreference = 'Stop'

$chrome = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
$userData = Join-Path (Resolve-Path '.') 'work\chrome-layout-profile'
$port = 9223
$url = 'http://localhost/hajageracaosolar/'

New-Item -ItemType Directory -Force -Path $userData | Out-Null
$args = @(
  '--headless=new',
  '--disable-gpu',
  '--hide-scrollbars',
  "--remote-debugging-port=$port",
  "--user-data-dir=$userData",
  '--window-size=1366,900',
  $url
)
$proc = Start-Process -FilePath $chrome -ArgumentList $args -PassThru -WindowStyle Hidden
Start-Sleep -Seconds 3

try {
  $tabs = Invoke-RestMethod "http://localhost:$port/json"
  $tab = $tabs | Where-Object { $_.url -like '*hajageracaosolar*' } | Select-Object -First 1
  if (-not $tab) { throw 'Tab not found' }

  Add-Type -AssemblyName System.Net.WebSockets.Client
  $ws = [System.Net.WebSockets.ClientWebSocket]::new()
  $uri = [Uri]$tab.webSocketDebuggerUrl
  $ws.ConnectAsync($uri, [Threading.CancellationToken]::None).Wait()

  function Send-CDP($method, $params) {
    $script:msgId = 1 + [int]$script:msgId
    $payload = @{ id = $script:msgId; method = $method; params = $params } | ConvertTo-Json -Depth 20 -Compress
    $bytes = [Text.Encoding]::UTF8.GetBytes($payload)
    $seg = [ArraySegment[byte]]::new($bytes)
    $ws.SendAsync($seg, [System.Net.WebSockets.WebSocketMessageType]::Text, $true, [Threading.CancellationToken]::None).Wait()
    $buffer = New-Object byte[] 1048576
    while ($true) {
      $out = [ArraySegment[byte]]::new($buffer)
      $res = $ws.ReceiveAsync($out, [Threading.CancellationToken]::None).Result
      $text = [Text.Encoding]::UTF8.GetString($buffer, 0, $res.Count)
      if ($text -match '"id":\s*' + $script:msgId) {
        return ($text | ConvertFrom-Json)
      }
    }
  }

  Send-CDP 'Runtime.enable' @{} | Out-Null
  Start-Sleep -Seconds 1

  $js = @'
(() => {
  const box = el => {
    if (!el) return null;
    const r = el.getBoundingClientRect();
    const cs = getComputedStyle(el);
    return {
      text: (el.innerText || el.textContent || '').trim().replace(/\s+/g,' ').slice(0,90),
      x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height),
      display: cs.display, gridTemplateColumns: cs.gridTemplateColumns,
      padding: cs.padding, margin: cs.margin, fontSize: cs.fontSize,
      background: cs.backgroundColor, border: cs.border
    };
  };
  const cards = [...document.querySelectorAll('#haja-servicos-principais .haja-servico-card')].map(card => ({
    card: box(card),
    iconFrame: box(card.querySelector('.haja-icon-frame')),
    icon: box(card.querySelector('.haja-servico-icon')),
    title: box(card.querySelector('h3')),
    text: box(card.querySelector('p')),
    button: box(card.querySelector('.haja-servico-btn')),
    href: card.href
  }));
  const hero = document.querySelector('.haja-hero');
  const services = document.querySelector('#haja-servicos-principais');
  const grid = document.querySelector('#haja-servicos-principais .haja-servicos-grid');
  const nav = document.querySelector('header, .elementor-location-header, .elementor-nav-menu');
  return {
    viewport: { w: innerWidth, h: innerHeight, scrollH: document.documentElement.scrollHeight },
    title: document.title,
    nav: box(nav),
    hero: box(hero),
    services: box(services),
    grid: box(grid),
    cards,
    overlaps: cards.map((c, i) => {
      const b = c.button, card = c.card, t = c.text;
      return {
        index: i + 1,
        buttonInside: !!(b && card && b.x >= card.x && b.y >= card.y && b.x + b.w <= card.x + card.w && b.y + b.h <= card.y + card.h),
        buttonBelowText: !!(b && t && b.y >= t.y + t.h),
        equalCardHeight: card ? card.h : null
      };
    })
  };
})()
'@
  $resp = Send-CDP 'Runtime.evaluate' @{ expression = $js; returnByValue = $true; awaitPromise = $true }
  $resp.result.result.value | ConvertTo-Json -Depth 20
}
finally {
  if ($ws) { $ws.Dispose() }
  if ($proc -and -not $proc.HasExited) { Stop-Process -Id $proc.Id -Force }
}
