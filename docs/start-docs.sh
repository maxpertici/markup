#!/bin/bash
#
# Script to start the Jekyll documentation server
# Usage: ./start-docs.sh
#

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}Starting Jekyll documentation server...${NC}"

# Change to script directory
cd "$(dirname "$0")" || exit 1

# Setup Ruby environment (chruby)
if [ -f /opt/homebrew/opt/chruby/share/chruby/chruby.sh ]; then
    source /opt/homebrew/opt/chruby/share/chruby/chruby.sh
    
    # Use Ruby 3.4.1
    if [ -d "$HOME/.rubies/ruby-3.4.1" ]; then
        chruby ruby-3.4.1
        echo -e "${GREEN}✓ Using Ruby 3.4.1 via chruby${NC}"
    else
        echo -e "${YELLOW}⚠ Ruby 3.4.1 not found in ~/.rubies/${NC}"
    fi
elif [ -f /usr/local/opt/chruby/share/chruby/chruby.sh ]; then
    source /usr/local/opt/chruby/share/chruby/chruby.sh
    
    # Use Ruby 3.4.1
    if [ -d "$HOME/.rubies/ruby-3.4.1" ]; then
        chruby ruby-3.4.1
        echo -e "${GREEN}✓ Using Ruby 3.4.1 via chruby${NC}"
    else
        echo -e "${YELLOW}⚠ Ruby 3.4.1 not found in ~/.rubies/${NC}"
    fi
else
    echo -e "${YELLOW}⚠ chruby not found, using system Ruby${NC}"
fi

# Check Ruby version
RUBY_VERSION=$(ruby -v | cut -d ' ' -f2 | cut -d '.' -f1-2)
echo -e "${BLUE}Using Ruby ${RUBY_VERSION}${NC}"

# Check if Ruby version is compatible
RUBY_MAJOR=$(echo $RUBY_VERSION | cut -d '.' -f1)
if [ "$RUBY_MAJOR" -lt 3 ]; then
    echo -e "${RED}✗ Ruby 3.0+ is required, but you have Ruby ${RUBY_VERSION}${NC}"
    echo -e "${YELLOW}Please install Ruby 3.4.1 and make sure chruby is configured${NC}"
    exit 1
fi

# Update bundler if needed (for Ruby 3.4+ compatibility)
if ! bundle --version | grep -q "2\."; then
    echo -e "${YELLOW}Updating Bundler to version 2.x for Ruby 3.4+ compatibility...${NC}"
    gem install bundler --version '~> 2.5' --no-document
fi

# Check if dependencies need to be reinstalled (incompatible bundler version)
if [ -d "vendor/bundle" ] && [ -f "Gemfile.lock" ]; then
    if grep -q "bundler-1\." Gemfile.lock; then
        echo -e "${YELLOW}Detected old Bundler version. Cleaning up...${NC}"
        chmod -R +w vendor/bundle 2>/dev/null || true
        rm -rf vendor/bundle
        rm -f Gemfile.lock
    fi
fi

# Check if dependencies are installed
if [ ! -d "vendor/bundle" ]; then
    echo -e "${YELLOW}Installing dependencies...${NC}"
    bundle install --path vendor/bundle
fi

# Start Jekyll server
echo -e "${GREEN}Server starting at http://localhost:4000${NC}"
echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}"
echo ""

# Use local config to avoid SSL issues with remote_theme
bundle exec jekyll serve --config _config-local.yml --host 127.0.0.1 --port 4000

