#!/bin/sh
set -eu

# Invoke with the service's existing server command as arguments.
if [ "$#" -eq 0 ]; then
    echo "Usage: sh scripts/railway-media-start.sh <existing server command> [arguments...]" >&2
    exit 1
fi

# Run in the application container, after Railway mounts the public-media volume.
php artisan content:restore-media --no-interaction
php artisan storage:link --no-interaction
exec "$@"
