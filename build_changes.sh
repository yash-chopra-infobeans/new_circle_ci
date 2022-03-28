#!/bin/bash

BASE_DIR="$PWD"

function setup_node() {
  # Node.js/NVM
  ## Install Node.js/NVM
  if curl -s -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.34.0/install.sh | bash > /dev/null 2>&1; then
    echo "Installed nvm.";
  else
    echo "Failed to install nvm.";
    exit 1;
  fi

  ## Load NVM
  export NVM_DIR="$HOME/.nvm";
  if [ -s "$NVM_DIR/nvm.sh" ]; then
    # shellcheck source=/dev/null
    . "$NVM_DIR/nvm.sh";
    echo "Loaded nvm.";
  else
    echo "Failed to load nvm.";
    exit 1;
  fi

  ## ensure correct node version is installed
  if nvm install 12.18.2 > /dev/null 2>&1; then
    nvm use 12.18.2 > /dev/null;
    printf "Using node %s.\n" "$(node --version)";
  else
    echo "Failed to install correct node version.";
    exit 1;
  fi
}

function setup_yarn() {
  # install yarn
  if curl -s -o- -L https://yarnpkg.com/install.sh | bash -s -- --version 1.13.0 > /dev/null 2>&1; then
    export PATH="$HOME/.yarn/bin:$PATH";
  else
    echo "Failed to install Yarn.";
    exit 1;
  fi

  # Install dependencies
  if yarn > /dev/null; then
    echo "Installed JS dependencies";
  else
    echo "Failed to install JS dependencies";
    exit 1;
  fi
}

function setup_php() {
  # Install required PHPCS lint rulesets
  # if  composer g require --dev automattic/vipwpcs dealerdirect/phpcodesniffer-composer-installer > /dev/null 2>&1; then
  #   echo "Installed PHPCS";
  # else
  #   echo "Failed to install PHPCS";
  #   exit 1;
  # fi

  # Disable XDebug
  if command -v phpenv > /dev/null; then
    phpenv config-rm xdebug.ini > /dev/null 2>&1;
  fi
}

if ! setup_node; then
  exit 1;
fi

if ! setup_yarn; then
  exit 1;
fi

if ! setup_php; then
  exit 1;
fi

#cd $TRAVIS_BUILD_DIR

# echo "PHPCS: Checking for errors"

# if ! ~/.config/composer/vendor/bin/phpcs --runtime-set ignore_warnings_on_exit true .; then
#   exit 1;
# fi

echo "travis build dir = $TRAVIS_BUILD_DIR";

echo "Yarn: Building Production Assets"

yarn build:prod --all-projects
