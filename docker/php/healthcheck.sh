#!/bin/sh

if nc -z localhost 9000 2>/dev/null; then
    exit 0
fi

if ps aux | grep -q "[p]hp-fpm: master process" 2>/dev/null; then
    exit 0
fi

exit 1

