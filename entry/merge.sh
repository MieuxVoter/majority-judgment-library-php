#!/bin/env sh

# Usage:
# 1. Download phpfilemerger.php
# 2. Run this from project root

php phpfilemerger.php merge \
  --output majorityjudgment.php \
  --exclude-entry \
  entry/merge.php
