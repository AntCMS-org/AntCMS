#!/bin/bash

# NPM vars
nodeModulesPath="node_modules"
packageLock="package-lock.json"
nodeBuildTimestamp="$nodeModulesPath/package.stamp"

# Composer vars
composerVendorPath="src/Vendor"
composerLock="composer.lock"
customVendorTimestamp="$composerVendorPath/composer.stamp"

# Other vars
npmBuildNeeded=0
now=$(date +"%Y-%m-%d_%H:%M:%S")

writeInfo() {
    echo -e "\033[1;33mINFO:\033[0m $1"
}

writeInfo "Automatically installing / updating dependencies and building the front-end. Please wait."
sleep 2

# If the NPM packages aren't installed or are outdated, install the locked versions
if [ ! -d "$nodeModulesPath" ] || [ ! -f "$nodeBuildTimestamp" ] || [ "$packageLock" -nt "$nodeBuildTimestamp" ]; then
    npm ci --include=dev
    npmBuildNeeded=1
elif [ ! -d "src/Themes/Default/Assets/build" ]; then
    npmBuildNeeded=1
fi

# If the composer packages aren't installed or are outdated, install the locked versions
if [ ! -d "$composerVendorPath" ] || [ ! -f "$customVendorTimestamp" ] || [ "$composerLock" -nt "$customVendorTimestamp" ]; then
    composer install
    echo "$now" > "$customVendorTimestamp"
fi

# If we changed NPM packages, then re-run the build automatically
if [ $npmBuildNeeded -eq 1 ]; then
    npm run build
    echo "$now" > "$nodeBuildTimestamp"
fi

writeInfo "Done!"
