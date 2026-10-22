ARG VARIANT="4-8.5-trixie"
FROM mcr.microsoft.com/devcontainers/php:${VARIANT}

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here> \
#     && apt-get clean -y && rm -rf /var/lib/apt/lists/*

# [Optional] Uncomment this line to install additional PHP extensions.
# RUN docker-php-ext-install <your-package-list-here>

# [Optional] Uncomment this line to install additional PECL extensions.
# RUN pecl install <your-package-list-here> \
#     && docker-php-ext-enable <your-package-list-here>

# [Optional] Uncomment this line to install global node packages.
# RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g <your-package-here>"

RUN sudo curl -LsS https://download.civicrm.org/civix/civix.phar -o /usr/local/bin/civix \
    && sudo chmod +x /usr/local/bin/civix