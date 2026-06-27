$ErrorActionPreference = 'Stop'

$key = 'HKLM:\SYSTEM\CurrentControlSet\Control\Session Manager'
$value = (Get-ItemProperty -Path $key -Name PendingFileRenameOperations).PendingFileRenameOperations
$filtered = [System.Collections.Generic.List[string]]::new()

for ($i = 0; $i -lt $value.Count; $i += 2) {
    $source = $value[$i]
    $destination = if ($i + 1 -lt $value.Count) { $value[$i + 1] } else { '' }

    if ($source -notmatch '(?i)\\Program Files( \(x86\))?\\Diebold') {
        $filtered.Add($source)
        $filtered.Add($destination)
    }
}

if ($filtered.Count -eq 0) {
    Remove-ItemProperty -Path $key -Name PendingFileRenameOperations
} else {
    Set-ItemProperty -Path $key -Name PendingFileRenameOperations -Value $filtered.ToArray() -Type MultiString
}
