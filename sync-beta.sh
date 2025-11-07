#!/bin/sh
GITREV=$(git rev-parse --short HEAD)
PWD=$(pwd)

rm -f "$PWD/app/Config/version"
echo $GITREV >"$PWD/app/Config/version"

case "$1" in
1) ;;
2) ;;
3) ;;
4) ;;
5) ;;
6) ;;
7) ;;
8) ;;
9) ;;
10) ;;
11) ;;
13) ;;
14) ;;
staging) ;;
*)
  echo $"Usage: $0 {1|2|3|4|5|6}"
  exit 2
  ;;
esac

echo "Syncing to: beta-$1.enviaflores.com"
rsync \
  --delete \
  --exclude='.git*' \
  --exclude=".project*" \
  --exclude="app/tmp/logs/*" \
  --exclude="app/tmp/persistent_logs/*" \
  --exclude="app/tmp/*.pid" \
  --exclude="app/tmp/spool/*" \
  --exclude="cloc*" \
  --exclude="sync*" \
  --exclude='.settings/' \
  --exclude='vendors/' \
  --exclude='tmp/' \
  --exclude='lib/' \
  --exclude='app/Vendor' \
  --exclude='app/webroot' \
  --exclude='app/View' \
  -raLve "ssh -i ~/.ssh/id_rsa " . apache@sync-dev-$1.enviaflores.com:/var/www/sites/ops-im.beta-6.enviaflores.com -O

host=beta-$1
branch=$(git rev-parse --abbrev-ref HEAD)
beta=$1
repo=$(basename `git rev-parse --show-toplevel`)
user=$(whoami)

#ssh apache@sync-dev-$1.enviaflores.com <<ENDSSH
#./slackPayload.sh $host $branch $beta $repo $user
#ENDSSH

exit $?
