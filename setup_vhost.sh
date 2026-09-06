#!/bin/bash
# Configuration de l'hôte virtuel Apache pour Olive Service
set -e

VHOST_FILE="/etc/apache2/sites-available/oliveservice.conf"

echo "=== Configuration du VirtualHost oliveservice.local ==="

cat << 'EOF' > "$VHOST_FILE"
<VirtualHost *:80>
    ServerName oliveservice.local
    DocumentRoot /var/www/html/oliveservice

    <Directory /var/www/html/oliveservice>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/oliveservice_error.log
    CustomLog ${APACHE_LOG_DIR}/oliveservice_access.log combined
</VirtualHost>
EOF

echo "✓ Fichier $VHOST_FILE mis à jour."

# Vérification et rechargement
apache2ctl configtest
systemctl reload apache2

echo "✓ Apache rechargé avec succès !"
echo "✓ Votre site est disponible sur http://oliveservice.local/"
