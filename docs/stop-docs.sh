#!/bin/bash
#
# Script to stop the Jekyll documentation server
# Usage: ./stop-docs.sh
#

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Stopping Jekyll documentation server...${NC}"

# Find and kill Jekyll processes
PIDS=$(pgrep -f "jekyll serve")

if [ -z "$PIDS" ]; then
    echo -e "${YELLOW}No Jekyll server is currently running.${NC}"
    exit 0
fi

# Kill the processes
echo "$PIDS" | xargs kill 2>/dev/null

# Wait a moment and check if they're gone
sleep 1

REMAINING_PIDS=$(pgrep -f "jekyll serve")
if [ -n "$REMAINING_PIDS" ]; then
    echo -e "${RED}Some processes didn't stop. Forcing shutdown...${NC}"
    echo "$REMAINING_PIDS" | xargs kill -9 2>/dev/null
    sleep 1
fi

# Final check
if pgrep -f "jekyll serve" > /dev/null 2>&1; then
    echo -e "${RED}Failed to stop Jekyll server.${NC}"
    exit 1
fi

echo -e "${GREEN}Jekyll server stopped successfully.${NC}"

