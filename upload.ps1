# FTP Upload Script
$FtpServer = "102.209.117.98"
$FtpUser = "relearnc"
$FtpPass = "!RS771]k7fhCMq"
$LocalFile = "c:\Users\muriit_ter\Downloads\relearn.16.01.2026\relearn\app\Models\Product.php"
$RemotePath = "domains/relearn.co.ke/relearn/app/Models/Product.php"

# Create FTP URI
$FtpUri = "ftp://$FtpServer/$RemotePath"

# Create WebClient
$WebClient = New-Object System.Net.WebClient
$WebClient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

try {
    Write-Host "Uploading $LocalFile to $FtpUri..."
    $WebClient.UploadFile($FtpUri, $LocalFile)
    Write-Host "Upload successful!" -ForegroundColor Green
    exit 0
}
catch {
    Write-Host "Upload failed: $_" -ForegroundColor Red
    exit 1
}
finally {
    $WebClient.Dispose()
}
