#!/bin/bash

./private/bin/build_changes.sh

echo "Installing cypress"
yarn cypress install

echo "Starting WP-Cypress"
yarn wp-cypress start

#tail -100 ./node_modules/@bigbite/wp-cypress/debug.log

echo "Running WP-Cypress"
yarn cypress run
