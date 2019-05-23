#!/usr/bin/env bash

USER="$1"
HTML="$2"
OGXT="$3"

curl -X POST http://localhost:8899/check.php \
  --data-urlencode user="${USER}" \
  --data-urlencode html="${HTML}" \
  --data-urlencode ogxt="${OGXT}"
