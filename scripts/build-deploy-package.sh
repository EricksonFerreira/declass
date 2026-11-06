#!/usr/bin/env bash
#
# Monta o pacote de deploy de producao (declass.ericksondev.online).
#
# A Hostinger nao tem SSH nem composer remoto, entao o pacote precisa conter
# tudo: codigo + vendor/ de producao + .env. Este script replica o processo
# manual documentado em AGENTS.md.
#
# Uso:
#   bash scripts/build-deploy-package.sh
#
# Saida: declass-deploy.tar.gz na raiz do projeto (~26MB).
# Depois e so chamar hosting_deploy com esse arquivo (path + type=static).

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORK="${TMPDIR:-/tmp}/declass-deploy-build"
VENDOR_SRC="$WORK/vendor-src"
PKG="$WORK/pkg"
OUT="$ROOT/declass-deploy.tar.gz"

cd "$ROOT"

echo "==> Limpando diretorio de trabalho"
rm -rf "$WORK"
mkdir -p "$VENDOR_SRC" "$PKG"

echo "==> composer install --no-dev (vendor de producao)"
cp composer.json composer.lock "$VENDOR_SRC/"
(
    cd "$VENDOR_SRC"
    composer install --no-dev --optimize-autoloader --no-scripts --no-interaction
    # Seeders usam fake() — faker e require-dev, precisa ir junto
    composer require fakerphp/faker:^1.23 --no-scripts --no-interaction --no-update
    composer update fakerphp/faker --no-dev --no-scripts --no-interaction
)

echo "==> Limpando arquivos desnecessarios do adminlte (~95MB)"
ADMINLTE="$VENDOR_SRC/vendor/almasaeed2010/adminlte"
rm -rf "$ADMINLTE/docs" "$ADMINLTE/plugins" "$ADMINLTE/build" "$ADMINLTE/src" \
       "$ADMINLTE/pages" "$ADMINLTE"/*.html "$ADMINLTE"/Dockerfile \
       "$ADMINLTE"/docker-compose.yml "$ADMINLTE"/*.md \
       "$ADMINLTE"/package.json "$ADMINLTE"/package-lock.json

echo "==> Copiando codigo do projeto (sem .git, node_modules, vendor, storage runtime)"
robocopy "$(cygpath -w "$ROOT")" "$(cygpath -w "$PKG")" //E \
    //XD "$(cygpath -w "$ROOT")\\.git" \
        "$(cygpath -w "$ROOT")\\node_modules" \
        "$(cygpath -w "$ROOT")\\vendor" \
        "$(cygpath -w "$ROOT")\\storage\\app" \
        "$(cygpath -w "$ROOT")\\storage\\logs" \
        "$(cygpath -w "$ROOT")\\storage\\framework\\cache" \
        "$(cygpath -w "$ROOT")\\storage\\framework\\sessions" \
        "$(cygpath -w "$ROOT")\\storage\\framework\\views" \
        "$(cygpath -w "$ROOT")\\storage\\framework\\testing" \
        "$(cygpath -w "$ROOT")\\bootstrap\\cache" \
    //XF .phpunit.result.cache nul declass-deploy.tar.gz \
    //NFL //NDL //NJH //NJS //NP || true

echo "==> Esqueleto do storage + vendor + .env"
mkdir -p "$PKG/storage/app/public" \
         "$PKG/storage/framework/cache/data" \
         "$PKG/storage/framework/sessions" \
         "$PKG/storage/framework/views" \
         "$PKG/storage/framework/testing" \
         "$PKG/storage/logs" \
         "$PKG/bootstrap/cache"
rm -rf "$PKG/.ai" "$PKG/.vscode"
robocopy "$(cygpath -w "$VENDOR_SRC")\\vendor" "$(cygpath -w "$PKG")\\vendor" //E //NFL //NDL //NJH //NJS //NP || true
cp .env.production "$PKG/.env"

echo "==> Compactando"
tar -czf "$OUT" -C "$PKG" .
ls -lh "$OUT"
echo ""
echo "Pronto. Deploy: hosting_deploy domain=declass.ericksondev.online path=$OUT type=static"
