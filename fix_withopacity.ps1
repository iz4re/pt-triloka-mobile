$files = Get-ChildItem -Path "lib\screens\*.dart"
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $newContent = $content -replace '\.withOpacity\(([\d.]+)\)', '.withValues(alpha: $1)'
    Set-Content -Path $file.FullName -Value $newContent -NoNewline
    Write-Host "Processed: $($file.Name)"
}
Write-Host "Done!"
