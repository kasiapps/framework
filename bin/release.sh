#!/usr/bin/env bash

set -e

# Make sure the release tag is provided.
if (( "$#" != 1 ))
then
    echo "Tag has to be provided."
    exit 1
fi

VERSION=$1

# Always prepend with "v" if not already present
if [[ $VERSION != v* ]]
then
    VERSION="v$VERSION"
fi

echo "Processing release for tag: $VERSION"

# Configure git for CI environment - use commit author info
git config --global user.email "$(git log -1 --pretty=format:'%ae')"
git config --global user.name "$(git log -1 --pretty=format:'%an')"

# Split and tag components
declare -A COMPONENTS=(
    ["auth"]="src/Auth"
    ["broadcasting"]="src/Broadcasting"
    ["bus"]="src/Bus"
    ["cache"]="src/Cache"
    ["collections"]="src/Collections"
    ["conditionable"]="src/Conditionable"
    ["config"]="src/Config"
    ["console"]="src/Console"
    ["container"]="src/Container"
    ["contracts"]="src/Contracts"
    ["database"]="src/Database"
    ["encryption"]="src/Encryption"
    ["events"]="src/Events"
    ["filesystem"]="src/Filesystem"
    ["hashing"]="src/Hashing"
    ["http"]="src/Http"
    ["log"]="src/Log"
    ["macroable"]="src/Macroable"
    ["pagination"]="src/Pagination"
    ["pipeline"]="src/Pipeline"
    ["prompts"]="src/Prompts"
    ["queue"]="src/Queue"
    ["redis"]="src/Redis"
    ["serializable-closure"]="src/SerializableClosure"
    ["session"]="src/Session"
    ["support"]="src/Support"
    ["testing"]="src/Testing"
    ["tinker"]="src/Tinker"
    ["translation"]="src/Translation"
    ["validation"]="src/Validation"
    ["view"]="src/View"
)

for REMOTE in "${!COMPONENTS[@]}"
do
    COMPONENT_PATH="${COMPONENTS[$REMOTE]}"

    echo ""
    echo "Splitting and tagging $REMOTE from $COMPONENT_PATH"

    # Check if component path exists
    if [ ! -d "$COMPONENT_PATH" ]; then
        echo "Warning: $COMPONENT_PATH does not exist, skipping $REMOTE"
        continue
    fi

    # Create the split using splitsh-lite
    SHA1=$(./bin/splitsh-lite --prefix="$COMPONENT_PATH")

    if [ -z "$SHA1" ]; then
        echo "Warning: No commits found for $COMPONENT_PATH, skipping $REMOTE"
        continue
    fi

    echo "Split SHA1 for $REMOTE: $SHA1"

    # Push the tag to the component repository
    REMOTE_URL="https://${GITHUB_TOKEN}@github.com/$REMOTE.git"
    git push "$REMOTE_URL" "$SHA1:refs/tags/$VERSION"

    echo "Successfully tagged $REMOTE with $VERSION"
done

echo ""
echo "Release $VERSION completed successfully!"
