@echo off
chcp 65001 >nul
title Central de Frequência e Alimentação Escolar - Feira Tecnológica
color 0A

echo.
echo  ╔════════════════════════════════════════════════════════╗
echo  ║   🏫 Central de Frequência e Alimentação Escolar 🏫   ║
echo  ║              Feira Tecnológica 2026                    ║
echo  ╚════════════════════════════════════════════════════════╝
echo.

echo  [1] Verificando se o MySQL esta rodando...
echo.

REM Testa a conexão com MySQL na porta 3306
powershell -Command "if (Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue) { exit 0 } else { exit 1 }" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo  ⚠️  MySQL NÃO está rodando na porta 3306!
    echo.
    echo  ➡️  Abra o XAMPP e clique START no Apache e MySQL
    echo  ➡️  Depois rode este script novamente
    echo.
    pause
    exit /b
)

echo  ✅ MySQL está rodando!
echo.

echo  [2] Verificando se o banco 'central_escolar' existe...
echo.

REM Verifica se o banco existe
mysql -u root -e "USE central_escolar;" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo  📦 Banco não encontrado. Importando banco_mysql.sql...
    mysql -u root < banco_mysql.sql
    if %ERRORLEVEL% equ 0 (
        echo  ✅ Banco 'central_escolar' criado e populado com sucesso!
    ) else (
        echo  ❌ Erro ao importar o banco. Importe manualmente pelo phpMyAdmin:
        echo     Abra http://localhost/phpmyadmin
        echo     Vá em Importar e selecione o arquivo banco_mysql.sql
    )
) else (
    echo  ✅ Banco 'central_escolar' já existe!
)

echo.
echo  [3] Iniciando servidor PHP na porta 8000...
echo.
echo  ╔════════════════════════════════════════════════════════╗
echo  ║                                                        ║
echo  ║   🌐 Acesse: http://localhost:8000                     ║
echo  ║                                                        ║
echo  ║   👩‍🏫 Login Inspetoras: vivi / val / cris (senha: 123456)  ║
echo  ║   🥗 Login Nutricionista: nutri (senha: 123456)        ║
echo  ║   👑 Login Admin: admin (senha: admin123)              ║
echo  ║                                                        ║
echo  ║   Pressione Ctrl+C para parar o servidor               ║
echo  ║                                                        ║
echo  ╚════════════════════════════════════════════════════════╝
echo.

php -S localhost:8000 -t public
