@echo off
echo Generating SSL certificate for localhost...
cd /d "C:\xampp\apache\bin"

echo.
echo Creating new SSL certificate for localhost...
makecert -r -pe -n "CN=localhost" -b 01/01/2025 -e 01/01/2026 -eku 1.3.6.1.5.5.7.3.1 -ss my -sr localMachine -sky exchange -sp "Microsoft RSA SChannel Cryptographic Provider" -sy 12 localhost.cer

echo.
echo Converting certificate to PEM format...
openssl x509 -outform PEM -in localhost.cer -out ..\conf\ssl.crt\server.crt

echo.
echo Generating private key...
openssl genrsa -out ..\conf\ssl.key\server.key 2048

echo.
echo SSL certificate generated successfully!
echo Certificate: C:\xampp\apache\conf\ssl.crt\server.crt
echo Private Key: C:\xampp\apache\conf\ssl.key\server.key
echo.
echo You can now start Apache with SSL support.
pause
