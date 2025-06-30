#!/usr/bin/env bash

set -e
set -x

# Default branch
CURRENT_BRANCH="main"
TAG=""

# Check if a tag is provided as first argument
if [ "$#" -eq 1 ]; then
    TAG="$1"
    echo "Splitting for tag: $TAG"
else
    echo "Splitting for branch: $CURRENT_BRANCH"
fi

function split()
{
    local PREFIX=$1
    local REMOTE_NAME=$2
    local REMOTE_URL=$3

    echo "Splitting $PREFIX to $REMOTE_NAME"

    # Check if path exists
    if [ ! -d "$PREFIX" ]; then
        echo "Warning: $PREFIX does not exist, skipping $REMOTE_NAME"
        return
    fi

    # Create the split
    SHA1=$(./bin/splitsh-lite --prefix="$PREFIX")

    if [ -z "$SHA1" ]; then
        echo "Warning: No commits found for $PREFIX, skipping $REMOTE_NAME"
        return
    fi

    echo "Split SHA1 for $REMOTE_NAME: $SHA1"

    # Convert SSH URL to HTTPS with token for CI
    if [[ "$REMOTE_URL" == git@github.com:* ]]; then
        HTTPS_URL="https://${GITHUB_TOKEN}@github.com/${REMOTE_URL#git@github.com:}"
        REMOTE_URL="$HTTPS_URL"
    fi

    # Push either tag or branch
    if [ -n "$TAG" ]; then
        echo "Pushing tag $TAG to $REMOTE_NAME"
        git push "$REMOTE_URL" "$SHA1:refs/tags/$TAG"
    else
        echo "Pushing branch $CURRENT_BRANCH to $REMOTE_NAME"
        git push "$REMOTE_URL" "$SHA1:refs/heads/$CURRENT_BRANCH" -f
    fi
}

function remote()
{
    git remote add $1 $2 2>/dev/null || true
}

# Configure git for CI if needed - use commit author info
git config --global user.email "$(git log -1 --pretty=format:'%ae')" 2>/dev/null || true
git config --global user.name "$(git log -1 --pretty=format:'%an')" 2>/dev/null || true

# Only pull if we're doing branch operations (not tags)
if [ -z "$TAG" ]; then
    git pull origin $CURRENT_BRANCH
fi

# Define components and their remote URLs
declare -A COMPONENTS=(
    ["auth"]="git@github.com:kasiapps/auth.git"
    ["broadcasting"]="git@github.com:kasiapps/broadcasting.git"
    ["bus"]="git@github.com:kasiapps/bus.git"
    ["cache"]="git@github.com:kasiapps/cache.git"
    ["collections"]="git@github.com:kasiapps/collections.git"
    ["conditionable"]="git@github.com:kasiapps/conditionable.git"
    ["config"]="git@github.com:kasiapps/config.git"
    ["console"]="git@github.com:kasiapps/console.git"
    ["container"]="git@github.com:kasiapps/container.git"
    ["contracts"]="git@github.com:kasiapps/contracts.git"
    ["database"]="git@github.com:kasiapps/database.git"
    ["encryption"]="git@github.com:kasiapps/encryption.git"
    ["events"]="git@github.com:kasiapps/events.git"
    ["filesystem"]="git@github.com:kasiapps/filesystem.git"
    ["hashing"]="git@github.com:kasiapps/hashing.git"
    ["http"]="git@github.com:kasiapps/http.git"
    ["log"]="git@github.com:kasiapps/log.git"
    ["macroable"]="git@github.com:kasiapps/macroable.git"
    ["pagination"]="git@github.com:kasiapps/pagination.git"
    ["pipeline"]="git@github.com:kasiapps/pipeline.git"
    ["prompts"]="git@github.com:kasiapps/prompts.git"
    ["queue"]="git@github.com:kasiapps/queue.git"
    ["redis"]="git@github.com:kasiapps/redis.git"
    ["serializable-closure"]="git@github.com:kasiapps/serializable-closure.git"
    ["session"]="git@github.com:kasiapps/session.git"
    ["support"]="git@github.com:kasiapps/support.git"
    ["testing"]="git@github.com:kasiapps/testing.git"
    ["tinker"]="git@github.com:kasiapps/tinker.git"
    ["translation"]="git@github.com:kasiapps/translation.git"
    ["validation"]="git@github.com:kasiapps/validation.git"
    ["view"]="git@github.com:kasiapps/view.git"
)

# Define source paths for each component
declare -A PATHS=(
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

# Add remotes and split components
for COMPONENT in "${!COMPONENTS[@]}"
do
    REMOTE_URL="${COMPONENTS[$COMPONENT]}"
    COMPONENT_PATH="${PATHS[$COMPONENT]}"

    # Add remote (ignore errors if already exists)
    remote "$COMPONENT" "$REMOTE_URL"

    # Split the component
    split "$COMPONENT_PATH" "$COMPONENT" "$REMOTE_URL"
done

echo ""
if [ -n "$TAG" ]; then
    echo "Tag splitting completed for: $TAG"
else
    echo "Branch splitting completed for: $CURRENT_BRANCH"
fi
