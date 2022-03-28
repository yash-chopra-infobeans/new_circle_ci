#!/bin/bash

# Run build sequence
. ./private/bin/build_changes.sh

echo "Removing .gitignore for build & deploy."
rm .gitignore

# if it's a tagged build, then the origin branch is master.
# TRAVIS_BRANCH used to be empty for tagged builds, but it
# is now set to equal TRAVIS_TAG. therefore, we will need
# to override it at the deployment stage, so that tagged
# builds get committed to "master-built", rather than to
# "[tag-name]-built"
# if [ "$TRAVIS_TAG" ]; then
# 	export TRAVIS_BRANCH="master";
# fi

if curl -s "https://raw.githubusercontent.com/Automattic/vip-go-build/master/deploy-travis-prepare.sh" | bash; then
  echo "Deploy preparation complete";
else
  echo "Failed to prepare deploy";
  exit 1;
fi

if curl -s "https://raw.githubusercontent.com/Automattic/vip-go-build/master/deploy.sh" | bash; then
  echo "Deploy complete";
else
  echo "Failed to deploy";
  exit 1;
fi
